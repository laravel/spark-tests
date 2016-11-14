<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CreateTeamTest extends TestCase
{
    use DatabaseMigrations;

    public function test_teams_can_be_created()
    {
        $this->actingAs(factory(User::class)->create())
                ->json('POST', '/settings/teams', [
                    'name' => 'New Team',
                ]);

        $this->seeInDatabase('teams', [
            'name' => 'New Team',
        ]);
    }


    public function test_name_is_required()
    {
        $this->actingAs(factory(User::class)->create())
                ->json('POST', '/settings/teams', [
                    'name' => '',
                ]);

        $this->seeStatusCode(422);
    }
}
