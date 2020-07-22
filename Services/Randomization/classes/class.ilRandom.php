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
    public function int($min = null, $max = null)
    {
        if (is_null($min)) {
            $min = 0;
        }
        if (is_null($max)) {
            $max = mt_getrandmax();
        }

        if ($this->supportsRandomInt()) {
            try {
                return random_int($min, $max);
            } catch (Exception $e) {
                $this->logger->logStack(\ilLogLevel::ERROR);
                $this->logger->error('No suitable random number generator found.');
                throw $e;
            } catch (Throwable $e) {
                $this->logger->logStack(\ilLogLevel::ERROR);
                $this->logger->error('max should be greater than min.');
                throw $e;
            }
        }
        // version 5.6 => use mt_rand
        return mt_rand($min, $max);
    }

    /**
     * @return bool
     */
    private function supportsRandomInt()
    {
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            return true;
        }
        return false;
    }
}
