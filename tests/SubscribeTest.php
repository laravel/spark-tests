<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group stripe
 */
class SubscribeTest extends TestCase
{
    use DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_users_can_subscribe()
    {
        $this->actingAs($user = factory(User::class)->create())
                ->json('POST', '/settings/subscription', [
                    'stripe_token' => $this->getStripeToken(),
                    'plan' => 'spark-test-1',
                ])->assertSuccessful();

        $user = $user->fresh();

        $this->assertTrue($user->subscribed());
        $this->assertEquals('spark-test-1', $user->subscription()->stripe_plan);
    }


    public function test_stripe_token_is_required()
    {
        $this->actingAs($user = factory(User::class)->create())
                ->json('POST', '/settings/subscription', [
                    'stripe_token' => '',
                    'existing_card' => 0,
                    'plan' => 'spark-test-1',
                ])->assertStatus(422);
    }


    public function test_plan_name_must_be_a_valid_plan()
    {
        $this->actingAs($user = factory(User::class)->create())
                ->json('POST', '/settings/subscription', [
                    'stripe_token' => $this->getStripeToken(),
                    'existing_card' => 0,
                    'plan' => 'spark-test-10',
                ])->assertStatus(422);
    }
}
