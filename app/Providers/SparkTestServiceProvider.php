<?php

namespace App\Providers;

use Laravel\Spark\Spark;
use Laravel\Spark\Providers\AppServiceProvider as ServiceProvider;

class SparkServiceProvider extends ServiceProvider
{
    /**
     * Your application and company details.
     *
     * @var array
     */
    protected $details = [
        'vendor' => 'Your Company',
        'product' => 'Your Product',
        'street' => 'PO Box 111',
        'location' => 'Your Town, NY 12345',
        'phone' => '555-555-5555',
    ];

    /**
     * The address where customer support e-mails should be sent.
     *
     * @var string
     */
    protected $sendSupportEmailsTo = 'taylor@laravel.com';

    /**
     * All of the application developer e-mail addresses.
     *
     * @var array
     */
    protected $developers = [
        'taylor@laravel.com'
    ];

    /**
     * Indicates if the application will expose an API.
     *
     * @var bool
     */
    protected $usesApi = true;

    /**
     * Finish configuring Spark.
     *
     * Define the subscription plans for your application.
     *
     * @return void
     */
    public function booted()
    {
        Spark::useTwoFactorAuth();

        Spark::useStripe()->noCardUpFront()->trialDays(10);

        Spark::teamTrialDays(10);

        Spark::freePlan()
            ->features([
                'First', 'Second', 'Third'
            ]);

        Spark::plan('Basic', 'spark-test-1')
            ->price(10)
            ->features([
                'First', 'Second', 'Third'
            ]);

        Spark::plan('Basic', 'spark-test-2')
            ->price(20)
            ->features([
                'First', 'Second', 'Third'
            ]);

        Spark::plan('Basic', 'spark-test-3')
            ->price(100)
            ->yearly()
            ->features([
                'First', 'Second', 'Third'
            ]);

        Spark::freeTeamPlan()
            ->features([
                'First', 'Second', 'Third'
            ]);

        Spark::teamPlan('Basic', 'spark-test-1')
            ->price(10)
            ->features([
                'First', 'Second', 'Third'
            ]);

        Spark::teamPlan('Basic', 'spark-test-2')
            ->price(20)
            ->features([
                'First', 'Second', 'Third'
            ]);

        Spark::teamPlan('Basic', 'spark-test-3')
            ->price(100)
            ->yearly()
            ->features([
                'First', 'Second', 'Third'
            ]);
    }
}
