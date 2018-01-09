<?php

use App\User;
use Ramsey\Uuid\Uuid;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateApiTokenTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @group shit
     */
    public function test_name_can_be_updated()
    {
        $user = factory(User::class)->create();

        $token = $user->tokens()->create([
            'id' => Uuid::uuid4(),
            'name' => 'New Token',
            'token' => str_random(40),
            'metadata' => [],
        ]);

        $this->actingAs($user)
                ->json('PUT', '/settings/api/token/'.$token->id, [
                    'name' => 'New Token (Updated)',
                    'abilities' => [],
                ])->assertSuccessful();

        $this->seeInDatabase('api_tokens', [
            'name' => 'New Token (Updated)',
        ]);
    }


    public function test_name_is_required()
    {
        $user = factory(User::class)->create();

        $token = $user->tokens()->create([
            'id' => Uuid::uuid4(),
            'name' => 'New Token',
            'token' => str_random(40),
            'metadata' => [],
        ]);

        $this->actingAs($user)
                ->json('PUT', '/settings/api/token/'.$token->id, [
                    'name' => '',
                    'abilities' => [],
                ])->assertStatus(422);
    }


    public function test_abilities_can_be_updated()
    {
        Spark::tokensCan([
            'create-servers' => 'Create Servers',
            'delete-servers' => 'Delete Servers'
        ]);

        $user = factory(User::class)->create();

        $token = $user->tokens()->create([
            'id' => Uuid::uuid4(),
            'name' => 'New Token',
            'token' => str_random(40),
            'metadata' => [],
        ]);

        $this->actingAs($user)
                ->json('PUT', '/settings/api/token/'.$token->id, [
                    'name' => 'New Token (Updated)',
                    'abilities' => ['create-servers'],
                ])->assertSuccessful();

        $token = $user->fresh()->tokens->first();

        $this->assertTrue($token->can('create-servers'));
        $this->assertFalse($token->can('delete-servers'));
    }


    public function test_abilities_must_be_valid_abilities()
    {
        Spark::tokensCan(['create-servers' => 'Create Servers']);

        $user = factory(User::class)->create();

        $token = $user->tokens()->create([
            'id' => Uuid::uuid4(),
            'name' => 'New Token',
            'token' => str_random(40),
            'metadata' => [],
        ]);

        $this->actingAs($user)
                ->json('PUT', '/settings/api/token/'.$token->id, [
                    'name' => 'New Token (Updated)',
                    'abilities' => ['delete-servers'],
                ])->assertStatus(422);
    }
}
