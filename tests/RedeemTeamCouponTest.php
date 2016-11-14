<?php

use App\User;
use App\Team;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Spark\Contracts\Repositories\CouponRepository;
use Laravel\Spark\Contracts\Interactions\Settings\PaymentMethod\RedeemCoupon;

class RedeemTeamCouponTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations;

    public function test_valid_coupon_can_be_redeemed()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $repository = Mockery::mock(CouponRepository::class);
        $repository->shouldReceive('canBeRedeemed')->with('coupon-code')->andReturn(true);
        $this->app->instance(CouponRepository::class, $repository);

        $interaction = Mockery::mock(RedeemCoupon::class);
        $interaction->shouldReceive('handle')->with(Mockery::type(Team::class), 'coupon-code');
        $this->app->instance(RedeemCoupon::class, $interaction);

        $this->actingAs($user)
                ->json('POST', '/settings/teams/'.$team->id.'/payment-method/coupon', [
                    'coupon' => 'coupon-code',
                ]);

        $this->seeStatusCode(200);
    }


    public function test_coupon_code_must_be_valid()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $repository = Mockery::mock(CouponRepository::class);
        $repository->shouldReceive('canBeRedeemed')->with('coupon-code')->andReturn(false);
        $this->app->instance(CouponRepository::class, $repository);

        $interaction = Mockery::mock(RedeemCoupon::class);
        $interaction->shouldReceive('handle')->never();
        $this->app->instance(RedeemCoupon::class, $interaction);

        $this->actingAs($user)
                ->json('POST', '/settings/teams/'.$team->id.'/payment-method/coupon', [
                    'coupon' => 'coupon-code',
                ]);

        $this->seeStatusCode(422);
    }
}
