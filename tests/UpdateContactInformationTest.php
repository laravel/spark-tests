<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateContactInformationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_contact_information_can_be_updated()
    {
        $this->actingAs(factory(User::class)->create())
                ->json('PUT', '/settings/contact', [
                    'name' => 'Taylor Otwell (Updated)',
                    'email' => 'taylor+updated@laravel.com',
                ]);

        $this->seeInDatabase('users', [
            'name' => 'Taylor Otwell (Updated)',
            'email' => 'taylor+updated@laravel.com',
        ]);
    }


    public function test_name_is_required()
    {
        $this->actingAs(factory(User::class)->create())
                ->json('PUT', '/settings/contact', [
                    'name' => '', 'email' => 'taylor@laravel.com',
                ])->assertStatus(422);
    }


    public function test_email_is_required()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)
                ->json('PUT', '/settings/contact', [
                    'name' => 'Taylor Otwell', 'email' => '',
                ])->assertStatus(422);
    }
}
