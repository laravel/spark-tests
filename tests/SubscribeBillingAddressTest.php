<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group stripe
 */
class SubscribeBillingAddressTest extends TestCase
{
    use DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_users_can_subscribe()
    {
        Spark::collectBillingAddress();

        $this->actingAs($user = factory(User::class)->create())
                ->json('POST', '/settings/subscription', [
                    'stripe_token' => $this->getStripeToken(),
                    'plan' => 'spark-test-1',
                    'address' => 'Test',
                    'city' => 'Test',
                    'state' => 'AR',
                    'zip' => '71901',
                    'country' => 'US',
                ])->assertSuccessful();

        $user = $user->fresh();

        $this->assertTrue($user->subscribed());
        $this->assertEquals('spark-test-1', $user->subscription()->stripe_plan);

        Spark::collectBillingAddress(false);
    }


    public function test_billing_address_country_must_match_stripe_country()
    {
        Spark::collectBillingAddress();

        $this->actingAs($user = factory(User::class)->create())
                ->json('POST', '/settings/subscription', [
                    'stripe_token' => $this->getStripeToken(),
                    'plan' => 'spark-test-1',
                    'address' => 'Test',
                    'city' => 'Test',
                    'state' => 'AR',
                    'zip' => '71901',
                    'country' => 'TV',
                ])->assertStatus(422);

        Spark::collectBillingAddress(false);
    }


    public function test_billing_address_state_must_be_valid_for_country()
    {
        Spark::collectBillingAddress();

        $this->actingAs($user = factory(User::class)->create())
                ->json('POST', '/settings/subscription', [
                    'stripe_token' => $this->getStripeToken(),
                    'plan' => 'spark-test-1',
                    'address' => 'Test',
                    'city' => 'Test',
                    'state' => 'TEST',
                    'zip' => '71901',
                    'country' => 'US',
                ])->assertStatus(422);

        Spark::collectBillingAddress(false);
    }
}
