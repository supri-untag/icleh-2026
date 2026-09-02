<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('participant.profile', [
            'user' => request()->user()->load('profile'),
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except('status_proof_file');

        if ($request->hasFile('status_proof_file')) {
            $data['status_proof_file'] = $request->file('status_proof_file')->store('profiles/'.$user->uuid);
        }

        $user->update([
            'name' => $data['full_name'],
            'whatsapp' => $data['whatsapp'],
            'institution' => $data['institution'],
            'country' => $data['country'],
        ]);

        $user->profile()->updateOrCreate(['user_id' => $user->id], $data);

        return back()->with('status', 'Profile updated.');
    }
}
