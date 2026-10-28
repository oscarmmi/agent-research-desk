<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiLlmClient implements LlmClient
{
    public function nextAction(string $question, array $history): array
    {
        $historyText = collect($history)->map(fn (array $step): string => implode("\n", [
            'Tool: '.$step['tool'],
            'Input: '.$step['input'],
            'Output: '.$step['output'],
        ]))->implode("\n\n");

        $prompt = <<<PROMPT
You are the Harbor Desk research agent. Work only from the evidence returned by tools; do not invent sources.
Choose exactly one next action and return JSON only: {"tool":"search_corpus|define_term|write_brief","input":"..."}.
Use search_corpus for operational questions, define_term when the user asks what a glossary phrase means, then write_brief after receiving evidence. The write_brief input must be a concise Markdown answer and cite source titles exactly as shown in tool output. If a tool returned no match, say that clearly.

Question: {$question}

Prior tool steps:
{$historyText}
PROMPT;

        $response = Http::timeout(12)
            ->acceptJson()
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.config('services.gemini.model').':generateContent?key='.config('services.gemini.key'), [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['responseMimeType' => 'application/json', 'temperature' => 0.1],
            ])
            ->throw()
            ->json('candidates.0.content.parts.0.text');

        $action = is_string($response) ? json_decode($response, true) : null;

        if (! is_array($action) || ! is_string($action['tool'] ?? null) || ! is_string($action['input'] ?? null)) {
            throw new RuntimeException('Gemini returned an invalid tool action.');
        }

        return ['tool' => $action['tool'], 'input' => $action['input']];
    }
}
