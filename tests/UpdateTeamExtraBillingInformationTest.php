<?php

use App\User;
use App\Team;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateTeamExtraBillingInformationTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations;

    public function test_billing_information_can_be_updated()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $this->actingAs($user)
                ->json('PUT', '/settings/teams/'.$team->id.'/extra-billing-information', [
                    'information' => 'Updated Information',
                ]);

        $this->seeStatusCode(200);

        $team = $team->fresh();

        $this->assertEquals('Updated Information', $team->extra_billing_information);
    }
}
