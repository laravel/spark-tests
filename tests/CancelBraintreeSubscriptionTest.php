<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group braintree
 */
class CancelBraintreeSubscriptionTest extends TestCase
{
    use DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_subscription_can_be_cancelled()
    {
        $user = $this->createBraintreeSubscribedUser('spark-test-1');

        $this->assertTrue($user->subscribed());
        $this->assertFalse($user->subscription()->onGracePeriod());

        $this->actingAs($user)
                ->json('DELETE', '/settings/subscription');

        $this->seeStatusCode(200);
        $this->assertTrue($user->subscription()->onGracePeriod());
    }
}
