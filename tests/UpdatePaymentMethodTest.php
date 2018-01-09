<?php

use App\User;
use Laravel\Spark\Spark;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Spark\Services\Stripe as StripeService;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group stripe
 */
class UpdatePaymentMethodTest extends TestCase
{
    use DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_payment_method_for_stripe_can_be_updated()
    {
        $user = $this->createSubscribedUser('spark-test-1');

        $this->actingAs($user)
                ->json('PUT', '/settings/payment-method', [
                    'stripe_token' => $this->getStripeToken(),
                ])->assertSuccessful();
    }


    public function test_stripe_token_is_required_to_update_payment_method()
    {
        $user = Mockery::mock(Authenticatable::class);

        $user->shouldReceive('updateCard')->never();

        $this->actingAs($user)
                ->json('PUT', '/settings/payment-method', [
                    'stripe_token' => '',
                ])->assertStatus(422);
    }
}
