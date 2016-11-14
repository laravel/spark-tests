<?php

use App\User;
use App\Team;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CanJoinTeamsTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_has_teams_indicates_if_user_belongs_to_any_teams()
    {
        $user = factory(User::class)->create();

        $this->assertFalse($user->hasTeams());

        $team = $this->createTeam($user, 'member');

        $user = $user->fresh();

        $this->assertTrue($user->hasTeams());
    }


    public function test_owns_team_returns_true_if_user_owns_the_given_team()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user, 'member');

        $this->assertTrue($user->ownsTeam($team));
        $this->assertEquals(1, $user->ownedTeams->count());
    }


    public function test_owns_team_returns_false_if_user_doesnt_own_the_given_team()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user, 'member');
        $team->owner_id = $user->id + 1;
        $team->save();

        $this->assertFalse($user->ownsTeam($team));
        $this->assertEquals(0, $user->ownedTeams->count());
    }


    public function test_current_role_can_be_retrieved_for_team()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user, 'member');

        $this->assertEquals('member', $user->roleOn($team));
    }


    public function test_current_team_returns_the_active_team_for_the_user()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user, 'member');

        $this->assertEquals($team->id, $user->current_team->id);
    }


    /**
     * @group stripe
     */
    public function test_current_team_on_trial_determines_if_current_team_is_on_trial()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user, 'member');
        $this->assertFalse($user->currentTeamOnTrial());

        $team->newSubscription('default', 'spark-test-1')->trialDays(10)->create($this->getStripeToken());

        $this->assertFalse($user->currentTeamOnTrial());
    }


    /**
     * @group braintree
     */
    public function test_current_team_on_trial_determines_if_current_team_is_on_trial_using_braintree()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user, 'member');
        $this->assertFalse($user->currentTeamOnTrial());

        $team->newSubscription('default', 'spark-test-1')->trialDays(10)->create('fake-valid-nonce');

        $this->assertFalse($user->currentTeamOnTrial());
    }


    public function test_current_team_will_gracefully_reset_when_current_team_id_is_null()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user, 'member');
        $user->forceFill(['current_team_id' => null])->save();

        $this->assertEquals($team->id, $user->current_team->id);
    }


    public function test_users_can_switch_to_another_active_team()
    {
        $user = factory(User::class)->create();
        $team1 = $this->createTeam($user, 'member');
        $team2 = $this->createTeam($user, 'member');

        $user->forceFill(['current_team_id' => $team1->id])->save();
        $this->assertEquals($team1->id, $user->current_team->id);

        $user->switchToTeam($team2);
        $this->assertEquals($team2->id, $user->current_team->id);
    }
}
