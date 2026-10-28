Build Agent Research Desk in THIS folder: C:\xampp\htdocs\agent-research-desk

A recruiter-ready multi-step research agent: given a question, search a local corpus, maybe look up a glossary term, then write a short brief with sources.

Laravel 13 + Vue 3 starter kit is already here if present. Reuse it. Do not create a second app inside NewPortfolio.

## Must satisfy

- Models: `SourceDoc` (title, body, topic), `GlossaryTerm` (term, definition), `ResearchJob` (question, brief, status queued|done|failed), `ResearchStep` (research_job_id, tool_name, input, output, position).
- Tools: `search_corpus(query)` TF-IDF top 3, `define_term(term)` glossary lookup, `write_brief(markdown)` finalizer.
- `App\Services\Research\ResearchAgent`: max 5 tool rounds via `App\Services\Llm\LlmClient`. Persist every step. Never 500.
- `FakeLlmClient` in tests and when no `GEMINI_API_KEY` (script: question containing "SLA" → search_corpus then write_brief citing that hit). Optional Gemini when key is set. Timeout 12s.
- UI: question box, visible step timeline (tool + snippet), final brief. Example questions: "What is our SLA for P1?", "Define dwell time".
- Seed 8 English ops docs + 6 glossary terms for fictional "Harbor Desk". No real clients.
- Pest: SLA question creates a job with at least one search_corpus step and a non-empty brief, FakeLlm bound, unknown term is a tool miss not a 500.
- README mermaid: Question → Agent → Tools (search/define) → Brief. How to run without API key.

## GitHub

- git config user.name "Oscar Munoz" / user.email "oscarxxi@gmail.com"
- Commit: feat: add agent research desk
- Public https://github.com/oscarmmi/agent-research-desk
- gh repo edit --description "Multi-step research agent with corpus search, glossary tools, and a sourced brief (Laravel + Vue)." --add-topic laravel --add-topic vue --add-topic ai --add-topic agents --add-topic php
- Stop. Print the URL.

No Impeccable. No Redis. No live web search in v1 (local corpus only).
