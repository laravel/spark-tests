<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group braintree
 */
class SubscribeBraintreeTest extends TestCase
{
    use DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_users_can_subscribe()
    {
        $this->actingAs($user = factory(User::class)->create())
                ->json('POST', '/settings/subscription', [
                    'braintree_token' => 'fake-valid-nonce',
                    'braintree_type' => 'credit-card',
                    'plan' => 'spark-test-1',
                ]);

        $user = $user->fresh();

        $this->seeStatusCode(200);
        $this->assertTrue($user->subscribed());
        $this->assertEquals('spark-test-1', $user->subscription()->braintree_plan);
    }


    public function test_braintree_token_is_required()
    {
        $this->actingAs($user = factory(User::class)->create())
                ->json('POST', '/settings/subscription', [
                    'braintree_token' => '',
                    'plan' => 'spark-test-1',
                ])->seeStatusCode(422);
    }


    public function test_braintree_type_is_required()
    {
        $this->actingAs($user = factory(User::class)->create())
                ->json('POST', '/settings/subscription', [
                    'braintree_token' => 'fake-valid-nonce',
                    'braintree_type' => '',
                    'plan' => 'spark-test-1',
                ])->seeStatusCode(422);
    }


    public function test_plan_name_must_be_a_valid_plan()
    {
        $this->actingAs($user = factory(User::class)->create())
                ->json('POST', '/settings/subscription', [
                    'braintree_token' => 'fake-valid-nonce',
                    'plan' => 'spark-test-10',
                ])->seeStatusCode(422);
    }
}
