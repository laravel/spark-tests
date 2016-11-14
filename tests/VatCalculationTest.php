<?php

use App\User;
use App\Team;

class VatCalculationTest extends TestCase
{
    public function test_vat_can_be_calculated()
    {
        Spark::collectEuropeanVat();

        $user = new User;

        // Test calculation...
        $user->forceFill([
            'card_country' => 'GB',
            'vat_id' => '',
        ]);

        $this->assertEquals(20, $user->taxPercentage());

        // Test VAT ID...
        $user->forceFill([
            'vat_id' => 'SOMETHING',
        ]);

        $this->assertEquals(0, $user->taxPercentage());

        $array = $user->toArray();
        $this->assertEquals(0, $array['tax_rate']);

        // Test with team...
        $team = new Team;

        $team->forceFill([
            'card_country' => 'GB',
            'vat_id' => '',
        ]);

        $array = $team->toArray();

        $this->assertEquals(20, $array['tax_rate']);

        Spark::collectEuropeanVat('GB', false);
    }

    public function test_vat_id_can_be_validated()
    {
        Spark::collectEuropeanVat();

        $validator = new Laravel\Spark\Validation\VatIdValidator;

        $this->assertTrue($validator->validate('vat_id', 'SE870822461601', []));
        $this->assertFalse($validator->validate('vat_id', 'SOMETHING', []));

        Spark::collectEuropeanVat('GB', false);
    }
}
