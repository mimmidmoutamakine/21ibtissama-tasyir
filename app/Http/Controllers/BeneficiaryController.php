<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesAssociationAccess;
use App\Models\AnnualProject;
use App\Models\AnnualProjectDomain;
use App\Models\AnnualProjectObjective;
use App\Models\Beneficiary;
use App\Models\BeneficiaryDocument;
use App\Models\BeneficiaryServiceAssignment;
use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BeneficiaryController extends Controller
{
    use AuthorizesAssociationAccess;
    
    private const DEFAULT_BENEFICIARY_DOCUMENTS = [
        'medical_file' => 'ملف طبي',
        'psychological_file' => 'ملف نفسي',
        'educational_file' => 'ملف تربوي',
        'individual_project' => 'المشروع الفردي',
        'mother_id_copy' => 'ب.و للأم',
        'father_id_copy' => 'ب.و للأب',
        'guardian_commitment' => 'التزام ولي الأمر',
    ];

    public function index(Request $request): View
    {
        $user = auth()->user();

        $beneficiaries = Beneficiary::query()
            ->with(['primaryEducator', 'serviceAssignments.specialist', 'serviceAssignments.service', 'documents'])
            ->when($user->hasRole('educator'), fn ($query) => $query->where('primary_educator_id', $user->id))
            ->when($user->hasRole('specialist'), fn ($query) => $query->whereHas('serviceAssignments', fn ($assignments) => $assignments->where('specialist_id', $user->id)))
            ->when($request->filled('status_filter'), function ($query) use ($request) {
                if ($request->status_filter === 'active') {
                    $query->where('is_active', true);
                }

                if ($request->status_filter === 'left') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('beneficiaries.index', compact('beneficiaries'));
    }

    public function show(Beneficiary $beneficiary): View
    {
        $this->ensureBeneficiaryAccess($beneficiary);
        $this->ensureDefaultDocumentsExist($beneficiary);

        $beneficiary->load([
            'primaryEducator',
            'documents',
            'serviceAssignments.specialist',
            'serviceAssignments.service',
            'annualProjects.domains.objectives',
            'monthlyPlans.items',
            'monthlyPlans.reviews.reviewer',
            'monthlyEvaluations.entries.monthlyPlanItem',
            'periodicMeetings.author',
            'attendances',
            'specialistReports.specialist',
            'leftByUser',
        ]);

        return view('beneficiaries.show', [
            'beneficiary' => $beneficiary,
            'canEditBeneficiary' => $this->canEditBeneficiary($beneficiary),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        return view('beneficiaries.create', [
            'beneficiary' => new Beneficiary(),
            'educators' => User::role('educator')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $validated = $this->validateBeneficiary($request);

        $beneficiary = Beneficiary::create($validated);

        $this->ensureDefaultDocumentsExist($beneficiary);

        return redirect()
            ->route('beneficiaries.show', $beneficiary)
            ->with('status', 'تمت إضافة المستفيد بنجاح.');
    }

    public function edit(Beneficiary $beneficiary): View
    {
        $this->ensureBeneficiaryAccess($beneficiary);
        abort_unless($this->canEditBeneficiary($beneficiary), 403);

        $this->ensureDefaultDocumentsExist($beneficiary);
        $beneficiary->load('documents');

        return view('beneficiaries.edit', [
            'beneficiary' => $beneficiary,
            'educators' => User::role('educator')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Beneficiary $beneficiary): RedirectResponse
    {
        $this->ensureBeneficiaryAccess($beneficiary);
        abort_unless($this->canEditBeneficiary($beneficiary), 403);

        $validated = $this->validateBeneficiary($request, $beneficiary->id);

        if (! auth()->user()->hasRole('admin')) {
            unset($validated['primary_educator_id'], $validated['can_assigned_educator_edit'], $validated['is_active']);
        }

        $beneficiary->update($validated);

        return redirect()
            ->route('beneficiaries.show', $beneficiary)
            ->with('status', 'تم تعديل بيانات المستفيد بنجاح.');
    }

    public function leave(Request $request, Beneficiary $beneficiary): RedirectResponse
    {
        $this->ensureBeneficiaryAccess($beneficiary);
        abort_unless($this->canEditBeneficiary($beneficiary), 403);

        $data = $request->validate([
            'left_reason' => ['nullable', 'string'],
            'exit_date' => ['nullable', 'date'],
        ]);

        $beneficiary->update([
            'is_active' => false,
            'status' => 'left',
            'exit_date' => $data['exit_date'] ?? now()->toDateString(),
            'left_at' => now(),
            'left_reason' => $data['left_reason'] ?? null,
            'left_by' => auth()->id(),
        ]);

        return redirect()
            ->route('beneficiaries.show', $beneficiary)
            ->with('status', 'تم تسجيل مغادرة المستفيد.');
    }

    public function annualProjects(): View
    {
        $user = auth()->user();

        $beneficiaries = Beneficiary::query()
            ->with(['annualProjects.domains.objectives', 'monthlyEvaluations.entries'])
            ->when($user->hasRole('educator'), fn ($query) => $query->where('primary_educator_id', $user->id))
            ->when($user->hasRole('specialist'), fn ($query) => $query->whereHas('serviceAssignments', fn ($assignments) => $assignments->where('specialist_id', $user->id)))
            ->get();

        return view('annual-projects.index', compact('beneficiaries'));
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $request->validate([
            'json_payload' => ['required', 'string'],
        ]);

        $payload = json_decode($request->string('json_payload')->toString(), true);

        if (! is_array($payload) || ! isset($payload['beneficiaries']) || ! is_array($payload['beneficiaries'])) {
            return back()->withErrors(['json_payload' => 'صيغة JSON غير صحيحة. يجب أن تحتوي على beneficiaries.'])->withInput();
        }

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($payload, &$created, &$updated) {
            foreach ($payload['beneficiaries'] as $row) {
                $internalCode = data_get($row, 'internal_code');

                if (! $internalCode) {
                    continue;
                }

                $lead = null;
                $leadData = (array) data_get($row, 'lead', []);

                if (! empty($leadData)) {
                    $lead = Lead::updateOrCreate(
                        [
                            'first_name_ar' => data_get($leadData, 'first_name_ar'),
                            'last_name_ar' => data_get($leadData, 'last_name_ar'),
                            'date_of_birth' => data_get($leadData, 'date_of_birth'),
                        ],
                        [
                            'first_name_fr' => data_get($leadData, 'first_name_fr'),
                            'last_name_fr' => data_get($leadData, 'last_name_fr'),
                            'age' => data_get($leadData, 'age'),
                            'place_of_birth' => data_get($leadData, 'place_of_birth'),
                            'gender' => data_get($leadData, 'gender'),
                            'address' => data_get($leadData, 'address'),
                            'father_full_name' => data_get($leadData, 'father_full_name'),
                            'father_phone' => data_get($leadData, 'father_phone'),
                            'father_cin' => data_get($leadData, 'father_cin'),
                            'mother_full_name' => data_get($leadData, 'mother_full_name'),
                            'mother_phone' => data_get($leadData, 'mother_phone'),
                            'mother_cin' => data_get($leadData, 'mother_cin'),
                            'guardian_cin' => data_get($leadData, 'guardian_cin'),
                            'birth_certificate_reference' => data_get($leadData, 'birth_certificate_reference'),
                            'family_status' => data_get($leadData, 'family_status'),
                            'disability_type' => data_get($leadData, 'disability_type'),
                            'disability_degree' => data_get($leadData, 'disability_degree'),
                            'disability_classification' => data_get($leadData, 'disability_classification'),
                            'school_level' => data_get($leadData, 'school_level'),
                            'school_institution' => data_get($leadData, 'school_institution'),
                            'registration_date' => data_get($leadData, 'registration_date'),
                            'exit_date' => data_get($leadData, 'exit_date'),
                            'benefit_type' => data_get($leadData, 'benefit_type'),
                            'program_service_domain' => data_get($leadData, 'program_service_domain'),
                            'document_checklist' => data_get($leadData, 'document_checklist', []),
                            'uploaded_documents' => data_get($leadData, 'uploaded_documents', []),
                            'status' => data_get($leadData, 'status', 'new'),
                            'notes' => data_get($leadData, 'notes'),
                        ]
                    );
                }

                $educator = User::where('email', data_get($row, 'assignments.primary_educator_email'))->first();
                $beneficiary = Beneficiary::firstOrNew(['internal_code' => $internalCode]);
                $wasExisting = $beneficiary->exists;

                $beneficiary->fill([
                    'lead_id' => $lead?->id,
                    'first_name_ar' => data_get($leadData, 'first_name_ar'),
                    'last_name_ar' => data_get($leadData, 'last_name_ar'),
                    'first_name_fr' => data_get($leadData, 'first_name_fr'),
                    'last_name_fr' => data_get($leadData, 'last_name_fr'),
                    'photo_path' => data_get($row, 'beneficiary.photo_path'),
                    'date_of_birth' => data_get($leadData, 'date_of_birth'),
                    'place_of_birth' => data_get($leadData, 'place_of_birth'),
                    'gender' => data_get($leadData, 'gender'),
                    'address' => data_get($leadData, 'address'),
                    'guardian_details' => data_get($row, 'guardian_details', []),
                    'phones' => data_get($row, 'phones', []),
                    'disability_details' => data_get($row, 'disability_details', []),
                    'schooling_details' => data_get($row, 'schooling_details', []),
                    'admission_date' => data_get($row, 'beneficiary.admission_date'),
                    'exit_date' => data_get($row, 'beneficiary.exit_date'),
                    'benefit_type' => data_get($row, 'beneficiary.benefit_type', data_get($leadData, 'benefit_type')),
                    'primary_educator_id' => $educator?->id,
                    'program_name' => data_get($row, 'beneficiary.program_name'),
                    'status' => data_get($row, 'beneficiary.status', 'active'),
                    'notes' => data_get($row, 'beneficiary.notes'),
                    'is_active' => data_get($row, 'beneficiary.status', 'active') !== 'left',
                    'can_assigned_educator_edit' => true,
                ]);
                $beneficiary->save();

                $wasExisting ? $updated++ : $created++;

                $this->ensureDefaultDocumentsExist($beneficiary);

                foreach ((array) data_get($row, 'documents', []) as $document) {
                    BeneficiaryDocument::updateOrCreate(
                        [
                            'beneficiary_id' => $beneficiary->id,
                            'title' => data_get($document, 'title'),
                        ],
                        [
                            'section' => data_get($document, 'section', 'إداري'),
                            'category' => data_get($document, 'category', 'إداري'),
                            'file_path' => data_get($document, 'file_path'),
                            'mime_type' => null,
                            'is_required' => (bool) data_get($document, 'is_required', false),
                            'needs_update' => (bool) data_get($document, 'needs_update', false),
                            'uploaded_at' => data_get($document, 'uploaded_at'),
                            'notes' => data_get($document, 'notes'),
                        ]
                    );
                }

                foreach ((array) data_get($row, 'assignments.services', []) as $serviceRow) {
                    $serviceName = data_get($serviceRow, 'service_name');

                    if (! $serviceName) {
                        continue;
                    }

                    $service = Service::firstOrCreate(
                        ['name' => $serviceName],
                        ['category' => 'خدمة مرافقة', 'status' => 'active']
                    );

                    $specialist = User::where('email', data_get($serviceRow, 'specialist_email'))->first();

                    BeneficiaryServiceAssignment::updateOrCreate(
                        [
                            'beneficiary_id' => $beneficiary->id,
                            'service_id' => $service->id,
                            'specialist_id' => $specialist?->id,
                            'start_date' => data_get($serviceRow, 'start_date'),
                        ],
                        [
                            'end_date' => data_get($serviceRow, 'end_date'),
                            'frequency' => data_get($serviceRow, 'frequency'),
                            'status' => data_get($serviceRow, 'status', 'active'),
                            'notes' => data_get($serviceRow, 'notes'),
                        ]
                    );
                }

                $annualProjectData = data_get($row, 'annual_project');

                if (is_array($annualProjectData) && ! empty($annualProjectData)) {
                    $annualProject = AnnualProject::updateOrCreate(
                        [
                            'beneficiary_id' => $beneficiary->id,
                            'year_label' => data_get($annualProjectData, 'year_label'),
                        ],
                        [
                            'schooling_space_type' => data_get($annualProjectData, 'schooling_space_type'),
                            'initial_situation_summary' => data_get($annualProjectData, 'initial_situation_summary'),
                            'team_participants' => data_get($annualProjectData, 'team_participants', []),
                            'family_participation' => data_get($annualProjectData, 'family_participation'),
                            'approval_status' => data_get($annualProjectData, 'approval_status', 'approved'),
                            'attachments' => [],
                        ]
                    );

                    foreach ((array) data_get($annualProjectData, 'domains', []) as $domainRow) {
                        $domain = AnnualProjectDomain::updateOrCreate(
                            [
                                'annual_project_id' => $annualProject->id,
                                'name' => data_get($domainRow, 'name'),
                            ],
                            [
                                'display_order' => data_get($domainRow, 'display_order', 0),
                            ]
                        );

                        foreach ((array) data_get($domainRow, 'objectives', []) as $objectiveRow) {
                            AnnualProjectObjective::updateOrCreate(
                                [
                                    'annual_project_domain_id' => $domain->id,
                                    'objective_text' => data_get($objectiveRow, 'objective_text'),
                                ],
                                [
                                    'baseline_level' => data_get($objectiveRow, 'baseline_level'),
                                    'target_level' => data_get($objectiveRow, 'target_level'),
                                    'display_order' => data_get($objectiveRow, 'display_order', 0),
                                ]
                            );
                        }
                    }
                }
            }
        });

        return back()->with('status', "تم استيراد {$created} مستفيد(ة) جديد(ة) وتحيين {$updated} ملف(ات).");
    }

    private function canEditBeneficiary(Beneficiary $beneficiary): bool
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('educator')) {
            return (int) $beneficiary->primary_educator_id === (int) $user->id
                && (bool) $beneficiary->can_assigned_educator_edit;
        }

        return false;
    }

    private function validateBeneficiary(Request $request, ?int $beneficiaryId = null): array
    {
        $validated = $request->validate([
            'internal_code' => ['nullable', 'string', 'max:255'],
            'first_name_ar' => ['required', 'string', 'max:255'],
            'last_name_ar' => ['required', 'string', 'max:255'],
            'first_name_fr' => ['nullable', 'string', 'max:255'],
            'last_name_fr' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'admission_date' => ['nullable', 'date'],
            'benefit_type' => ['nullable', 'string', 'max:255'],
            'program_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'primary_educator_id' => ['nullable', 'exists:users,id'],
            'can_assigned_educator_edit' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],

            'father_name' => ['nullable', 'string', 'max:255'],
            'father_phone' => ['nullable', 'string', 'max:50'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_phone' => ['nullable', 'string', 'max:50'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:50'],

            'school_level' => ['nullable', 'string', 'max:255'],
            'school_institution' => ['nullable', 'string', 'max:255'],

            'disability_type' => ['nullable', 'string', 'max:255'],
            'disability_degree' => ['nullable', 'string', 'max:255'],
        ]);

        return [
            'internal_code' => $validated['internal_code'] ?? null,
            'first_name_ar' => $validated['first_name_ar'],
            'last_name_ar' => $validated['last_name_ar'],
            'first_name_fr' => $validated['first_name_fr'] ?? null,
            'last_name_fr' => $validated['last_name_fr'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'place_of_birth' => $validated['place_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'admission_date' => $validated['admission_date'] ?? null,
            'benefit_type' => $validated['benefit_type'] ?? null,
            'program_name' => $validated['program_name'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'notes' => $validated['notes'] ?? null,
            'primary_educator_id' => $validated['primary_educator_id'] ?? null,
            'can_assigned_educator_edit' => (bool) ($validated['can_assigned_educator_edit'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),

            'guardian_details' => [
                'father_name' => $validated['father_name'] ?? null,
                'father_phone' => $validated['father_phone'] ?? null,
                'mother_name' => $validated['mother_name'] ?? null,
                'mother_phone' => $validated['mother_phone'] ?? null,
                'guardian_name' => $validated['guardian_name'] ?? null,
                'guardian_phone' => $validated['guardian_phone'] ?? null,
            ],

            'schooling_details' => [
                'level' => $validated['school_level'] ?? null,
                'institution' => $validated['school_institution'] ?? null,
            ],

            'disability_details' => [
                'type' => $validated['disability_type'] ?? null,
                'degree' => $validated['disability_degree'] ?? null,
            ],

            'phones' => array_values(array_filter([
                $validated['father_phone'] ?? null,
                $validated['mother_phone'] ?? null,
                $validated['guardian_phone'] ?? null,
            ])),
        ];
    }


    public function updateDocument(Request $request, Beneficiary $beneficiary, BeneficiaryDocument $document): RedirectResponse
    {
        $this->ensureBeneficiaryAccess($beneficiary);
        abort_unless($this->canEditBeneficiary($beneficiary), 403);
        abort_unless((int) $document->beneficiary_id === (int) $beneficiary->id, 404);

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
            'document_file' => ['nullable', 'file', 'max:10240'],
        ]);

        $data = [
            'notes' => $validated['notes'] ?? null,
        ];

        if ($request->hasFile('document_file')) {
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $uploadedFile = $request->file('document_file');

            $data['file_path'] = $uploadedFile->store('beneficiaries/documents', 'public');
            $data['mime_type'] = $uploadedFile->getClientMimeType();
            $data['uploaded_at'] = now()->toDateString();
        }

        $document->update($data);

        return back()->with('status', 'تم تحيين الوثيقة بنجاح.');
    }

    private function ensureDefaultDocumentsExist(Beneficiary $beneficiary): void
    {
        foreach (self::DEFAULT_BENEFICIARY_DOCUMENTS as $key => $label) {
            $beneficiary->documents()->firstOrCreate(
                [
                    'category' => $key,
                ],
                [
                    'section' => 'وثائق مشروع تحسين ظروف التمدرس',
                    'category' => $key,
                    'title' => $label,
                    'is_required' => true,
                ]
            );
        }
    }
}
