<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesAssociationAccess;
use App\Models\AnnualProject;
use App\Models\AnnualProjectDomain;
use App\Models\AnnualProjectObjective;
use App\Models\Beneficiary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnnualProjectController extends Controller
{
    use AuthorizesAssociationAccess;

    public function show(AnnualProject $annualProject): View
    {
        $this->ensureAnnualProjectAccess($annualProject);

        $annualProject->load([
            'beneficiary.primaryEducator',
            'beneficiary.serviceAssignments.specialist',
            'beneficiary.serviceAssignments.service',
            'domains.objectives',
        ]);

        return view('annual-projects.show', [
            'annualProject' => $annualProject,
            'canEditAnnualProject' => $this->canEditAnnualProject($annualProject),
        ]);
    }

    public function create(Beneficiary $beneficiary): View
    {
        $this->ensureBeneficiaryAccess($beneficiary);
        abort_unless($this->canEditBeneficiaryAnnualProject($beneficiary), 403);

        $annualProject = new AnnualProject([
            'beneficiary_id' => $beneficiary->id,
            'approval_status' => 'draft',
            'team_participants' => [],
            'attachments' => [],
        ]);
        $annualProject->setRelation('beneficiary', $beneficiary);

        return view('annual-projects.edit', [
            'annualProject' => $annualProject,
            'beneficiary' => $beneficiary,
            'isCreateMode' => true,
            'domainBlueprints' => $this->defaultDomainBlueprints(),
        ]);
    }

    public function store(Request $request, Beneficiary $beneficiary): RedirectResponse
    {
        $this->ensureBeneficiaryAccess($beneficiary);
        abort_unless($this->canEditBeneficiaryAnnualProject($beneficiary), 403);

        $validated = $this->validateAnnualProject($request);

        $annualProject = DB::transaction(function () use ($beneficiary, $validated) {
            $annualProject = AnnualProject::create([
                'beneficiary_id' => $beneficiary->id,
                'year_label' => $validated['year_label'],
                'schooling_space_type' => $validated['schooling_space_type'] ?? null,
                'initial_situation_summary' => $validated['initial_situation_summary'] ?? null,
                'team_participants' => $this->normalizeParticipants($validated['team_participants_text'] ?? null),
                'family_participation' => $validated['family_participation'] ?? null,
                'approval_status' => $validated['approval_status'],
                'attachments' => [],
            ]);

            $this->syncDomainsAndObjectives($annualProject, $validated['domains'] ?? []);

            return $annualProject;
        });

        activity()
            ->performedOn($annualProject)
            ->causedBy(auth()->user())
            ->event('annual_project_created')
            ->log('تم إنشاء المشروع الفردي السنوي');

        return redirect()
            ->route('annual-projects.show', $annualProject)
            ->with('status', 'تم إنشاء المشروع الفردي السنوي بنجاح.');
    }

    public function edit(AnnualProject $annualProject): View
    {
        $this->ensureAnnualProjectAccess($annualProject);
        abort_unless($this->canEditAnnualProject($annualProject), 403);

        $annualProject->load(['beneficiary', 'domains.objectives']);

        return view('annual-projects.edit', [
            'annualProject' => $annualProject,
            'beneficiary' => $annualProject->beneficiary,
            'isCreateMode' => false,
            'domainBlueprints' => $this->domainBlueprintsFromProject($annualProject),
        ]);
    }

    public function update(Request $request, AnnualProject $annualProject): RedirectResponse
    {
        $this->ensureAnnualProjectAccess($annualProject);
        abort_unless($this->canEditAnnualProject($annualProject), 403);

        $validated = $this->validateAnnualProject($request);

        DB::transaction(function () use ($annualProject, $validated) {
            $annualProject->update([
                'year_label' => $validated['year_label'],
                'schooling_space_type' => $validated['schooling_space_type'] ?? null,
                'initial_situation_summary' => $validated['initial_situation_summary'] ?? null,
                'team_participants' => $this->normalizeParticipants($validated['team_participants_text'] ?? null),
                'family_participation' => $validated['family_participation'] ?? null,
                'approval_status' => $validated['approval_status'],
            ]);

            $this->syncDomainsAndObjectives($annualProject, $validated['domains'] ?? []);
        });

        activity()
            ->performedOn($annualProject)
            ->causedBy(auth()->user())
            ->event('annual_project_updated')
            ->log('تم تعديل المشروع الفردي السنوي');

        return redirect()
            ->route('annual-projects.show', $annualProject)
            ->with('status', 'تم تحيين المشروع الفردي السنوي بنجاح.');
    }

    private function validateAnnualProject(Request $request): array
    {
        return $request->validate([
            'year_label' => ['required', 'string', 'max:255'],
            'schooling_space_type' => ['nullable', 'string', 'max:255'],
            'initial_situation_summary' => ['nullable', 'string'],
            'team_participants_text' => ['nullable', 'string'],
            'family_participation' => ['nullable', 'string'],
            'approval_status' => ['required', 'string', 'max:100'],
            'domains' => ['required', 'array', 'min:1'],
            'domains.*.id' => ['nullable', 'integer'],
            'domains.*.name' => ['required', 'string', 'max:255'],
            'domains.*.display_order' => ['nullable', 'integer', 'min:0'],
            'domains.*.objectives' => ['required', 'array', 'min:1'],
            'domains.*.objectives.*.id' => ['nullable', 'integer'],
            'domains.*.objectives.*.objective_text' => ['required', 'string'],
            'domains.*.objectives.*.baseline_level' => ['nullable', 'string', 'max:255'],
            'domains.*.objectives.*.target_level' => ['nullable', 'string', 'max:255'],
            'domains.*.objectives.*.display_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function syncDomainsAndObjectives(AnnualProject $annualProject, array $domainsPayload): void
    {
        $existingDomainIds = $annualProject->domains()->pluck('id')->all();
        $keptDomainIds = [];

        foreach ($domainsPayload as $domainIndex => $domainData) {
            $domain = ! empty($domainData['id'])
                ? AnnualProjectDomain::query()
                    ->where('annual_project_id', $annualProject->id)
                    ->findOrFail($domainData['id'])
                : new AnnualProjectDomain(['annual_project_id' => $annualProject->id]);

            $domain->fill([
                'name' => $domainData['name'],
                'display_order' => $domainData['display_order'] ?? ($domainIndex + 1),
            ]);
            $domain->annual_project_id = $annualProject->id;
            $domain->save();

            $keptDomainIds[] = $domain->id;

            $existingObjectiveIds = $domain->objectives()->pluck('id')->all();
            $keptObjectiveIds = [];

            foreach (($domainData['objectives'] ?? []) as $objectiveIndex => $objectiveData) {
                $objective = ! empty($objectiveData['id'])
                    ? AnnualProjectObjective::query()
                        ->where('annual_project_domain_id', $domain->id)
                        ->findOrFail($objectiveData['id'])
                    : new AnnualProjectObjective(['annual_project_domain_id' => $domain->id]);

                $objective->fill([
                    'objective_text' => $objectiveData['objective_text'],
                    'baseline_level' => $objectiveData['baseline_level'] ?? null,
                    'target_level' => $objectiveData['target_level'] ?? null,
                    'display_order' => $objectiveData['display_order'] ?? ($objectiveIndex + 1),
                ]);
                $objective->annual_project_domain_id = $domain->id;
                $objective->save();

                $keptObjectiveIds[] = $objective->id;
            }

            $objectiveIdsToDelete = array_diff($existingObjectiveIds, $keptObjectiveIds);
            if ($objectiveIdsToDelete !== []) {
                $domain->objectives()->whereIn('id', $objectiveIdsToDelete)->delete();
            }
        }

        $domainIdsToDelete = array_diff($existingDomainIds, $keptDomainIds);
        if ($domainIdsToDelete !== []) {
            $annualProject->domains()->whereIn('id', $domainIdsToDelete)->delete();
        }
    }

    private function normalizeParticipants(?string $participantsText): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $participantsText))
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }

    private function canEditBeneficiaryAnnualProject(Beneficiary $beneficiary): bool
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('educator') && (int) $beneficiary->primary_educator_id === (int) $user->id;
    }

    private function canEditAnnualProject(AnnualProject $annualProject): bool
    {
        return $this->canEditBeneficiaryAnnualProject($annualProject->beneficiary);
    }

    private function domainBlueprintsFromProject(AnnualProject $annualProject): array
    {
        $domainsByName = $annualProject->domains->keyBy('name');

        return collect($this->fixedDomainNames())
            ->map(function (string $domainName, int $index) use ($domainsByName) {
                /** @var \App\Models\AnnualProjectDomain|null $existingDomain */
                $existingDomain = $domainsByName->get($domainName);

                $objectives = $existingDomain
                    ? $existingDomain->objectives
                        ->sortBy('display_order')
                        ->map(fn (AnnualProjectObjective $objective) => [
                            'id' => $objective->id,
                            'objective_text' => $objective->objective_text,
                            'baseline_level' => $objective->baseline_level,
                            'target_level' => $objective->target_level,
                            'display_order' => $objective->display_order,
                        ])->values()->all()
                    : [];

                if ($objectives === []) {
                    $objectives = [
                        ['id' => null, 'objective_text' => '', 'baseline_level' => '', 'target_level' => '', 'display_order' => 1],
                    ];
                }

                return [
                    'id' => $existingDomain?->id,
                    'name' => $domainName,
                    'display_order' => $existingDomain?->display_order ?? ($index + 1),
                    'objectives' => $objectives,
                ];
            })
            ->all();
    }

    private function defaultDomainBlueprints(): array
    {
        return collect($this->fixedDomainNames())
            ->map(fn (string $name, int $index) => [
                'id' => null,
                'name' => $name,
                'display_order' => $index + 1,
                'objectives' => [
                    ['id' => null, 'objective_text' => '', 'baseline_level' => '', 'target_level' => '', 'display_order' => 1],
                ],
            ])->all();
    }

    private function fixedDomainNames(): array
    {
        return [
            'التربية والتعلمات الدراسية',
            'التعلمات الاجتماعية',
            'التواصل والتفاعل الاجتماعي',
            'المهــارات الحــــركية',
            'التعلمات المهنية',
        ];
    }
}
