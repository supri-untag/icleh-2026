<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Role;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    public function test_admin_can_create_update_and_delete_faq_entry(): void
    {
        $admin = User::query()->where('email', config('icleh.admin.email'))->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.crud.store', 'faqs'), [
                'question' => 'Can admin manage CRUD?',
                'answer' => 'Yes, through the admin CRUD controller.',
                'display_order' => 50,
                'active' => 1,
            ])
            ->assertRedirect(route('admin.content.faqs'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('faqs', [
            'question' => 'Can admin manage CRUD?',
            'answer' => 'Yes, through the admin CRUD controller.',
            'display_order' => 50,
            'active' => 1,
        ]);

        $faq = Faq::query()->where('question', 'Can admin manage CRUD?')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.crud.edit', ['faqs', $faq->getRouteKey()]))
            ->assertOk()
            ->assertSee('Edit FAQ');

        $this->actingAs($admin)
            ->put(route('admin.crud.update', ['faqs', $faq->getRouteKey()]), [
                'question' => 'Can admin update CRUD?',
                'answer' => 'Yes, updates are persisted.',
                'display_order' => 51,
                'active' => 0,
            ])
            ->assertRedirect(route('admin.content.faqs'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('faqs', [
            'id' => $faq->id,
            'question' => 'Can admin update CRUD?',
            'answer' => 'Yes, updates are persisted.',
            'display_order' => 51,
            'active' => 0,
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.crud.destroy', ['faqs', $faq->getRouteKey()]))
            ->assertOk()
            ->assertJson(['message' => 'FAQ deleted successfully.']);

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_admin_can_create_update_and_delete_user_with_roles(): void
    {
        $admin = User::query()->where('email', config('icleh.admin.email'))->firstOrFail();
        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
        $email = 'crud-user@example.test';

        $this->actingAs($admin)
            ->post(route('admin.crud.store', 'users'), [
                'name' => 'CRUD User',
                'email' => $email,
                'whatsapp' => '+6200000001',
                'institution' => 'ICLEH Test',
                'country' => 'Indonesia',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'email_verified' => 1,
                'role_ids' => [$adminRole->id],
            ])
            ->assertRedirect(route('admin.system.users'))
            ->assertSessionHasNoErrors();

        $user = User::query()->where('email', $email)->with('roles')->firstOrFail();

        $this->assertNotNull($user->email_verified_at);
        $this->assertSame(['admin'], $user->roles->pluck('name')->all());

        $this->actingAs($admin)
            ->put(route('admin.crud.update', ['users', $user->getRouteKey()]), [
                'name' => 'CRUD User Updated',
                'email' => $email,
                'whatsapp' => '+6200000002',
                'institution' => 'ICLEH Test Updated',
                'country' => 'Indonesia',
                'password' => '',
                'password_confirmation' => '',
                'email_verified' => 0,
                'role_ids' => [],
            ])
            ->assertRedirect(route('admin.system.users'))
            ->assertSessionHasNoErrors();

        $user->refresh()->load('roles');

        $this->assertNull($user->email_verified_at);
        $this->assertSame('CRUD User Updated', $user->name);
        $this->assertSame(0, $user->roles->count());

        $this->actingAs($admin)
            ->deleteJson(route('admin.crud.destroy', ['users', $user->getRouteKey()]))
            ->assertOk()
            ->assertJson(['message' => 'User deleted successfully.']);

        $this->assertModelMissing($user);
    }

    public function test_admin_can_upload_replace_and_delete_speaker_photo(): void
    {
        Storage::fake('public');

        $admin = User::query()->where('email', config('icleh.admin.email'))->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.crud.store', 'speakers'), [
                'type' => 'speaker',
                'name' => 'Speaker With Photo',
                'title' => 'Prof.',
                'affiliation' => 'ICLEH Test',
                'country' => 'Indonesia',
                'biography' => 'Speaker biography.',
                'topic_title' => 'Conference Topic',
                'attendance_mode' => 'hybrid',
                'display_order' => 20,
                'active' => 1,
                'photo_file' => UploadedFile::fake()->image('speaker.jpg', 600, 600),
            ])
            ->assertRedirect(route('admin.conference.speakers'))
            ->assertSessionHasNoErrors();

        $speaker = Speaker::query()->where('name', 'Speaker With Photo')->firstOrFail();

        $this->assertNotNull($speaker->photo);
        Storage::disk('public')->assertExists($speaker->photo);

        $oldPhoto = $speaker->photo;

        $this->actingAs($admin)
            ->put(route('admin.crud.update', ['speakers', $speaker->getRouteKey()]), [
                'type' => 'speaker',
                'name' => 'Speaker With Replaced Photo',
                'title' => 'Prof.',
                'affiliation' => 'ICLEH Test',
                'country' => 'Indonesia',
                'biography' => 'Speaker biography.',
                'topic_title' => 'Conference Topic',
                'attendance_mode' => 'hybrid',
                'display_order' => 21,
                'active' => 1,
                'photo_file' => UploadedFile::fake()->image('speaker-replacement.png', 600, 600),
            ])
            ->assertRedirect(route('admin.conference.speakers'))
            ->assertSessionHasNoErrors();

        $speaker->refresh();

        $this->assertNotSame($oldPhoto, $speaker->photo);
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($speaker->photo);

        $newPhoto = $speaker->photo;

        $this->actingAs($admin)
            ->deleteJson(route('admin.crud.destroy', ['speakers', $speaker->getRouteKey()]))
            ->assertOk()
            ->assertJson(['message' => 'Speaker deleted successfully.']);

        Storage::disk('public')->assertMissing($newPhoto);
        $this->assertModelMissing($speaker);
    }
}
