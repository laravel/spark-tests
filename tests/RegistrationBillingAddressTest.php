<?php

use App\User;
use Ramsey\Uuid\Uuid;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group stripe
 */
class RegistrationBillingAddressTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_user_can_register()
    {
        Spark::collectBillingAddress();

        $this->json('POST', '/register', [
            'plan' => 'spark-test-1',
            'stripe_token' => $this->getStripeToken(),
            'team' => 'Laravel',
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
            'address' => 'Test',
            'city' => 'Test',
            'state' => 'AR',
            'zip' => '71901',
            'country' => 'US',
            'terms' => true,
        ])->assertSuccessful();

        Spark::collectBillingAddress(false);
    }


    public function test_address_is_required()
    {
        Spark::collectBillingAddress();

        $this->json('POST', '/register', [
            'plan' => 'spark-test-1',
            'stripe_token' => $this->getStripeToken(),
            'team' => 'Laravel',
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
            'terms' => true,
        ])->assertStatus(422);

        Spark::collectBillingAddress(false);
    }


    public function test_declared_country_must_match_stripe_country()
    {
        Spark::collectBillingAddress();

        $response = $this->json('POST', '/register', [
            'plan' => 'spark-test-1',
            'stripe_token' => $this->getStripeToken(),
            'team' => 'Laravel',
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
            'address' => 'Test',
            'city' => 'Test',
            'state' => 'AR',
            'zip' => '71901',
            'country' => 'TV',
            'terms' => true,
        ])->assertStatus(422);
        
        $content = $response->decodeResponseJson();
        
        $this->assertEquals('This country does not match the origin country of your card.', $content['errors']['country'][0]);

        Spark::collectBillingAddress(false);
    }


    public function test_state_must_be_valid_for_country()
    {
        Spark::collectBillingAddress();

        $this->json('POST', '/register', [
            'plan' => 'spark-test-1',
            'stripe_token' => $this->getStripeToken(),
            'team' => 'Laravel',
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
            'address' => 'Test',
            'city' => 'Test',
            'state' => 'TEST',
            'zip' => '71901',
            'country' => 'US',
            'terms' => true,
        ])->assertStatus(422);

        Spark::collectBillingAddress(false);
    }
}
