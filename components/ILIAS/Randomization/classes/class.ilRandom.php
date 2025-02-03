<?php

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
 *********************************************************************/

/**
 * Wrapper for generation of random numbers, strings, bytes
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilRandom
{
    private function logIfPossible(callable $c): void
    {
        global $DIC;

        if (isset($DIC['ilLoggerFactory'])) {
            $c($DIC->logger()->rnd());
        }
    }

    public function int(int $min = 0, int $max = PHP_INT_MAX): int
    {
        try {
            return random_int($min, $max);
        } catch (Throwable $e) {
            $this->logIfPossible(static function (ilLogger $logger): void {
                $logger->logStack(ilLogLevel::ERROR);
                $logger->error('No suitable random number generator found.');
            });
            throw $e;
        }
    }
}
