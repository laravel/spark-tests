<?php

use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateProfilePhotoTest extends TestCase
{
    use DatabaseMigrations;

    public function test_profile_photo_can_be_updated()
    {
        $file = new UploadedFile(
            public_path('img/color-logo.png'), 'color-logo.png', 'image/png', null, null, true
        );

        Storage::shouldReceive('disk')
                        ->once()
                        ->with('public')
                        ->andReturn($disk = Mockery::mock('StdClass'));

        $disk->shouldReceive('put');
        $disk->shouldReceive('url')->once()->andReturn('/profile/photo');

        $this->actingAs(factory(User::class)->create())
                ->json('POST', '/settings/photo', [
                    'photo' => $file,
                ]);

        $this->seeStatusCode(200);

        $this->seeInDatabase('users', [
            'photo_url' => '/profile/photo',
        ]);
    }
}
