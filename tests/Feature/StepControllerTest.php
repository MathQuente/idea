<?php

use App\Models\Idea;
use App\Models\Steps;
use App\Models\User;

test('owner can toggle a step of their own idea', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->create(['user_id' => $user->id]);
    $step = Steps::factory()->create(['idea_id' => $idea->id, 'completed' => false]);

    $response = $this->actingAs($user)->patch(route('step.update', $step));

    $response->assertRedirect();
    expect($step->fresh()->completed)->toBeTrue();
});

test('another user cannot toggle a step belonging to someone else idea', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $idea = Idea::factory()->create(['user_id' => $owner->id]);
    $step = Steps::factory()->create(['idea_id' => $idea->id, 'completed' => false]);

    $response = $this->actingAs($attacker)->patch(route('step.update', $step));

    $response->assertForbidden();
    expect($step->fresh()->completed)->toBeFalse();
});
