<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Store a new report.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reported_user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string|max:1000',
            'is_blocked' => 'boolean',
        ]);

        // Create the report
        $report = new UserReport();
        $report->reporter_id = Auth::id();
        $report->reported_user_id = $validated['reported_user_id'];
        $report->reason = $validated['reason'];
        $report->details = $validated['details'] ?? null;
        $report->is_blocked = $validated['is_blocked'] ?? false;
        $report->save();

        return redirect()->back()->with('success', 'Report submitted successfully');
    }

    /**
     * Block a user without reporting.
     */
    public function block(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Create a report with block flag but no details
        $report = new UserReport();
        $report->reporter_id = Auth::id();
        $report->reported_user_id = $validated['user_id'];
        $report->reason = 'Blocked by user';
        $report->is_blocked = true;
        $report->save();

        return redirect()->back()->with('success', 'User blocked successfully');
    }
} 