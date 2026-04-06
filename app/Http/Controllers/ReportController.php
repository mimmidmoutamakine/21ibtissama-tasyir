<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function monthly(Beneficiary $beneficiary): View
    {
        $meeting = $beneficiary->periodicMeetings()->latest('meeting_month')->first();

        return view('reports.monthly', [
            'beneficiary' => $beneficiary,
            'meeting' => $meeting,
            'monthLabel' => Carbon::now()->locale('ar')->translatedFormat('F Y'),
        ]);
    }
}
