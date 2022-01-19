<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class CertificateVerificationClassMapTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    public function testClassMap()
    {
        $map = new ilCertificateVerificationClassMap();
        $this->assertEquals(
            "crsv",
            $map->getVerificationTypeByType("crs")
        );
    }
}
