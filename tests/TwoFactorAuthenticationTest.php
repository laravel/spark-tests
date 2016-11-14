<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TwoFactorAuthenticationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_authentication_can_be_enabled()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)
                ->json('POST', '/settings/two-factor-auth', [
                    'country_code' => '1',
                    'phone' => '4792266733',
                ]);

        $this->seeStatusCode(200);

        $user = $user->fresh();

        $this->assertTrue(! is_null($user->authy_id));
    }


    public function test_country_code_is_required()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)
                ->json('POST', '/settings/two-factor-auth', [
                    'country_code' => '',
                    'phone' => '4792266733',
                ]);

        $this->seeStatusCode(422);
    }


    public function test_phone_is_required()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)
                ->json('POST', '/settings/two-factor-auth', [
                    'country_code' => '1',
                    'phone' => '',
                ]);

        $this->seeStatusCode(422);
    }


    public function test_authentication_can_be_disabled()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)
                ->json('POST', '/settings/two-factor-auth', [
                    'country_code' => '1',
                    'phone' => '4792266733',
                ]);

        $this->seeStatusCode(200);

        $user = $user->fresh();
        $this->assertTrue(! is_null($user->authy_id));

        $this->actingAs($user)
                ->json('DELETE', '/settings/two-factor-auth', []);

        $this->seeStatusCode(200);

        $user = $user->fresh();
        $this->assertTrue(is_null($user->authy_id));
    }
}
