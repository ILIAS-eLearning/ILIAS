<?php

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class LocatorGUITest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
    }

    public function testValue(): void
    {
        $loc = new ilLocatorGUI();
        $loc->setOffline(true);
        $this->assertEquals(
            true,
            $loc->getOffline()
        );
    }
}
