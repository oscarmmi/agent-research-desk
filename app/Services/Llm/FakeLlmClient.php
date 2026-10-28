<?php

namespace App\Services\Llm;

class FakeLlmClient implements LlmClient
{
    public function nextAction(string $question, array $history): array
    {
        $tools = array_column($history, 'tool');

        if (in_array('search_corpus', $tools, true) || in_array('define_term', $tools, true)) {
            $evidence = collect($history)
                ->filter(fn (array $step): bool => in_array($step['tool'], ['search_corpus', 'define_term'], true))
                ->map(fn (array $step): string => $step['output'])
                ->implode("\n\n");

            return [
                'tool' => 'write_brief',
                'input' => $this->brief($question, $evidence),
            ];
        }

        if (preg_match('/\bdefine\b|\bdefinition\b|\bwhat does .+ mean\b/i', $question) === 1) {
            $term = preg_replace('/^.*?\b(?:define|definition of|what does)\s+/i', '', $question) ?? $question;
            $term = trim(preg_replace('/\s+mean\??$/i', '', $term) ?? $term, " \t\n\r\0\x0B?.!");

            return ['tool' => 'define_term', 'input' => $term];
        }

        return ['tool' => 'search_corpus', 'input' => $question];
    }

    private function brief(string $question, string $evidence): string
    {
        if (str_contains($evidence, 'No matching source documents found') || str_contains($evidence, 'No glossary entry found')) {
            return "## Research brief\n\nI could not find a matching entry in the Harbor Desk local reference set for **{$question}**. The available corpus may not cover this question yet. No external sources were consulted.";
        }

        return "## Research brief\n\n{$evidence}\n\n### Takeaway\nUse the referenced Harbor Desk guidance as the current operating answer. Confirm with the appropriate team lead if the situation falls outside the documented scope.\n\n*Sources are limited to the local Harbor Desk reference corpus.*";
    }
}
