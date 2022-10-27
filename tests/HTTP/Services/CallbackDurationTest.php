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
 */

namespace HTTP\Services;

use PHPUnit\Framework\TestCase;
use ILIAS\HTTP\Duration\CallbackDuration;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CallbackDurationTest extends TestCase
{

//    // there isn't a good way to test the shutdown-function with PHPUnit (yet?).
//    public function testCallbackStretchingWithExit() : void
//    {
//        $callback = $this->getTestCallbackWithLength(1, true);
//        $duration = new CallbackDuration(1);
//
//        $this->expectException(\LogicException::class);
//        $this->expectExceptionMessage("Callback could not be stretched because it halted the programm.");
//        $duration->stretch($callback);
//    }

    public function testCallbackStretchingWithTooLongExecutionTime() : void
    {
        $callback = $this->getTestCallbackWithLength(2);
        $duration = new CallbackDuration(1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Execution of callback exceeded the given duration and could not be stretched.");
        $duration->stretch($callback);
    }

    public function testCallbackStretching() : void
    {
        $callback = $this->getTestCallbackWithLength(1);
        $duration = new CallbackDuration(3);

        $start_time = microtime(true);
        $duration->stretch($callback);
        $end_time = microtime(true);

        $elapsed_time = ($end_time - $start_time); // in microseconds (us)
        $expected_duration_in_us = (0.002);

        $this->assertGreaterThanOrEqual($expected_duration_in_us, ($end_time - $start_time));
    }

    protected function getTestCallbackWithLength(int $duration_in_ms, bool $should_halt = false) : callable
    {
        return static function () use ($duration_in_ms, $should_halt) {
            usleep(1_000 * $duration_in_ms);
            if ($should_halt) {
                exit;
            }
        };
    }
}
