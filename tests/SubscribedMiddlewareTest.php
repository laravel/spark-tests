<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @group stripe
 */
class SubscribedMiddlewareTest extends TestCase
{
    use DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_middleware_allows_requests_to_pass_for_subscribed_users()
    {
        $user = $this->createSubscribedUser('spark-test-1');

        Route::get('/integration-test/subscribed', ['middleware' => 'subscribed', function () {
            return response('SUBSCRIBED');
        }]);

        $response = $this->actingAs($user)
                ->json('GET', '/integration-test/subscribed')
                ->assertSuccessful();

        $this->assertEquals('SUBSCRIBED', $response->getContent());
    }


    public function test_middleware_allows_requests_to_pass_for_trialing_users()
    {
        $user = factory(User::class)->create();
        $user->trial_ends_at = Carbon::now()->addDays(10);
        $user->save();

        Route::get('/integration-test/subscribed', ['middleware' => 'subscribed', function () {
            return response('SUBSCRIBED');
        }]);

        $response = $this->actingAs($user)
                ->json('GET', '/integration-test/subscribed')
                ->assertSuccessful();

        $this->assertEquals('SUBSCRIBED', $response->getContent());
    }


    public function test_middleware_allows_requests_to_fail_for_unsubscribed_users()
    {
        $user = factory(User::class)->create();

        Route::get('/integration-test/subscribed', ['middleware' => 'subscribed', function () {
            return response('SUBSCRIBED');
        }]);

        $this->actingAs($user)
                ->json('GET', '/integration-test/subscribed')
                ->assertStatus(402);
    }

    public function test_middleware_allows_requests_to_pass_for_subscribed_users_for_a_specific_plan()
    {
        $user = $this->createSubscribedUser('spark-test-1');
        $subscription = $user->subscriptions->first();
        $subscription->stripe_plan = 'something';
        $subscription->save();

        Route::get('/integration-test/subscribed', ['middleware' => 'subscribed:default,something', function () {
            return response('SUBSCRIBED');
        }]);

        $response = $this->actingAs($user)
                ->json('GET', '/integration-test/subscribed')
                ->assertSuccessful();

        $this->assertEquals('SUBSCRIBED', $response->getContent());
    }

    public function test_middleware_allows_requests_to_fail_for_subscribed_users_for_a_missing_plan()
    {
        $user = $this->createSubscribedUser('spark-test-1');

        Route::get('/integration-test/subscribed', ['middleware' => 'subscribed:something', function () {
            return response('SUBSCRIBED');
        }]);

        $this->actingAs($user)
                ->json('GET', '/integration-test/subscribed')
                ->assertStatus(402);
    }
}
