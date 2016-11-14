<?php

use App\User;
use App\Team;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group stripe
 */
class SubscribeTeamTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_teams_can_subscribe()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $this->actingAs($user)
                ->json('POST', '/settings/teams/'.$team->id.'/subscription', [
                    'stripe_token' => $this->getStripeToken(),
                    'plan' => 'spark-test-1',
                ]);

        $team = $team->fresh();

        $this->seeStatusCode(200);
        $this->assertTrue($team->subscribed());
        $this->assertEquals('spark-test-1', $team->subscription()->stripe_plan);
    }


    public function test_stripe_token_is_required()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $this->actingAs($user)
                ->json('POST', '/settings/teams/'.$team->id.'/subscription', [
                    'stripe_token' => '',
                    'plan' => 'spark-test-1',
                ])->seeStatusCode(422);
    }


    public function test_plan_name_must_be_a_valid_team_plan()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $this->actingAs($user)
                ->json('POST', '/settings/teams/'.$team->id.'/subscription', [
                    'stripe_token' => $this->getStripeToken(),
                    'plan' => 'spark-test-10',
                ])->seeStatusCode(422);
    }
}
