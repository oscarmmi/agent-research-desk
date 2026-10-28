<?php

namespace App\Services\Llm;

interface LlmClient
{
    /**
     * Return one tool action as ['tool' => tool name, 'input' => tool input].
     * The only valid tools are search_corpus, define_term, and write_brief.
     *
     * @param  array<int, array{tool:string,input:string,output:string}>  $history
     * @return array{tool:string,input:string}
     */
    public function nextAction(string $question, array $history): array;
}
