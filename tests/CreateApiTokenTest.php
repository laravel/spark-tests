<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CreateApiTokenTest extends TestCase
{
    use DatabaseMigrations;

    public function test_api_token_can_be_created()
    {
        $this->actingAs($user = factory(User::class)->create())
                ->json('POST', '/settings/api/token', [
                    'name' => 'New Token',
                ]);

        $this->assertDatabaseHas('api_tokens', [
            'name' => 'New Token',
        ]);
    }


    public function test_name_is_required()
    {
        $this->actingAs(factory(User::class)->create())
                ->json('POST', '/settings/api/token', [
                    'name' => '',
                ])->assertSuccessful();
    }


    public function test_tokens_can_be_created_with_abilities()
    {
        Spark::tokensCan([
            'create-servers' => 'Create Servers',
            'delete-servers' => 'Delete Servers'
        ]);

        $this->actingAs($user = factory(User::class)->create())
                ->json('POST', '/settings/api/token', [
                    'name' => 'New Token',
                    'abilities' => ['create-servers'],
                ]);

        $this->assertTrue(
            $user->tokens()->where('name', 'New Token')->first()->can('create-servers')
        );

        $this->assertFalse(
            $user->tokens()->where('name', 'New Token')->first()->can('delete-servers')
        );
    }


    public function test_abilities_must_be_valid_abilities()
    {
        Spark::tokensCan(['create-servers' => 'Create Servers']);

        $this->actingAs(factory(User::class)->create())
                ->json('POST', '/settings/api/token', [
                    'name' => 'New Token (Updated)',
                    'abilities' => ['delete-servers'],
                ])->assertStatus(422);
    }
}
