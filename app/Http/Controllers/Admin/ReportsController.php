<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReportsController extends Controller
{
    /**
     * Display a listing of all reports.
     */
    public function index()
    {
        $reports = UserReport::select('user_reports.*')
            ->leftJoin('users as reporter', 'user_reports.reporter_id', '=', 'reporter.id')
            ->leftJoin('users as reported_user', 'user_reports.reported_user_id', '=', 'reported_user.id')
            ->addSelect([
                'reporter.name as reporter_name',
                'reporter.email as reporter_email',
                'reported_user.name as reported_user_name', 
                'reported_user.email as reported_user_email'
            ])
            ->orderBy('reviewed', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Transform the data to include the user objects
        $reports->getCollection()->transform(function ($report) {
            $report->reporter = $report->reporter_name ? (object)[
                'id' => $report->reporter_id,
                'name' => $report->reporter_name,
                'email' => $report->reporter_email
            ] : null;
            
            $report->reportedUser = $report->reported_user_name ? (object)[
                'id' => $report->reported_user_id,
                'name' => $report->reported_user_name,
                'email' => $report->reported_user_email
            ] : null;
            
            // Clean up the extra fields
            unset($report->reporter_name, $report->reporter_email, $report->reported_user_name, $report->reported_user_email);
            
            return $report;
        });
            
        // Make sure pagination links are properly formatted
        if ($reports->hasPages()) {
            $reports->appends(request()->all());
        }

        return Inertia::render('Admin/Reports/Index', [
            'reports' => $reports
        ]);
    }

    /**
     * Display the specified report.
     */
    public function show($id)
    {
        $report = UserReport::select('user_reports.*')
            ->leftJoin('users as reporter', 'user_reports.reporter_id', '=', 'reporter.id')
            ->leftJoin('users as reported_user', 'user_reports.reported_user_id', '=', 'reported_user.id')
            ->leftJoin('users as reviewer', 'user_reports.reviewed_by', '=', 'reviewer.id')
            ->addSelect([
                'reporter.name as reporter_name',
                'reporter.email as reporter_email',
                'reported_user.name as reported_user_name', 
                'reported_user.email as reported_user_email',
                'reviewer.name as reviewer_name',
                'reviewer.email as reviewer_email'
            ])
            ->where('user_reports.id', $id)
            ->firstOrFail();
            
        // Add the user objects
        $report->reporter = $report->reporter_name ? (object)[
            'id' => $report->reporter_id,
            'name' => $report->reporter_name,
            'email' => $report->reporter_email
        ] : null;
        
        $report->reportedUser = $report->reported_user_name ? (object)[
            'id' => $report->reported_user_id,
            'name' => $report->reported_user_name,
            'email' => $report->reported_user_email
        ] : null;
        
        $report->reviewer = $report->reviewer_name ? (object)[
            'id' => $report->reviewed_by,
            'name' => $report->reviewer_name,
            'email' => $report->reviewer_email
        ] : null;
        
        // Clean up the extra fields
        unset($report->reporter_name, $report->reporter_email, $report->reported_user_name, $report->reported_user_email, $report->reviewer_name, $report->reviewer_email);

        return Inertia::render('Admin/Reports/Show', [
            'report' => $report
        ]);
    }

    /**
     * Update the report status.
     */
    public function update(Request $request, $id)
    {
        $report = UserReport::findOrFail($id);

        $validated = $request->validate([
            'reviewed' => 'required|boolean',
        ]);

        $report->reviewed = $validated['reviewed'];
        $report->reviewed_at = now();
        $report->reviewed_by = Auth::id();
        $report->save();

        return redirect()->route('admin.reports.index')->with('success', 'Report updated successfully');
    }

    /**
     * Get statistics for the admin dashboard.
     */
    public function getStats()
    {
        $stats = [
            'totalReports' => UserReport::count(),
            'pendingReports' => UserReport::where('reviewed', false)->count(),
            'blockedUsers' => UserReport::where('is_blocked', true)->count(),
        ];

        return response()->json($stats);
    }
} 