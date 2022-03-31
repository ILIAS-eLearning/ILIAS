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

    // int and "null"?
    // why not(you can delete isnull($min)also):
//  public function int(int $min = 0, int $max = 0) : int

// since this also throws exceptions, i added phpdoc    
    /**
     * Generate a random INT-Number.
     * 
     * @param int $min
     * @param int $max
     * @return int
     * @throws Throwable
     */
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
