<?php

use App\IdeaStatus;
use App\Models\Idea;
use App\Models\User;

test('index only lists ideas belonging to the authenticated user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mine = Idea::factory()->create(['user_id' => $user->id]);
    Idea::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->get(route('idea.index'));

    $response->assertOk();
    $response->assertViewHas('ideas', function ($ideas) use ($mine) {
        return $ideas->count() === 1 && $ideas->first()->is($mine);
    });
});

test('index filters by status', function () {
    $user = User::factory()->create();

    Idea::factory()->create(['user_id' => $user->id, 'status' => IdeaStatus::PENDING]);
    $completed = Idea::factory()->create(['user_id' => $user->id, 'status' => IdeaStatus::COMPLETED]);

    $response = $this->actingAs($user)->get(route('idea.index', ['status' => IdeaStatus::COMPLETED->value]));

    $response->assertViewHas('ideas', function ($ideas) use ($completed) {
        return $ideas->count() === 1 && $ideas->first()->is($completed);
    });
});

test('guest is redirected away from the idea index', function () {
    $this->get(route('idea.index'))->assertRedirect(route('login'));
});

test('store creates an idea with steps for the authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('idea.store'), [
        'title' => 'My idea',
        'status' => IdeaStatus::PENDING->value,
        'steps' => [
            ['description' => 'First step'],
        ],
    ]);

    $response->assertRedirect(route('idea.index'));

    $idea = Idea::where('title', 'My idea')->firstOrFail();
    expect($idea->user_id)->toBe($user->id);
    expect($idea->steps)->toHaveCount(1);
});

test('store fails validation without a title', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('idea.store'), [
        'status' => IdeaStatus::PENDING->value,
    ]);

    $response->assertSessionHasErrors('title');
});

test('store returns a validation error instead of a 500 when a step has no description', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('idea.store'), [
        'title' => 'My idea',
        'status' => IdeaStatus::PENDING->value,
        'steps' => [
            ['completed' => true],
        ],
    ]);

    $response->assertSessionHasErrors('steps.0.description');
});

test('owner can view their idea', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get(route('idea.show', $idea))->assertOk();
});

test('another user cannot view someone else idea', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $idea = Idea::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)->get(route('idea.show', $idea))->assertForbidden();
});

test('owner can update their idea', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->patch(route('idea.update', $idea), [
        'title' => 'Updated title',
        'status' => IdeaStatus::PENDING->value,
    ]);

    $response->assertRedirect();
    expect($idea->fresh()->title)->toBe('Updated title');
});

test('another user cannot update someone else idea', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $idea = Idea::factory()->create(['user_id' => $owner->id, 'title' => 'Original']);

    $response = $this->actingAs($other)->patch(route('idea.update', $idea), [
        'title' => 'Hacked title',
        'status' => IdeaStatus::PENDING->value,
    ]);

    $response->assertForbidden();
    expect($idea->fresh()->title)->toBe('Original');
});

test('update returns a validation error instead of a 500 when a step has no description', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->patch(route('idea.update', $idea), [
        'title' => 'Updated title',
        'status' => IdeaStatus::PENDING->value,
        'steps' => [
            ['completed' => true],
        ],
    ]);

    $response->assertSessionHasErrors('steps.0.description');
});

test('owner can delete their idea', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete(route('idea.destroy', $idea));

    $response->assertRedirect(route('idea.index'));
    expect(Idea::find($idea->id))->toBeNull();
});

test('another user cannot delete someone else idea', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $idea = Idea::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($other)->delete(route('idea.destroy', $idea));

    $response->assertForbidden();
    expect(Idea::find($idea->id))->not->toBeNull();
});
