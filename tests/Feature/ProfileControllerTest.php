<?php

use App\Models\User;
use App\Notifications\EmailChanged;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('user can update their name and email', function () {
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('new@example.com');
});

test('email must be unique among other users', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'mine@example.com']);

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => 'taken@example.com',
    ]);

    $response->assertSessionHasErrors('email');
});

test('a user can keep their own email unchanged without a uniqueness error', function () {
    $user = User::factory()->create(['email' => 'mine@example.com']);

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => 'mine@example.com',
    ]);

    $response->assertSessionDoesntHaveErrors('email');
});

test('submitting the form with a blank password keeps the current password hash', function () {
    $user = User::factory()->create();
    $originalHash = $user->password;

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'password' => '',
    ]);

    $response->assertSessionDoesntHaveErrors();
    expect($user->fresh()->password)->toBe($originalHash);
});

test('submitting a new password updates the stored hash', function () {
    $user = User::factory()->create();
    $originalHash = $user->password;

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'a-brand-new-password',
    ]);

    $fresh = $user->fresh();
    expect($fresh->password)->not->toBe($originalHash);
    expect(Hash::check('a-brand-new-password', $fresh->password))->toBeTrue();
});

test('changing the email notifies the old address', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => 'new@example.com',
    ]);

    Notification::assertSentOnDemand(
        EmailChanged::class,
        fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'old@example.com'
    );
});

test('keeping the same email does not send a notification', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'same@example.com']);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => 'same@example.com',
    ]);

    Notification::assertNothingSent();
});
