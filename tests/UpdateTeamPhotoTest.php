<?php

use App\User;
use App\Team;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateTeamPhotoTest extends TestCase
{
    use DatabaseMigrations;

    public function test_team_photo_can_be_updated()
    {
        $user = factory(User::class)->create();

        $team = (new Team)->forceFill([
            'name' => 'New Team',
            'owner_id' => $user->id,
        ]);

        $user->teams()->save($team, ['role' => 'owner']);

        $file = new UploadedFile(
            public_path('img/color-logo.png'), 'color-logo.png', 'image/png', null, null, true
        );

        Storage::shouldReceive('disk')
                        ->once()
                        ->with('public')
                        ->andReturn($disk = Mockery::mock('StdClass'));

        $disk->shouldReceive('put');
        $disk->shouldReceive('url')->once()->andReturn('/team/photo');

        $this->actingAs($user)
                ->json('POST', '/settings/teams/'.$team->id.'/photo', [
                    'photo' => $file,
                ]);

        $this->seeStatusCode(200);

        $this->seeInDatabase('teams', [
            'photo_url' => '/team/photo',
        ]);
    }
}
