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

namespace ILIAS\HTTP\Duration;

use Closure;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class CallbackDuration extends Duration
{
    /**
     * @return mixed|null
     */
    public function stretch(callable $callback)
    {
        $start_time = microtime(true);
        $halted = true;

        register_shutdown_function(static function () use (&$halted) {
            if ($halted) {
                throw new \LogicException("Callback could not be stretched because it halted the programm.");
            }
        });

        $return = $callback();
        $halted = false;

        $elapsed_time_in_us = ((microtime(true) - $start_time) * self::S_TO_US);
        $duration_in_us = ($this->duration_in_ms * self::MS_TO_US);

        if ($elapsed_time_in_us > $duration_in_us) {
            throw new \RuntimeException("Execution of callback exceeded the given duration and could not be stretched.");
        }

        if ($elapsed_time_in_us < $duration_in_us) {
            usleep((int) round($duration_in_us - $elapsed_time_in_us));
        }

        return $return;
    }
}
