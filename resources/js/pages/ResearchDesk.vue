<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

type ResearchStep = {
    id: number;
    tool_name: string;
    input: string;
    output: string;
    position: number;
};

type ResearchJob = {
    id: number;
    question: string;
    brief: string | null;
    status: 'queued' | 'done' | 'failed';
    steps: ResearchStep[];
    created_at: string;
};

type RecentJob = Pick<ResearchJob, 'id' | 'question' | 'status' | 'created_at'>;

const props = defineProps<{
    job: ResearchJob | null;
    recentJobs: RecentJob[];
    corpusCount: number;
    glossaryCount: number;
}>();

const form = useForm({ question: '' });
const examples = ['What is our SLA for P1?', 'Define dwell time'];
const jobDate = computed(() =>
    props.job ? new Date(props.job.created_at).toLocaleString() : '',
);

function ask(question = form.question) {
    form.question = question;
    form.post('/research', { preserveScroll: true });
}

function toolLabel(tool: string) {
    return (
        (
            {
                search_corpus: 'Corpus search',
                define_term: 'Glossary lookup',
                write_brief: 'Brief writer',
                research_error: 'Run status',
            } as Record<string, string>
        )[tool] ?? tool.replaceAll('_', ' ')
    );
}

function snippet(output: string) {
    return output.length > 460 ? `${output.slice(0, 460)}…` : output;
}
</script>

<template>
    <Head title="Research desk" />

    <div class="min-h-screen bg-[#f5f6f4] text-[#17241f]">
        <header class="border-b border-[#e2e7e3] bg-white">
            <div
                class="mx-auto flex max-w-[1440px] items-center justify-between px-5 py-4 sm:px-8"
            >
                <a
                    href="/"
                    class="flex items-center gap-3"
                    aria-label="Agent Research Desk home"
                >
                    <span
                        class="grid size-10 place-items-center rounded-xl bg-[#143e32] text-white"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            class="size-5"
                            aria-hidden="true"
                        >
                            <path
                                d="M4 5.5h10M4 10h7M4 14.5h5M15.5 13.5l2 2 3.5-4"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M16 4.5h4v4"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>
                    <span>
                        <span class="block text-sm font-semibold tracking-tight"
                            >Harbor Desk</span
                        >
                        <span class="block text-xs text-[#75827b]"
                            >Agent Research Desk</span
                        >
                    </span>
                </a>
                <div
                    class="hidden items-center gap-2 rounded-full border border-[#dce5df] bg-[#f8faf8] px-3 py-1.5 text-xs font-medium text-[#4c6358] sm:flex"
                >
                    <span class="size-1.5 rounded-full bg-[#3d9b71]" />
                    Local knowledge base
                </div>
            </div>
        </header>

        <main
            class="mx-auto grid max-w-[1440px] gap-8 px-5 py-8 sm:px-8 lg:grid-cols-[minmax(0,1fr)_300px] lg:gap-12 lg:py-12"
        >
            <section class="min-w-0">
                <div class="mb-8 max-w-3xl">
                    <div
                        class="mb-4 flex items-center gap-2 text-[11px] font-semibold tracking-[0.16em] text-[#507362] uppercase"
                    >
                        <span class="h-px w-6 bg-[#7ca38e]" />
                        Operations knowledge, on demand
                    </div>
                    <h1
                        class="max-w-2xl text-3xl leading-tight font-semibold tracking-[-0.04em] text-[#17382d] sm:text-[42px]"
                    >
                        Research the answer.<br class="hidden sm:block" />
                        Keep the source in view.
                    </h1>
                    <p
                        class="mt-4 max-w-2xl text-sm leading-6 text-[#65746c] sm:text-base"
                    >
                        Ask a question about Harbor Desk operations. The agent
                        searches its local playbooks, checks terminology when
                        useful, and builds a short, sourced brief.
                    </p>
                </div>

                <form
                    class="rounded-2xl border border-[#dce4de] bg-white p-3 shadow-[0_10px_35px_-30px_rgba(23,56,45,0.35)] sm:p-4"
                    @submit.prevent="ask()"
                >
                    <label
                        for="question"
                        class="mb-2 block px-1 text-xs font-semibold text-[#314b3e]"
                        >What do you need to know?</label
                    >
                    <textarea
                        id="question"
                        v-model="form.question"
                        rows="3"
                        maxlength="1000"
                        placeholder="Ask about a service target, shift procedure, or operational term…"
                        class="w-full resize-y border-0 bg-transparent px-1 py-2 text-[15px] leading-6 text-[#1a2b23] outline-none placeholder:text-[#9aa59f] focus:ring-0"
                        required
                    />
                    <div
                        class="flex flex-col gap-3 border-t border-[#edf0ed] pt-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <span class="px-1 text-xs text-[#87928b]"
                            >Answers are grounded in the local reference
                            set.</span
                        >
                        <button
                            type="submit"
                            :disabled="
                                form.processing ||
                                form.question.trim().length < 3
                            "
                            class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-[#174b3a] px-4 text-sm font-semibold text-white transition hover:bg-[#103d2f] disabled:cursor-not-allowed disabled:opacity-55"
                        >
                            <svg
                                v-if="form.processing"
                                class="size-4 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                    stroke="currentColor"
                                    stroke-opacity=".3"
                                    stroke-width="3"
                                />
                                <path
                                    d="M21 12a9 9 0 0 0-9-9"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <svg
                                v-else
                                class="size-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 12h14m-6-6 6 6-6 6"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            {{
                                form.processing
                                    ? 'Researching'
                                    : 'Research answer'
                            }}
                        </button>
                    </div>
                    <p
                        v-if="form.errors.question"
                        class="px-1 pt-2 text-sm text-[#a34435]"
                    >
                        {{ form.errors.question }}
                    </p>
                </form>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="mr-1 text-xs font-medium text-[#7b8981]"
                        >Try an example</span
                    >
                    <button
                        v-for="example in examples"
                        :key="example"
                        type="button"
                        class="rounded-full border border-[#d9e2dc] bg-white px-3 py-1.5 text-xs text-[#456052] transition hover:border-[#98b5a3] hover:bg-[#f1f7f3]"
                        @click="ask(example)"
                    >
                        {{ example }}
                    </button>
                </div>

                <section v-if="job" class="mt-10" aria-labelledby="run-heading">
                    <div
                        class="mb-5 flex flex-wrap items-end justify-between gap-3"
                    >
                        <div>
                            <div
                                class="mb-1 text-[11px] font-semibold tracking-[0.14em] text-[#789083] uppercase"
                            >
                                Research run · #{{ job.id }}
                            </div>
                            <h2
                                id="run-heading"
                                class="max-w-3xl text-lg leading-snug font-semibold text-[#20382d]"
                            >
                                {{ job.question }}
                            </h2>
                        </div>
                        <span
                            class="rounded-full px-2.5 py-1 text-[11px] font-semibold capitalize"
                            :class="
                                job.status === 'done'
                                    ? 'bg-[#e4f3e9] text-[#32724e]'
                                    : job.status === 'failed'
                                      ? 'bg-[#f9e8e5] text-[#a34435]'
                                      : 'bg-[#f8efdb] text-[#8a6721]'
                            "
                        >
                            {{ job.status }}
                        </span>
                    </div>

                    <div
                        class="grid gap-5 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]"
                    >
                        <div
                            class="rounded-2xl border border-[#e0e6e1] bg-white p-5 sm:p-6"
                        >
                            <div class="mb-5 flex items-center justify-between">
                                <h3
                                    class="text-sm font-semibold text-[#304a3d]"
                                >
                                    Agent timeline
                                </h3>
                                <span class="text-xs text-[#94a098]"
                                    >{{ job.steps.length }} steps</span
                                >
                            </div>
                            <ol v-if="job.steps.length" class="space-y-0">
                                <li
                                    v-for="(step, index) in job.steps"
                                    :key="step.id"
                                    class="relative pb-6 last:pb-0"
                                >
                                    <span
                                        v-if="index < job.steps.length - 1"
                                        class="absolute top-7 left-[13px] h-full w-px bg-[#e2e9e4]"
                                    />
                                    <div class="relative flex gap-3">
                                        <span
                                            class="z-10 grid size-[27px] shrink-0 place-items-center rounded-full border border-[#dbe7de] bg-[#f3f8f4] text-[10px] font-bold text-[#47755b]"
                                            >{{
                                                String(step.position).padStart(
                                                    2,
                                                    '0',
                                                )
                                            }}</span
                                        >
                                        <div class="min-w-0 flex-1 pt-0.5">
                                            <div
                                                class="flex flex-wrap items-center justify-between gap-2"
                                            >
                                                <span
                                                    class="text-xs font-semibold text-[#304a3d]"
                                                    >{{
                                                        toolLabel(
                                                            step.tool_name,
                                                        )
                                                    }}</span
                                                >
                                                <span
                                                    class="rounded-md bg-[#f2f5f2] px-1.5 py-0.5 font-mono text-[10px] text-[#718078]"
                                                    >{{ step.tool_name }}</span
                                                >
                                            </div>
                                            <p
                                                class="mt-1 text-xs leading-5 text-[#68776f]"
                                            >
                                                {{ snippet(step.output) }}
                                            </p>
                                        </div>
                                    </div>
                                </li>
                            </ol>
                            <p v-else class="text-sm leading-6 text-[#7b8981]">
                                This job has no completed tool steps yet.
                            </p>
                        </div>

                        <article
                            class="overflow-hidden rounded-2xl border border-[#d7e3da] bg-[#fbfcfa]"
                        >
                            <div
                                class="flex items-center justify-between border-b border-[#e6ece7] px-5 py-4 sm:px-6"
                            >
                                <div>
                                    <div
                                        class="text-[10px] font-semibold tracking-[0.15em] text-[#779083] uppercase"
                                    >
                                        Research output
                                    </div>
                                    <h3
                                        class="mt-1 text-sm font-semibold text-[#304a3d]"
                                    >
                                        Brief with sources
                                    </h3>
                                </div>
                                <span
                                    class="grid size-8 place-items-center rounded-lg bg-[#eaf2ec] text-[#47755b]"
                                    aria-hidden="true"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        class="size-4"
                                    >
                                        <path
                                            d="M7 4.75h7l4 4v10.5H7a2 2 0 0 1-2-2v-10a2.5 2.5 0 0 1 2-2.5Z"
                                            stroke="currentColor"
                                            stroke-width="1.6"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            d="M14 5v4h4M8.5 13h7M8.5 16h7"
                                            stroke="currentColor"
                                            stroke-width="1.6"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </span>
                            </div>
                            <div
                                v-if="job.brief"
                                class="brief-copy px-5 py-5 text-[13px] leading-7 whitespace-pre-wrap text-[#394c41] sm:px-6"
                            >
                                {{ job.brief }}
                            </div>
                            <div
                                v-else
                                class="px-5 py-8 text-sm text-[#8a968f] sm:px-6"
                            >
                                The brief will appear when the agent finishes.
                            </div>
                            <div
                                class="border-t border-[#e6ece7] px-5 py-3 text-[11px] text-[#829087] sm:px-6"
                            >
                                Run completed {{ jobDate }}
                            </div>
                        </article>
                    </div>
                </section>

                <section
                    v-else
                    class="mt-10 rounded-2xl border border-dashed border-[#d5dfd8] bg-white/60 px-6 py-8 text-center"
                >
                    <span
                        class="mx-auto grid size-11 place-items-center rounded-full bg-[#eaf2ec] text-[#47755b]"
                    >
                        <svg viewBox="0 0 24 24" fill="none" class="size-5">
                            <path
                                d="M12 3.5v3m0 11v3m8.5-8.5h-3m-11 0h-3m14.01-6.01-2.12 2.12m-7.78 7.78-2.12 2.12m12.02 0-2.12-2.12m-7.78-7.78L6.49 6.49"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            />
                        </svg>
                    </span>
                    <h2 class="mt-3 text-sm font-semibold text-[#304a3d]">
                        Your research trail appears here
                    </h2>
                    <p
                        class="mx-auto mt-1 max-w-md text-xs leading-5 text-[#829087]"
                    >
                        Each tool call is saved to the run, so you can see what
                        the agent searched and how it formed its answer.
                    </p>
                </section>
            </section>

            <aside class="space-y-5 lg:pt-2">
                <section
                    class="rounded-2xl border border-[#e0e6e1] bg-white p-5"
                >
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-[#304a3d]">
                            Knowledge base
                        </h2>
                        <span
                            class="rounded-full bg-[#edf5ef] px-2 py-1 text-[10px] font-semibold text-[#4a785c]"
                            >LOCAL ONLY</span
                        >
                    </div>
                    <p class="mt-2 text-xs leading-5 text-[#7b8981]">
                        The agent only retrieves from this fictional operations
                        library. No live web search.
                    </p>
                    <div class="mt-5 grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-[#f5f8f5] p-3">
                            <div
                                class="text-xl font-semibold tracking-tight text-[#244b39]"
                            >
                                {{ corpusCount }}
                            </div>
                            <div class="mt-0.5 text-[10px] text-[#7b8981]">
                                playbooks
                            </div>
                        </div>
                        <div class="rounded-xl bg-[#f5f8f5] p-3">
                            <div
                                class="text-xl font-semibold tracking-tight text-[#244b39]"
                            >
                                {{ glossaryCount }}
                            </div>
                            <div class="mt-0.5 text-[10px] text-[#7b8981]">
                                terms
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 space-y-3 border-t border-[#edf0ed] pt-4">
                        <div class="flex gap-3">
                            <span
                                class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-md bg-[#edf5ef] text-[#47755b]"
                                ><svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    class="size-3.5"
                                >
                                    <circle
                                        cx="10.8"
                                        cy="10.8"
                                        r="6.3"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    />
                                    <path
                                        d="m15.5 15.5 4 4"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                    /></svg
                            ></span>
                            <div>
                                <div class="text-xs font-medium text-[#455a4e]">
                                    search_corpus
                                </div>
                                <div
                                    class="text-[11px] leading-4 text-[#87938c]"
                                >
                                    TF-IDF ranks up to 3 matching docs
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span
                                class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-md bg-[#edf5ef] text-[#47755b]"
                                ><svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    class="size-3.5"
                                >
                                    <path
                                        d="M5 5.5h14M5 10h10M5 14.5h7M5 19h14"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                    /></svg
                            ></span>
                            <div>
                                <div class="text-xs font-medium text-[#455a4e]">
                                    define_term
                                </div>
                                <div
                                    class="text-[11px] leading-4 text-[#87938c]"
                                >
                                    Exact glossary lookup, including misses
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span
                                class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-md bg-[#edf5ef] text-[#47755b]"
                                ><svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    class="size-3.5"
                                >
                                    <path
                                        d="M6 4.5h12v15H6zM9 9h6M9 12h6M9 15h4"
                                        stroke="currentColor"
                                        stroke-width="1.6"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    /></svg
                            ></span>
                            <div>
                                <div class="text-xs font-medium text-[#455a4e]">
                                    write_brief
                                </div>
                                <div
                                    class="text-[11px] leading-4 text-[#87938c]"
                                >
                                    Finalizes a concise, cited answer
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    class="rounded-2xl border border-[#e0e6e1] bg-white p-5"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-[#304a3d]">
                            Recent research
                        </h2>
                        <span class="text-[10px] text-[#98a29c]"
                            >{{ recentJobs.length }} runs</span
                        >
                    </div>
                    <ul v-if="recentJobs.length" class="space-y-1">
                        <li v-for="recent in recentJobs" :key="recent.id">
                            <Link
                                :href="`/?job=${recent.id}`"
                                class="group flex items-start gap-2.5 rounded-lg px-2 py-2 transition hover:bg-[#f4f8f4]"
                            >
                                <span
                                    class="mt-1.5 size-1.5 shrink-0 rounded-full"
                                    :class="
                                        recent.status === 'done'
                                            ? 'bg-[#68a47c]'
                                            : 'bg-[#d1a647]'
                                    "
                                />
                                <span
                                    class="line-clamp-2 text-xs leading-5 text-[#65746c] group-hover:text-[#345344]"
                                    >{{ recent.question }}</span
                                >
                            </Link>
                        </li>
                    </ul>
                    <p v-else class="text-xs leading-5 text-[#8b9790]">
                        Your latest runs will be listed here.
                    </p>
                </section>

                <div
                    class="rounded-xl bg-[#e9efeb] px-4 py-3 text-[11px] leading-5 text-[#62756a]"
                >
                    <span class="font-semibold text-[#435f4e]"
                        >Demo workspace</span
                    ><br />
                    Harbor Desk is a fictional operations team. All policies and
                    examples are synthetic.
                </div>
            </aside>
        </main>
        <footer
            class="mx-auto max-w-[1440px] px-5 pb-8 text-[10px] text-[#9aa49e] sm:px-8"
        >
            Agent Research Desk · Local corpus retrieval · No external search
        </footer>
    </div>
</template>
