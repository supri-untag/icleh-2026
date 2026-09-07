<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    public function test_guest_can_register_with_country_relation(): void
    {
        Event::fake([Registered::class]);

        $country = Country::query()->where('iso2', 'MY')->firstOrFail();

        $this->post(route('register'), [
            'name' => 'Registered Participant',
            'email' => 'participant@example.test',
            'whatsapp' => '+60123456789',
            'institution' => 'ICLEH Test University',
            'country_id' => $country->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'consent' => '1',
        ])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHasNoErrors();

        $user = User::query()
            ->where('email', 'participant@example.test')
            ->with('profile')
            ->firstOrFail();

        $this->assertSame($country->id, $user->country_id);
        $this->assertSame($country->name, $user->country);
        $this->assertSame($country->id, $user->profile->country_id);
        $this->assertSame($country->name, $user->profile->country);

        Event::assertDispatched(Registered::class);
    }
}
