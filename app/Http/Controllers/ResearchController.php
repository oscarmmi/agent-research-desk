<?php

namespace App\Http\Controllers;

use App\Models\GlossaryTerm;
use App\Models\ResearchJob;
use App\Models\SourceDoc;
use App\Services\Research\ResearchAgent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResearchController extends Controller
{
    public function index(Request $request): Response
    {
        $jobId = $request->integer('job');
        $job = $jobId > 0
            ? ResearchJob::query()->with('steps')->find($jobId)
            : ResearchJob::query()->with('steps')->latest('id')->first();

        return Inertia::render('ResearchDesk', [
            'job' => $job,
            'recentJobs' => ResearchJob::query()->latest('id')->limit(6)->get(['id', 'question', 'status', 'created_at']),
            'corpusCount' => SourceDoc::query()->count(),
            'glossaryCount' => GlossaryTerm::query()->count(),
        ]);
    }

    public function store(Request $request, ResearchAgent $agent): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $job = $agent->research($validated['question']);

        return redirect()->route('home', ['job' => $job->id]);
    }
}
