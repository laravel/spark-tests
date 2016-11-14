<?php

use App\User;
use App\Team;
use Illuminate\Http\Request;
use Braintree\TransactionSearch;
use Braintree\Transaction as BraintreeTransaction;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Spark\Events\Subscription\SubscriptionCancelled;
use Laravel\Spark\Events\Teams\Subscription\SubscriptionCancelled as TeamSubscriptionCancelled;

/**
 * @group braintree
 */
class BraintreeWebhookControllerTest extends TestCase
{
    use CreatesTeams, DatabaseMigrations, InteractsWithPaymentProviders;

    public function test_local_invoices_are_stored()
    {
        $user = $this->createBraintreeSubscribedUser('spark-test-1');
        $invoice = $user->invoicesIncludingPending()->first();

        $parameters = [
            TransactionSearch::customerId()->is($user->braintree_id),
        ];

        $transactions = BraintreeTransaction::search($parameters);

        $request = Request::create('/', 'POST', [], [], [], [], json_encode([
            'kind' => 'subscription_charged_successfully', 'id' => 'event-id',
            'subscription' => [
                'id' => $user->subscriptions->first()->braintree_id,
                'transactions' => [
                    ['id' => $transactions->firstItem()->id],
                ]
            ],
        ]));

        $controller = new SparkBraintreeWebhookControllerTestStub;
        $response = $controller->handleWebhook($request);

        $localInvoice = DB::table('invoices')->first();

        $this->assertEquals($invoice->id, $localInvoice->provider_id);
        $this->assertEquals($user->id, $localInvoice->user_id);
        $this->assertEquals(10, $localInvoice->total);
        $this->assertEquals(0, $localInvoice->tax);
    }

    public function test_local_team_invoices_are_stored()
    {
        $user = factory(User::class)->create();
        $team = $this->createTeam($user);
        $team->newSubscription('default', 'spark-test-1')->create('fake-valid-nonce');
        $invoice = $team->invoicesIncludingPending()->first();

        $parameters = [
            TransactionSearch::customerId()->is($team->braintree_id),
        ];

        $transactions = BraintreeTransaction::search($parameters);

        $request = Request::create('/', 'POST', [], [], [], [], json_encode([
            'kind' => 'subscription_charged_successfully', 'id' => 'event-id',
            'subscription' => [
                'id' => $team->subscriptions->first()->braintree_id,
                'transactions' => [
                    ['id' => $transactions->firstItem()->id],
                ]
            ],
        ]));

        $controller = new SparkBraintreeWebhookControllerTestStub;
        $response = $controller->handleWebhook($request);

        $localInvoice = DB::table('invoices')->first();

        $this->assertEquals($invoice->id, $localInvoice->provider_id);
        $this->assertEquals($team->id, $localInvoice->team_id);
        $this->assertEquals(10, $localInvoice->total);
        $this->assertEquals(0, $localInvoice->tax);
    }

    public function test_events_are_fired_when_subscriptions_are_deleted()
    {
        $this->expectsEvents(SubscriptionCancelled::class);
        $user = $this->createBraintreeSubscribedUser('spark-test-1');

        $request = Request::create('/', 'POST', [], [], [], [], json_encode([
            'kind' => 'subscription_canceled', 'id' => 'event-id',
            'subscription' => [
                'id' => $user->subscriptions->first()->braintree_id,
            ],
        ]));

        $controller = new SparkBraintreeWebhookControllerTestStub;
        $response = $controller->handleWebhook($request);
    }

    public function test_team_events_are_fired_when_subscriptions_are_deleted()
    {
        $this->expectsEvents(TeamSubscriptionCancelled::class);

        $user = factory(User::class)->create();
        $team = $this->createTeam($user);
        $team->newSubscription('default', 'spark-test-1')->create('fake-valid-nonce');

        $request = Request::create('/', 'POST', [], [], [], [], json_encode([
            'kind' => 'subscription_canceled', 'id' => 'event-id',
            'subscription' => [
                'id' => $team->subscriptions->first()->braintree_id,
            ],
        ]));

        $controller = new SparkBraintreeWebhookControllerTestStub;
        $response = $controller->handleWebhook($request);
    }
}


class SparkBraintreeWebhookControllerTestStub extends Laravel\Spark\Http\Controllers\Settings\Billing\BraintreeWebhookController
{
    /**
     * Parse the given Braintree webhook notification request.
     *
     * @param  Request  $request
     * @return WebhookNotification
     */
    protected function parseBraintreeNotification($request)
    {
        return json_decode($request->getContent());
    }
}
