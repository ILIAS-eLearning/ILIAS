<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ChartDataLinesTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    public function testLineWidth(): void
    {
        $cd = new ilChartDataLines();
        $cd->setLineWidth(15);
        $this->assertEquals(
            15,
            $cd->getLineWidth()
        );
    }

    public function testLineSteps(): void
    {
        $cd = new ilChartDataLines();
        $cd->setLineSteps(true);
        $this->assertEquals(
            true,
            $cd->getLineSteps()
        );
        $cd->setLineSteps(false);
        $this->assertEquals(
            false,
            $cd->getLineSteps()
        );
    }
}
