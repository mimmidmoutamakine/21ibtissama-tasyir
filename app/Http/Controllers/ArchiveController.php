<?php

namespace App\Http\Controllers;

use App\Models\ArchiveFile;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function index(): View
    {
        return view('archives.index', [
            'files' => ArchiveFile::latest()->paginate(15),
        ]);
    }
}
