<?php

use App\User;
use Ramsey\Uuid\Uuid;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class MailedInvitationTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_invitations_can_be_created()
    {
        $team = $this->createTeam();

        $this->actingAs($team->owner)
                ->json('POST', '/settings/teams/'.$team->id.'/invitations', [
                    'email' => 'test@spark.laravel.com',
                ])->assertSuccessful();

        $this->assertEquals(1, count($team->invitations()->count()));
    }


    public function test_email_addess_is_required()
    {
        $team = $this->createTeam();

        $this->actingAs($team->owner)
                ->json('POST', '/settings/teams/'.$team->id.'/invitations', [
                    'email' => '',
                ])->assertStatus(422);
    }


    public function test_email_must_not_already_be_on_team()
    {
        $team = $this->createTeam();

        $this->actingAs($team->owner)
                ->json('POST', '/settings/teams/'.$team->id.'/invitations', [
                    'email' => $team->owner->email,
                ])->assertStatus(422);
    }


    public function test_email_must_not_already_be_invited()
    {
        $team = $this->createTeam();

        $team->invitations()->create([
            'id' => Uuid::uuid4(),
            'email' => 'test@spark.laravel.com',
            'token' => str_random(40),
        ]);

        $this->actingAs($team->owner)
                ->json('POST', '/settings/teams/'.$team->id.'/invitations', [
                    'email' => 'test@spark.laravel.com',
                ])->assertStatus(422);
    }


    public function test_invitation_cannot_be_created_if_would_exceed_max_team_members()
    {
        Spark::plan('Test', 'test-plan-with-max-team-members')
                    ->price(10)->maxTeamMembers(1);

        $team = $this->createTeam($this->createSubscribedUser('spark-test-1'));

        $team->owner->subscription()->forceFill([
            'stripe_plan' => 'test-plan-with-max-team-members',
        ]);

        $this->actingAs($team->owner)
                ->json('POST', '/settings/teams/'.$team->id.'/invitations', [
                    'email' => 'test@spark.laravel.com',
                ])->assertStatus(422);
    }


    public function test_invitation_cannot_be_created_if_would_exceed_max_team_collaborators()
    {
        Spark::plan('Test', 'test-plan-with-max-team-members')
                    ->price(10)->maxCollaborators(1);

        $team = $this->createTeam(
            $this->createSubscribedUser('spark-test-1')
        );

        $team->owner->subscription()->forceFill([
            'stripe_plan' => 'test-plan-with-max-team-members',
        ]);

        $this->actingAs($team->owner)
                ->json('POST', '/settings/teams/'.$team->id.'/invitations', [
                    'email' => 'test@spark.laravel.com',
                ])->assertStatus(422);
    }


    public function test_invitations_can_be_deleted()
    {
        $team = $this->createTeam();

        $invitation = $team->invitations()->create([
            'id' => Uuid::uuid4(),
            'email' => 'test@spark.laravel.com',
            'token' => str_random(40),
        ]);

        $this->actingAs($team->owner)
                ->json('DELETE', '/settings/invitations/'.$invitation->id)
                ->assertSuccessful();
    }


    public function test_owner_is_only_one_who_can_delete_invitations()
    {
        $team = $this->createTeam();

        $invitation = $team->invitations()->create([
            'id' => Uuid::uuid4(),
            'email' => 'test@spark.laravel.com',
            'token' => str_random(40),
        ]);

        $this->actingAs(factory(User::class)->create())
                ->json('DELETE', '/settings/invitations/'.$invitation->id)
                ->assertStatus(404);
    }
}
