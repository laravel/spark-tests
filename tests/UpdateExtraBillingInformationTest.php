<?php

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateExtraBillingInformationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_billing_information_can_be_updated()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)
                ->json('PUT', '/settings/extra-billing-information', [
                    'information' => 'Updated Information',
                ])->assertSuccessful();

        $user = $user->fresh();

        $this->assertEquals('Updated Information', $user->extra_billing_information);
    }
}
