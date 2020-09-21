<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class LSGlobalSettingsTest extends TestCase
{
    public function testConstruction() : LSGlobalSettings
    {
        $interval = 12.3;
        $settings = new LSGlobalSettings($interval);
        $this->assertEquals(
            $interval,
            $settings->getPollingIntervalSeconds()
        );

        return $settings;
    }

    /**
     * @depends testConstruction
     */
    public function testIntervalAttribute(LSGlobalSettings $settings)
    {
        $interval = 2.0;
        $settings = $settings->withPollingIntervalSeconds($interval);
        $this->assertEquals(
            $interval,
            $settings->getPollingIntervalSeconds()
        );
        $this->assertEquals(
            $interval * 1000,
            $settings->getPollingIntervalMilliseconds()
        );
    }
}
