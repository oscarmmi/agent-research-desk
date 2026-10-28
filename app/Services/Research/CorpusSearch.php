<?php

namespace App\Services\Research;

use App\Models\SourceDoc;

class CorpusSearch
{
    public function search(string $query): string
    {
        $documents = SourceDoc::query()->get();

        if ($documents->isEmpty()) {
            return 'No matching source documents found because the local corpus is empty.';
        }

        $queryTokens = $this->tokens($query);
        if ($queryTokens === []) {
            return "No matching source documents found for: {$query}";
        }

        $tokenized = $documents->mapWithKeys(fn (SourceDoc $document): array => [
            $document->id => $this->tokens($document->title.' '.$document->topic.' '.$document->body),
        ]);
        $documentCount = $documents->count();
        $scores = [];

        foreach ($tokenized as $documentId => $tokens) {
            $score = 0.0;
            $documentLength = max(count($tokens), 1);

            foreach (array_unique($queryTokens) as $term) {
                $termFrequency = count(array_filter($tokens, fn (string $token): bool => $token === $term)) / $documentLength;
                if ($termFrequency === 0.0) {
                    continue;
                }

                $documentFrequency = $tokenized->filter(
                    fn (array $documentTokens): bool => in_array($term, $documentTokens, true),
                )->count();
                $inverseDocumentFrequency = log(1 + ($documentCount / (1 + $documentFrequency)));
                $score += $termFrequency * $inverseDocumentFrequency;
            }

            if ($score > 0.0) {
                $scores[$documentId] = $score;
            }
        }

        arsort($scores);
        $topIds = array_slice(array_keys($scores), 0, 3);

        if ($topIds === []) {
            return "No matching source documents found for: {$query}";
        }

        $ranked = $documents->keyBy('id');

        return collect($topIds)->map(function (int $id, int $index) use ($ranked, $scores): string {
            /** @var SourceDoc $document */
            $document = $ranked->get($id);
            $excerpt = trim(preg_replace('/\s+/', ' ', $document->body) ?? $document->body);

            return sprintf(
                '[%d] %s — %s (TF-IDF %.3f)\n%s',
                $index + 1,
                $document->title,
                $document->topic,
                $scores[$id],
                $excerpt,
            );
        })->implode("\n\n");
    }

    /** @return list<string> */
    private function tokens(string $text): array
    {
        preg_match_all('/[a-z0-9]+/i', mb_strtolower($text), $matches);

        return $matches[0];
    }
}
