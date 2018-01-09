<?php

use App\User;
use Ramsey\Uuid\Uuid;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PendingInvitationTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations;

    public function test_invitations_can_be_accepted()
    {
        $team = $this->createTeam();

        $user = factory(User::class)->create();

        $invitation = $team->invitations()->create([
            'id' => Uuid::uuid4(),
            'user_id' => $user->id,
            'email' => 'test@spark.laravel.com',
            'token' => str_random(40),
        ]);

        $this->actingAs($user)
                ->json('POST', '/settings/invitations/'.$invitation->id.'/accept')
                ->assertSuccessful();

        $this->assertEquals(1, $user->teams()->count());
    }


    public function test_invitatation_cannot_be_accepted_by_anyone_other_than_owner()
    {
        $team = $this->createTeam();

        $user = factory(User::class)->create();

        $invitation = $team->invitations()->create([
            'id' => Uuid::uuid4(),
            'user_id' => $user->id,
            'email' => 'test@spark.laravel.com',
            'token' => str_random(40),
        ]);

        $this->actingAs($team->owner)
                ->json('POST', '/settings/invitations/'.$invitation->id.'/accept')
                ->assertStatus(404);
    }


    public function test_invitations_can_be_rejected()
    {
        $team = $this->createTeam();

        $user = factory(User::class)->create();

        $invitation = $team->invitations()->create([
            'id' => Uuid::uuid4(),
            'user_id' => $user->id,
            'email' => 'test@spark.laravel.com',
            'token' => str_random(40),
        ]);

        $this->actingAs($user)
                ->json('POST', '/settings/invitations/'.$invitation->id.'/reject')
                ->assertSuccessful();

        $this->assertEquals(0, $user->teams()->count());
    }


    public function test_invitatation_cannot_be_rejected_by_anyone_other_than_owner()
    {
        $team = $this->createTeam();

        $user = factory(User::class)->create();

        $invitation = $team->invitations()->create([
            'id' => Uuid::uuid4(),
            'user_id' => $user->id,
            'email' => 'test@spark.laravel.com',
            'token' => str_random(40),
        ]);

        $this->actingAs($team->owner)
                ->json('POST', '/settings/invitations/'.$invitation->id.'/reject')
                ->assertStatus(404);
    }
}
