<?php

namespace App\Http\Controllers;

use App\Models\AssociationProject;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('projects.index', [
            'projects' => AssociationProject::latest()->get(),
        ]);
    }
}
