<?php

namespace App\Services\Research;

use App\Models\GlossaryTerm;

class ResearchTools
{
    public function __construct(private readonly CorpusSearch $corpusSearch) {}

    public function run(string $tool, string $input): string
    {
        return match ($tool) {
            'search_corpus' => $this->corpusSearch->search($input),
            'define_term' => $this->defineTerm($input),
            default => "Tool not found: {$tool}. Available tools: search_corpus, define_term, write_brief.",
        };
    }

    private function defineTerm(string $term): string
    {
        $entry = GlossaryTerm::query()
            ->whereRaw('LOWER(term) = ?', [mb_strtolower(trim($term))])
            ->first();

        return $entry
            ? "{$entry->term}: {$entry->definition}"
            : "No glossary entry found for \"{$term}\". This is a tool miss; the term is not in the local glossary.";
    }
}
