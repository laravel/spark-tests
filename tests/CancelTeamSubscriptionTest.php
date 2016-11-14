<?php

use App\User;
use App\Team;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group stripe
 */
class CancelTeamSubscriptionTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_subscription_can_be_cancelled()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $team->newSubscription('default', 'spark-test-1')->create($this->getStripeToken());

        $this->assertTrue($team->subscribed());
        $this->assertFalse($team->subscription()->onGracePeriod());

        $this->actingAs($user)
                ->json('DELETE', '/settings/teams/'.$team->id.'/subscription');

        $team = $team->fresh();

        $this->seeStatusCode(200);
        $this->assertTrue($team->subscription()->onGracePeriod());
    }
}
