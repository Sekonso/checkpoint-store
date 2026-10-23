<?php

use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\ArticleFixtures;
use Tests\Support\UserFixtures;

it('renders the article creation form', function () {
    $admin = UserFixtures::admin();

    ArticleFixtures::tags();

    $this->actingAs($admin)
        ->get('/admin/articles/create')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Articles/create')
            ->has('tagOptions', 2)
            ->has('statusOptions', 3)
            ->where('statusOptions.0.value', 'draft')
            ->where('statusOptions.2.value', 'archived')
        );
});
