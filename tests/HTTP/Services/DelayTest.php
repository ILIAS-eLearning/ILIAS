<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

namespace ILIAS\HTTP;

/** @noRector */
require_once "AbstractBaseTest.php";

use ILIAS\HTTP\Throttling\Delay;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class DelayTest extends AbstractBaseTest
{
    public function testDelayWithNegativeSeconds() : void
    {
        $delay = $this->getTestableDelay(-10);

        $this->assertEquals(0, $delay->s());
        $this->assertEquals(0, $delay->us());
    }

    public function testDelayWithPositiveSeconds() : void
    {
        $delay = $this->getTestableDelay(10);

        $this->assertEquals(10, $delay->s());
        $this->assertEquals(0, $delay->us());
    }

    public function testDelayWithFloatingPoint() : void
    {
        $delay = $this->getTestableDelay(1.5);

        $this->assertEquals(1, $delay->s());
        $this->assertEquals(500_000, $delay->us());
    }

    public function testDelayWithWeirdFloatingPoint() : void
    {
        $delay = $this->getTestableDelay(0.987654321);

        $this->assertEquals(0, $delay->s());
        // float output would be 987654.321, therefore a round-down is expected.
        $this->assertEquals(987_654, $delay->us());
    }

    protected function getTestableDelay(float $delay) : Delay
    {
        return new class($delay) extends Delay {
            public function s() : int
            {
                return $this->getFullSeconds();
            }

            public function us() : int
            {
                return $this->getRemainingMicroSeconds();
            }
        };
    }
}
