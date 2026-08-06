<?php

use App\Actions\UpdateIdea;
use App\IdeaStatus;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('owner can remove the image from their idea', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $path = UploadedFile::fake()->image('cover.jpg')->store('ideas', 'public');
    $idea = Idea::factory()->create(['user_id' => $user->id, 'image_path' => $path]);

    $response = $this->actingAs($user)->delete(route('idea.image.destroy', $idea));

    $response->assertRedirect();
    Storage::disk('public')->assertMissing($path);
    expect($idea->fresh()->image_path)->toBeNull();
});

test('deleting the image of an idea that has none does not error', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $idea = Idea::factory()->create(['user_id' => $user->id, 'image_path' => null]);

    $response = $this->actingAs($user)->delete(route('idea.image.destroy', $idea));

    $response->assertRedirect();
    expect($idea->fresh()->image_path)->toBeNull();
});

test('another user cannot remove the image from someone else idea', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $other = User::factory()->create();
    $path = UploadedFile::fake()->image('cover.jpg')->store('ideas', 'public');
    $idea = Idea::factory()->create(['user_id' => $owner->id, 'image_path' => $path]);

    $response = $this->actingAs($other)->delete(route('idea.image.destroy', $idea));

    $response->assertForbidden();
    Storage::disk('public')->assertExists($path);
});

test('replacing the image deletes the previously stored file', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $oldPath = UploadedFile::fake()->image('old.jpg')->store('ideas', 'public');
    $idea = Idea::factory()->create(['user_id' => $user->id, 'image_path' => $oldPath]);

    (new UpdateIdea())->handle([
        'title' => $idea->title,
        'status' => IdeaStatus::PENDING,
        'image' => UploadedFile::fake()->image('new.jpg'),
    ], $idea);

    Storage::disk('public')->assertMissing($oldPath);
    expect($idea->fresh()->image_path)->not->toBe($oldPath);
});
