<?php

use App\User;

trait InteractsWithPaymentProviders
{
    /**
     * Create a new subscribed user.
     *
     * @return mixed
     */
    public function createSubscribedUser($plan)
    {
        $user = factory(User::class)->create();

        $user->newSubscription('default', $plan)->create($this->getStripeToken());

        return $user;
    }

    /**
     * Create a new subscribed user.
     *
     * @return mixed
     */
    public function createBraintreeSubscribedUser($plan)
    {
        $user = factory(User::class)->create();

        $user->newSubscription('default', $plan)->create('fake-valid-nonce');

        return $user;
    }

    /**
     * Get a test Stripe token.
     *
     * @return string
     */
    protected function getStripeToken()
    {
        return Stripe\Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => 2020,
                'cvc' => "123",
            ],
        ], config('services.stripe.secret'))->id;
    }
}
