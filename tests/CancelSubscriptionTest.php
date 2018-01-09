<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group stripe
 */
class CancelSubscriptionTest extends TestCase
{
    use DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_subscription_can_be_cancelled()
    {
        $user = $this->createSubscribedUser('spark-test-1');

        $this->assertTrue($user->subscribed());
        $this->assertFalse($user->subscription()->onGracePeriod());

        $this->actingAs($user)
                ->json('DELETE', '/settings/subscription')->assertSuccessful();

        $this->assertTrue($user->subscription()->onGracePeriod());
    }
}
