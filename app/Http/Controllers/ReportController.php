<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Task;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request, ReportService $reportService)
    {
        abort_unless($request->user()->hasPermissionTo('reports.view'), 403);

        $report = $reportService->sales($request);
        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);

        return view('reports.sales', [
            'report' => $report,
            'customers' => $customers,
        ]);
    }

    public function crm(Request $request, ReportService $reportService)
    {
        abort_unless($request->user()->hasPermissionTo('reports.view'), 403);

        return view('reports.crm', [
            'report' => $reportService->crm($request),
        ]);
    }

    public function projects(Request $request, ReportService $reportService)
    {
        abort_unless($request->user()->hasPermissionTo('reports.view'), 403);

        $report = $reportService->projects($request);
        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);

        return view('reports.projects', compact('report', 'customers'));
    }

    public function tasks(Request $request, ReportService $reportService)
    {
        abort_unless($request->user()->hasPermissionTo('reports.view'), 403);

        $report = $reportService->tasks($request);
        $projects = Project::query()->orderBy('name')->get(['id', 'name']);

        return view('reports.tasks', compact('report', 'projects'));
    }

    public function team(Request $request, ReportService $reportService)
    {
        abort_unless($request->user()->hasPermissionTo('reports.view'), 403);

        return view('reports.team', [
            'report' => $reportService->teamPerformance($request),
        ]);
    }

    public function export(Request $request, string $report, ReportService $reportService)
    {
        abort_unless($request->user()->hasPermissionTo('reports.export'), 403);

        return match ($report) {
            'sales' => $reportService->streamCsv(
                'sales-report.csv',
                [__('Title'), __('Customer'), __('Owner'), __('Stage'), __('Value'), __('Status'), __('Expected Close')],
                \App\Models\Deal::query()->with(['customer', 'owner', 'stage'])->get()->map(fn ($deal) => [
                    $deal->title,
                    $deal->customer?->name,
                    $deal->owner?->name,
                    __($deal->stage?->name ?? 'Unknown'),
                    $deal->value,
                    __(str($deal->status)->title()->toString()),
                    optional($deal->expected_close_date)->format('Y-m-d'),
                ])
            ),
            'projects' => $reportService->streamCsv(
                'projects-report.csv',
                [__('Name'), __('Customer'), __('Manager'), __('Budget'), __('Status'), __('Progress')],
                Project::query()->with(['customer', 'manager'])->get()->map(fn ($project) => [
                    $project->name,
                    $project->customer?->name,
                    $project->manager?->name,
                    $project->budget,
                    __(str($project->status)->replace('_', ' ')->title()->toString()),
                    $project->progress,
                ])
            ),
            'tasks' => $reportService->streamCsv(
                'tasks-report.csv',
                [__('Title'), __('Project'), __('Status'), __('Priority'), __('Due Date')],
                Task::query()->with('project')->get()->map(fn ($task) => [
                    $task->title,
                    $task->project?->name,
                    __(str($task->status)->replace('_', ' ')->title()->toString()),
                    __(str($task->priority)->title()->toString()),
                    optional($task->due_date)->format('Y-m-d'),
                ])
            ),
            default => abort(404),
        };
    }
}
