<?php

namespace App\Services\Research;

use App\Models\ResearchJob;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;
use Illuminate\Support\Facades\Log;
use Throwable;

class ResearchAgent
{
    private const MAX_TOOL_ROUNDS = 5;

    public function __construct(
        private readonly LlmClient $llm,
        private readonly FakeLlmClient $fallbackLlm,
        private readonly ResearchTools $tools,
    ) {}

    public function research(string $question): ResearchJob
    {
        $job = ResearchJob::query()->create(['question' => $question, 'status' => 'queued']);
        $history = [];

        try {
            for ($round = 0; $round < self::MAX_TOOL_ROUNDS; $round++) {
                try {
                    $action = $this->llm->nextAction($question, $history);
                } catch (Throwable $exception) {
                    Log::warning('Research LLM unavailable; continuing with deterministic fallback.', [
                        'research_job_id' => $job->id,
                        'exception' => $exception::class,
                    ]);
                    $action = $this->fallbackLlm->nextAction($question, $history);
                }

                $tool = $action['tool'];
                $input = mb_substr($action['input'], 0, 12000);

                if ($tool === 'write_brief') {
                    $brief = $input !== '' ? $input : 'The research agent returned an empty brief. Please try again.';
                    $this->persistStep($job, $tool, $brief, $brief, $history);
                    $job->update(['brief' => $brief, 'status' => 'done']);

                    return $job->fresh('steps');
                }

                $output = in_array($tool, ['search_corpus', 'define_term'], true)
                    ? $this->tools->run($tool, $input)
                    : "Tool not found: {$tool}. Available tools: search_corpus, define_term, write_brief.";

                $this->persistStep($job, $tool !== '' ? $tool : 'unknown_tool', $input, $output, $history);
            }

            $evidence = collect($history)
                ->filter(fn (array $step): bool => in_array($step['tool'], ['search_corpus', 'define_term'], true))
                ->map(fn (array $step): string => $step['output'])
                ->implode("\n\n");
            $finalAction = $this->fallbackLlm->nextAction($question, $history);
            $brief = $finalAction['input'];

            if (str_contains($brief, 'No matching')) {
                $brief = "## Research brief\n\nI could not find a matching entry in the Harbor Desk local reference set for **{$question}**. No external sources were consulted.";
            } elseif ($evidence !== '') {
                $brief = "## Research brief\n\n{$evidence}\n\n### Takeaway\nUse the referenced Harbor Desk guidance as the current operating answer. This brief was finalized after the five-round tool limit.";
            }

            $this->persistStep($job, 'write_brief', $brief, $brief, $history);
            $job->update(['brief' => $brief, 'status' => 'done']);
        } catch (Throwable $exception) {
            Log::error('Research job failed safely.', ['research_job_id' => $job->id, 'exception' => $exception::class]);
            $brief = '## Research unavailable'."\n\nThe local research run could not be completed. Please retry in a moment.";
            $job->update(['brief' => $brief, 'status' => 'failed']);

            try {
                $this->persistStep($job, 'research_error', 'Finalize research', $brief, $history);
            } catch (Throwable) {
                // The job remains marked failed even if step persistence is unavailable.
            }
        }

        return $job->fresh('steps');
    }

    /** @param array<int, array{tool:string,input:string,output:string}> $history */
    private function persistStep(ResearchJob $job, string $tool, string $input, string $output, array &$history): void
    {
        $position = count($history) + 1;
        $job->steps()->create([
            'tool_name' => $tool,
            'input' => $input,
            'output' => $output,
            'position' => $position,
        ]);
        $history[] = ['tool' => $tool, 'input' => $input, 'output' => $output];
    }
}
