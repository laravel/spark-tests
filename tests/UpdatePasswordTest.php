<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdatePasswordTest extends TestCase
{
    use DatabaseMigrations;

    public function test_password_can_be_updated()
    {
        $user = factory(User::class)->create(['password' => bcrypt('secret')]);

        $this->actingAs($user)
                ->json('PUT', '/settings/password', [
                    'current_password' => 'secret',
                    'password' => 'secret-updated',
                    'password_confirmation' => 'secret-updated',
                ])->assertSuccessful();

        $user = $user->fresh();

        $this->assertTrue(Hash::check('secret-updated', $user->password));
    }


    public function test_old_password_is_required()
    {
        $user = factory(User::class)->create(['password' => bcrypt('secret')]);

        $this->actingAs($user)
                ->json('PUT', '/settings/password', [
                    'current_password' => '',
                    'password' => 'secret-updated',
                    'password_confirmation' => 'secret-updated',
                ])->assertStatus(422);
    }


    public function test_old_password_must_match_current_password()
    {
        $user = factory(User::class)->create(['password' => bcrypt('secret')]);

        $this->actingAs($user)
                ->json('PUT', '/settings/password', [
                    'current_password' => 'wrong-secret',
                    'password' => 'secret-updated',
                    'password_confirmation' => 'secret-updated',
                ])->assertStatus(422);
    }


    public function test_new_passwords_are_required()
    {
        $user = factory(User::class)->create(['password' => bcrypt('secret')]);

        $this->actingAs($user)
                ->json('PUT', '/settings/password', [
                    'current_password' => 'secret',
                    'password' => '',
                    'password_confirmation' => 'secret-updated',
                ])->assertStatus(422);

        $this->actingAs($user)
                ->json('PUT', '/settings/password', [
                    'current_password' => 'secret',
                    'password' => 'secret-updated',
                    'password_confirmation' => '',
                ])->assertStatus(422);
    }


    public function test_new_passwords_must_match()
    {
        $user = factory(User::class)->create(['password' => bcrypt('secret')]);

        $this->actingAs($user)
                ->json('PUT', '/settings/password', [
                    'current_password' => 'secret',
                    'password' => 'secret-updated',
                    'password_confirmation' => 'secret-updated-2',
                ])->assertStatus(422);
    }


    public function test_new_passwords_must_be_at_least_six_characters()
    {
        $user = factory(User::class)->create(['password' => bcrypt('secret')]);

        $this->actingAs($user)
                ->json('PUT', '/settings/password', [
                    'current_password' => 'secret',
                    'password' => 'hello',
                    'password_confirmation' => 'hello',
                ])->assertStatus(422);
    }
}
