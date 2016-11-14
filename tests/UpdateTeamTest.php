<?php

use App\User;
use App\Team;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateTeamTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations;

    public function test_name_can_be_updated()
    {
        $team = $this->createTeam();

        $this->actingAs($team->owner)
                ->json('PUT', '/settings/teams/'.$team->id.'/name', [
                    'name' => 'Name (Updated)',
                ]);

        $this->seeStatusCode(200);

        $this->seeInDatabase('teams', [
            'name' => 'Name (Updated)',
        ]);
    }


    public function test_name_is_required()
    {
        $team = $this->createTeam();

        $this->actingAs($team->owner)
                ->json('PUT', '/settings/teams/'.$team->id.'/name', [
                    'name' => '',
                ])->seeStatusCode(422);
    }
}
