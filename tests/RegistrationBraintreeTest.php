<?php

use App\User;
use Ramsey\Uuid\Uuid;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group braintree
 */
class RegistrationBraintreeTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_user_can_subscribe_to_plan()
    {
        $this->json('POST', '/register', [
            'plan' => 'spark-test-1',
            'braintree_token' => 'fake-valid-nonce',
            'braintree_type' => 'credit-card',
            'team' => 'Laravel',
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
            'terms' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'taylor@laravel.com',
        ]);

        $user = User::where('email', 'taylor@laravel.com')->first();

        $this->assertTrue($user->subscribed());
        $this->assertEquals('spark-test-1', $user->subscription()->braintree_plan);
    }
}
