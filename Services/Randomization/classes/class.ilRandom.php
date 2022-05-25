<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wrapper for generation of random numbers, strings, bytes
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilRandom
{
    private function logIfPossible(callable $c) : void
    {
        global $DIC;

        if (isset($DIC['ilLoggerFactory'])) {
            $c($DIC->logger()->rnd());
        }
    }

    public function int(int $min = 0, int $max = PHP_INT_MAX) : int
    {
        try {
            return random_int($min, $max);
        } catch (Throwable $e) {
            $this->logIfPossible(static function (ilLogger $logger) : void {
                $logger->logStack(ilLogLevel::ERROR);
                $logger->error('No suitable random number generator found.');
            });
            throw $e;
        }
    }
}
