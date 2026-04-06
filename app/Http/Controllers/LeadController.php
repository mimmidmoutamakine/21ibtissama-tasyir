<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(): View
    {
        return view('leads.index', [
            'leads' => Lead::latest()->paginate(10),
            'disabilityTypes' => ['T21', 'IMC', 'TSA', 'H', 'Other'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name_ar' => ['required', 'string'],
            'last_name_ar' => ['required', 'string'],
            'first_name_fr' => ['nullable', 'string'],
            'last_name_fr' => ['nullable', 'string'],
            'age' => ['nullable', 'integer'],
            'date_of_birth' => ['nullable', 'date'],
            'place_of_birth' => ['nullable', 'string'],
            'gender' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'father_full_name' => ['nullable', 'string'],
            'father_phone' => ['nullable', 'string'],
            'father_cin' => ['nullable', 'string'],
            'mother_full_name' => ['nullable', 'string'],
            'mother_phone' => ['nullable', 'string'],
            'mother_cin' => ['nullable', 'string'],
            'guardian_cin' => ['nullable', 'string'],
            'birth_certificate_reference' => ['nullable', 'string'],
            'family_status' => ['nullable', 'string'],
            'disability_type' => ['nullable', 'string'],
            'disability_degree' => ['nullable', 'string'],
            'disability_classification' => ['nullable', 'string'],
            'school_level' => ['nullable', 'string'],
            'school_institution' => ['nullable', 'string'],
            'registration_date' => ['nullable', 'date'],
            'exit_date' => ['nullable', 'date'],
            'benefit_type' => ['nullable', 'string'],
            'program_service_domain' => ['nullable', 'string'],
            'document_checklist' => ['array'],
            'uploaded_documents' => ['array'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['status'] = 'new';
        Lead::create($validated);

        activity()->causedBy(auth()->user())->event('lead_created')->log('تم إنشاء طلب إحالة جديد');

        return back()->with('status', 'تم حفظ طلب الإحالة بنجاح.');
    }
}
