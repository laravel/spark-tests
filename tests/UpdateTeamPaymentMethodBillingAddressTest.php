<?php

use App\User;
use App\Team;
use Laravel\Spark\Spark;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group stripe
 */
class UpdateTeamPaymentMethodBillingAddressTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_payment_method_for_stripe_can_be_updated()
    {
        Spark::collectBillingAddress();

        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $team->newSubscription('default', 'spark-test-1')->create($this->getStripeToken());

        $this->actingAs($user)
                ->json('PUT', '/settings/teams/'.$team->id.'/payment-method', [
                    'stripe_token' => $this->getStripeToken(),
                    'address' => 'Test',
                    'city' => 'Test',
                    'state' => 'AR',
                    'zip' => '71901',
                    'country' => 'US',
                ]);

        $this->seeStatusCode(200);

        Spark::collectBillingAddress(false);
    }


    public function test_payment_method_country_must_match_stripe_country()
    {
        Spark::collectBillingAddress();

        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $team->newSubscription('default', 'spark-test-1')->create($this->getStripeToken());

        $this->actingAs($user)
                ->json('PUT', '/settings/teams/'.$team->id.'/payment-method', [
                    'stripe_token' => $this->getStripeToken(),
                    'address' => 'Test',
                    'city' => 'Test',
                    'state' => 'AR',
                    'zip' => '71901',
                    'country' => 'TV',
                ]);

        $this->seeStatusCode(422);

        Spark::collectBillingAddress(false);
    }

    public function test_payment_method_state_must_be_valid_for_country()
    {
        Spark::collectBillingAddress();

        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $team->newSubscription('default', 'spark-test-1')->create($this->getStripeToken());

        $this->actingAs($user)
                ->json('PUT', '/settings/teams/'.$team->id.'/payment-method', [
                    'stripe_token' => $this->getStripeToken(),
                    'address' => 'Test',
                    'city' => 'Test',
                    'state' => 'TEST',
                    'zip' => '71901',
                    'country' => 'US',
                ]);

        $this->seeStatusCode(422);

        Spark::collectBillingAddress(false);
    }
}
