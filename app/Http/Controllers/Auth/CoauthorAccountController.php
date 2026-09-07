<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SubmissionAuthor;
use App\Models\User;
use App\Notifications\CoauthorAccountInvitation;
use App\Services\Mail\WorkflowMailService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class CoauthorAccountController extends Controller
{
    public function edit(Request $request, string $token): View
    {
        return view('auth.coauthor-account', ['token' => $token, 'email' => $request->string('email')->toString()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset($data, function (User $user, string $password): void {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();

            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
                event(new Verified($user));
            }

            app(WorkflowMailService::class)->user($user, 'password_changed', 'Your account password was changed');
            event(new PasswordReset($user));
        });

        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('status', 'Password set. You can now log in.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function resend(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::query()->where('email', mb_strtolower(trim($data['email'])))->first();

        if ($user && SubmissionAuthor::query()->whereBelongsTo($user)->exists()) {
            $user->notify(new CoauthorAccountInvitation);
        }

        return back()->with('status', 'If this email belongs to a co-author, a new password link will be sent.');
    }
}
