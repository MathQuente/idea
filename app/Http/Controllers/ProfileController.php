<?php

namespace App\Http\Controllers;

use App\Notifications\EmailChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function edit() {
        return view('profile.edit', [
            'user' => Auth::user()
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request) {

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
           'name' => ['required', 'string', 'max:255'],
           'email' => [
                'required', 'string', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id)
           ],
           'password' => ['nullable', Password::defaults()]
        ]);

        $originalEmail = $user->email;

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->filled('password') ? $request->password : $user->password
        ]);

        if($originalEmail !== $request->email) {
            Notification::route('mail', $originalEmail)
                ->notify(new EmailChanged($user, $originalEmail));
        }

        return redirect()->route('profile.edit')->with('success', 'Profile updated!');
    }
}
