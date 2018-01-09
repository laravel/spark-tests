<?php

use App\User;
use Laravel\Spark\Spark;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group braintree
 */
class UpdateBraintreePaymentMethodTest extends TestCase
{
    use DatabaseMigrations;

    public function test_payment_method_for_braintree_can_be_updated()
    {
        $user = Mockery::mock(Authenticatable::class);

        $user->braintree_id = 'test_braintree_id';
        $user->shouldReceive('updateCard')->once()->with('fake-valid-nonce');

        $this->actingAs($user)
                ->json('PUT', '/settings/payment-method', [
                    'braintree_token' => 'fake-valid-nonce',
                    'braintree_type' => 'credit-card',
                ])->assertSuccessful();
    }


    public function test_braintree_token_is_required_to_update_payment_method()
    {
        $user = Mockery::mock(Authenticatable::class);

        $user->shouldReceive('updateCard')->never();

        $this->actingAs($user)
                ->json('PUT', '/settings/payment-method', [
                    'braintree_token' => '',
                    'braintree_type' => 'credit-card',
                ])->assertStatus(422);
    }


    public function test_braintree_type_is_required_to_update_payment_method()
    {
        $user = Mockery::mock(Authenticatable::class);

        $user->shouldReceive('updateCard')->never();

        $this->actingAs($user)
                ->json('PUT', '/settings/payment-method', [
                    'braintree_token' => 'fake-valid-nonce',
                    'braintree_type' => '',
                ])->assertStatus(422);
    }
}
