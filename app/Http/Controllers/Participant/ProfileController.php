<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\ProfileRequest;
use App\Models\Country;
use App\Services\Mail\WorkflowMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('participant.profile', [
            'countries' => Country::query()->active()->ordered()->get(['id', 'name']),
            'defaultCountryId' => Country::query()->active()->where('iso2', 'ID')->value('id'),
            'user' => request()->user()->load('profile'),
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except('status_proof_file');
        $country = Country::query()->findOrFail($data['country_id']);
        $data['country_id'] = $country->id;
        $data['country'] = $country->name;

        if ($request->hasFile('status_proof_file')) {
            $data['status_proof_file'] = $request->file('status_proof_file')->store('profiles/'.$user->uuid);
        }

        $user->update([
            'name' => $data['full_name'],
            'whatsapp' => $data['whatsapp'],
            'institution' => $data['institution'],
            'country_id' => $country->id,
            'country' => $data['country'],
        ]);

        $user->profile()->updateOrCreate(['user_id' => $user->id], $data);

        app(WorkflowMailService::class)->user($user, 'profile_updated', 'Profile updated');

        return back()->with('status', 'Profile updated.');
    }
}
