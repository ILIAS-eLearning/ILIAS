<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wrapper for generation of random numbers, strings, bytes
 */
class ilRandom
{
    /**
     * @var \ilLogger | null
     */
    private $logger = null;

    /**
     * constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->rnd();
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     * @throws Throwable
     */
    public function int(int $min = null, int $max = null) :int
    {
        if(is_null($min)) {
            $min = 0;
        }
        if(is_null($max)) {
            $max = mt_getrandmax();
        }

        try {
            return random_int($min, $max);
        } catch (Exception $e) {
            $this->logger->logStack(\ilLogLevel::ERROR);
            $this->logger()->error('No suitable random number generator found.');
            throw $e;
        } catch (Throwable $e) {
            $this->logger->logStack(\ilLogLevel::ERROR);
            $this->logger->error('max should be greater than min.');
            throw $e;
        }
    }

}