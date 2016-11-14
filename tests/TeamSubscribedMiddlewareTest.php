<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group stripe
 */
class TeamSubscribedMiddlewareTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_middleware_allows_requests_to_pass_for_subscribed_users()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $team->newSubscription('default', 'spark-test-1')->create($this->getStripeToken());

        Route::get('/integration-test/subscribed', ['middleware' => 'teamSubscribed', function () {
            return response('SUBSCRIBED');
        }]);

        $this->actingAs($user)
                ->json('GET', '/integration-test/subscribed');

        $this->seeStatusCode(200);
        $this->assertEquals('SUBSCRIBED', (string) $this->response->getContent());
    }


    public function test_middleware_allows_requests_to_pass_for_trialing_users()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);
        $team->trial_ends_at = Carbon::now()->addDays(10);
        $team->save();

        Route::get('/integration-test/subscribed', ['middleware' => 'teamSubscribed', function () {
            return response('SUBSCRIBED');
        }]);

        $this->actingAs($user)
                ->json('GET', '/integration-test/subscribed');

        $this->seeStatusCode(200);
        $this->assertEquals('SUBSCRIBED', (string) $this->response->getContent());
    }


    public function test_middleware_allows_requests_to_fail_for_unsubscribed_users()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        Route::get('/integration-test/subscribed', ['middleware' => 'teamSubscribed', function () {
            return response('SUBSCRIBED');
        }]);

        $this->actingAs($user)
                ->json('GET', '/integration-test/subscribed');

        $this->seeStatusCode(402);
    }

    public function test_middleware_allows_requests_to_pass_for_subscribed_users_for_a_specific_plan()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);
        $team->newSubscription('default', 'spark-test-1')->create($this->getStripeToken());
        $subscription = $team->subscriptions->first();
        $subscription->stripe_plan = 'something';
        $subscription->save();

        Route::get('/integration-test/subscribed', ['middleware' => 'teamSubscribed:default,something', function () {
            return response('SUBSCRIBED');
        }]);

        $this->actingAs($user->fresh())
                ->json('GET', '/integration-test/subscribed');

        $this->seeStatusCode(200);
        $this->assertEquals('SUBSCRIBED', (string) $this->response->getContent());
    }

    public function test_middleware_allows_requests_to_fail_for_subscribed_users_for_a_missing_plan()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);

        $team->newSubscription('default', 'spark-test-1')->create($this->getStripeToken());

        Route::get('/integration-test/subscribed', ['middleware' => 'subscribed:something', function () {
            return response('SUBSCRIBED');
        }]);

        $this->actingAs($user)
                ->json('GET', '/integration-test/subscribed');

        $this->seeStatusCode(402);
    }
}
