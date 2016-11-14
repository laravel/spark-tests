<?php

use App\User;
use App\Team;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Spark\Events\Subscription\SubscriptionCancelled;
use Laravel\Spark\Events\Teams\Subscription\SubscriptionCancelled as TeamSubscriptionCancelled;

/**
 * @group stripe
 */
class StripeWebhookControllerTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_local_invoices_are_stored()
    {
        $user = $this->createSubscribedUser('spark-test-1');
        $user->forceFill([
            'card_country' => 'US',
        ])->save();
        $invoice = $user->invoices()->first();

        $this->json('POST', '/webhook/stripe', [
            'type' => 'invoice.payment_succeeded',
            'id' => 'event-id',
            'data' => [
                'object' => [
                    'id' => $invoice->id,
                    'customer' => $user->stripe_id,
                ],
            ],
        ]);

        $this->seeStatusCode(200);

        $localInvoice = DB::table('invoices')->first();

        $this->assertEquals($invoice->id, $localInvoice->provider_id);
        $this->assertEquals($user->id, $localInvoice->user_id);
        $this->assertEquals(10, $localInvoice->total);
        $this->assertEquals(0, $localInvoice->tax);
        $this->assertEquals('US', $localInvoice->card_country);
    }

    public function test_local_team_invoices_are_stored()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);
        $team->newSubscription('default', 'spark-test-1')->create($this->getStripeToken());
        $team->forceFill([
            'card_country' => 'US',
        ])->save();
        $invoice = $team->invoices()->first();

        $this->json('POST', '/webhook/stripe', [
            'type' => 'invoice.payment_succeeded',
            'id' => 'event-id',
            'data' => [
                'object' => [
                    'id' => $invoice->id,
                    'customer' => $team->stripe_id,
                ],
            ],
        ]);

        $this->seeStatusCode(200);

        $localInvoice = DB::table('invoices')->first();

        $this->assertEquals($invoice->id, $localInvoice->provider_id);
        $this->assertEquals($team->id, $localInvoice->team_id);
        $this->assertEquals(10, $localInvoice->total);
        $this->assertEquals(0, $localInvoice->tax);
        $this->assertEquals('US', $localInvoice->card_country);
    }

    public function test_events_are_fired_when_subscriptions_are_deleted()
    {
        $this->expectsEvents(SubscriptionCancelled::class);

        $user = $this->createSubscribedUser('spark-test-1');

        $this->json('POST', '/webhook/stripe', [
            'type' => 'customer.subscription_deleted',
            'id' => 'event-id',
            'data' => [
                'object' => [
                    'id' => $user->subscriptions->first()->stripe_id,
                    'customer' => $user->stripe_id,
                ],
            ],
        ]);

        $this->seeStatusCode(200);
    }

    public function test_team_events_are_fired_when_subscriptions_are_deleted()
    {
        $this->expectsEvents(TeamSubscriptionCancelled::class);

        $user = factory(User::class)->create();
        $team = $this->createTeam($user);
        $team->newSubscription('default', 'spark-test-1')->create($this->getStripeToken());

        $this->json('POST', '/webhook/stripe', [
            'type' => 'customer.subscription_deleted',
            'id' => 'event-id',
            'data' => [
                'object' => [
                    'id' => $team->subscriptions->first()->stripe_id,
                    'customer' => $team->stripe_id,
                ],
            ],
        ]);

        $this->seeStatusCode(200);
    }
}
