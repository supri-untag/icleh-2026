<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisteredUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisteredUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'whatsapp' => $data['whatsapp'],
            'institution' => $data['institution'],
            'country' => $data['country'],
            'password' => $data['password'],
        ]);

        $user->profile()->create([
            'full_name' => $user->name,
            'whatsapp' => $user->whatsapp,
            'institution' => $user->institution,
            'country' => $user->country,
        ]);

        $participantRole = Role::query()->where('name', UserRole::Participant->value)->first();

        if ($participantRole) {
            $user->roles()->attach($participantRole);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
