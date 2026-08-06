<?php

use App\Models\Idea;
use App\Models\User;
use App\Policies\IdeaPolicy;

test('workWith allows the owner', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->create(['user_id' => $user->id]);

    expect((new IdeaPolicy())->workWith($user, $idea))->toBeTrue();
});

test('workWith denies another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $idea = Idea::factory()->create(['user_id' => $owner->id]);

    expect((new IdeaPolicy())->workWith($other, $idea))->toBeFalse();
});
