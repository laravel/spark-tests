<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group stripe
 */
class UpdateSubscriptionTest extends TestCase
{
    use DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_subscription_can_be_updated()
    {
        $user = $this->createSubscribedUser('spark-test-1');

        $this->actingAs($user)
                ->json('PUT', '/settings/subscription', [
                    'plan' => 'spark-test-2',
                ]);

        $user = $user->fresh();

        $this->seeStatusCode(200);
        $this->assertTrue($user->subscribed());
        $this->assertEquals('spark-test-2', $user->subscription()->stripe_plan);
    }


    public function test_plan_is_required()
    {
        $this->actingAs(factory(User::class)->create())
                ->json('PUT', '/settings/subscription', [
                    'plan' => '',
                ])->seeStatusCode(422);
    }


    public function test_plan_must_be_a_valid_plan()
    {
        $this->actingAs(factory(User::class)->create())
                ->json('PUT', '/settings/subscription', [
                    'plan' => 'spark-test-10',
                ])->seeStatusCode(422);
    }
}
