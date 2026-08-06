<?php

use App\Models\Idea;
use App\Models\User;

test('a raw script tag in the description is not rendered unescaped on the show page', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->create([
        'user_id' => $user->id,
        'description' => "Nice idea\n\n<script>alert('xss')</script>",
    ]);

    $response = $this->actingAs($user)->get(route('idea.show', $idea));

    $response->assertOk();
    $response->assertDontSee('<script>alert', false);
});

test('an inline event handler attribute in the description is stripped', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->create([
        'user_id' => $user->id,
        'description' => '<img src=x onerror="alert(1)">',
    ]);

    $response = $this->actingAs($user)->get(route('idea.show', $idea));

    $response->assertOk();
    $response->assertDontSee('<img src=x onerror="alert(1)">', false);
});
