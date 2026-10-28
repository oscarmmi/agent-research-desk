<?php

test('the research desk renders its seeded local knowledge base', function () {
    $this->seed();
    $this->withoutVite();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('ResearchDesk')
            ->where('corpusCount', 8)
            ->where('glossaryCount', 6)
        );
});
