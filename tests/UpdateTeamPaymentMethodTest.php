<?php

use App\User;
use App\Team;
use Laravel\Spark\Spark;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group stripe
 */
class UpdateTeamPaymentMethodTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_payment_method_for_stripe_can_be_updated()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $team->newSubscription('default', 'spark-test-1')->create($this->getStripeToken());

        $this->actingAs($user)
                ->json('PUT', '/settings/teams/'.$team->id.'/payment-method', [
                    'stripe_token' => $this->getStripeToken(),
                ])->assertSuccessful();
    }


    public function test_stripe_token_is_required_to_update_payment_method()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $this->actingAs($user)
                ->json('PUT', '/settings/teams/'.$team->id.'/payment-method', [
                    'stripe_token' => '',
                ])->assertStatus(422);
    }
}
