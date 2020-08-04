<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wrapper for generation of random numbers, strings, bytes
 */
class ilRandom
{

    /**
     * ilRandom constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param callable $c
     */
    private function logIfPossible(callable $c)
    {
        global $DIC;

        if (isset($DIC['ilLoggerFactory'])) {
            $c($DIC->logger()->rnd());
        }
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     * @throws Throwable
     */
    public function int(int $min = null, int $max = null) :int
    {
        if (is_null($min)) {
            $min = 0;
        }
        if (is_null($max)) {
            $max = mt_getrandmax();
        }

        try {
            return random_int($min, $max);
        } catch (Exception $e) {
                $this->logIfPossible(static function (ilLogger $logger) {
                    $logger->logStack(\ilLogLevel::ERROR);
                    $logger->error('No suitable random number generator found.');
                });
            throw $e;
        } catch (Throwable $e) {
                $this->logIfPossible(static function (ilLogger $logger) {
                    $logger->logStack(\ilLogLevel::ERROR);
                    $logger->error('max should be greater than min.');
                });
            throw $e;
        }
    }
}
