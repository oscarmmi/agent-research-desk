<?php

use App\Models\ResearchJob;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;

beforeEach(function () {
    config()->set('services.gemini.key', null);
    $this->seed();
});

test('the default llm client is the deterministic fake when no Gemini key is set', function () {
    expect(app(LlmClient::class))->toBeInstanceOf(FakeLlmClient::class);
});

test('an SLA question searches the corpus and creates a sourced brief', function () {
    expect(app(LlmClient::class))->toBeInstanceOf(FakeLlmClient::class);

    $response = $this->post(route('research.store'), ['question' => 'What is our SLA for P1?']);
    $job = ResearchJob::query()->with('steps')->latest('id')->firstOrFail();

    $response->assertRedirect(route('home', ['job' => $job->id]));
    expect($job->status)->toBe('done')
        ->and($job->brief)->not->toBeEmpty()
        ->and($job->brief)->toContain('Priority incident response')
        ->and($job->steps->pluck('tool_name')->all())->toContain('search_corpus', 'write_brief')
        ->and($job->steps->pluck('position')->all())->toBe([1, 2]);
});

test('an unknown glossary term is recorded as a tool miss without a server error', function () {
    $response = $this->post(route('research.store'), ['question' => 'Define quantum banana']);
    $job = ResearchJob::query()->with('steps')->latest('id')->firstOrFail();

    $response->assertRedirect(route('home', ['job' => $job->id]));
    expect($job->status)->toBe('done')
        ->and($job->steps->first()->tool_name)->toBe('define_term')
        ->and($job->steps->first()->output)->toContain('No glossary entry found')
        ->and($job->brief)->toContain('could not find a matching entry');
});

test('a failing optional llm falls back to local research instead of returning a 500', function () {
    $this->app->instance(LlmClient::class, new class implements LlmClient
    {
        public function nextAction(string $question, array $history): array
        {
            throw new RuntimeException('Simulated Gemini timeout');
        }
    });

    $response = $this->post(route('research.store'), ['question' => 'What is our SLA for P1?']);
    $job = ResearchJob::query()->with('steps')->latest('id')->firstOrFail();

    $response->assertRedirect(route('home', ['job' => $job->id]));
    expect($job->status)->toBe('done')
        ->and($job->brief)->toContain('Priority incident response')
        ->and($job->steps->pluck('tool_name')->all())->toContain('search_corpus', 'write_brief');
});
