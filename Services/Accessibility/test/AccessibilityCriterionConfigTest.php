<?php

use PHPUnit\Framework\TestCase;

/**
 * Test session repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class AccessibilityCriterionConfigTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    public function testToJson()
    {
        $config = new ilAccessibilityCriterionConfig(["foo" => "bar"]);
        $this->assertEquals(
            '{"foo":"bar"}',
            $config->toJson()
        );
    }
}
