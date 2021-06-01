<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wrapper for generation of random numbers, strings, bytes
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilRandom
{

    /**
     * ilRandom constructor.
     */
    public function __construct()
    {
    }

    private function logIfPossible(callable $c) : void
    {
        global $DIC;

        if (isset($DIC['ilLoggerFactory'])) {
            $c($DIC->logger()->rnd());
        }
    }

    public function int(int $min = null, int $max = null) : int
    {
        if (is_null($min)) {
            $min = 0;
        }

        if (is_null($max)) {
            $max = mt_getrandmax();
        }

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
