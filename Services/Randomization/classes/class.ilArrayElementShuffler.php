<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Björn Heyser <bheyser@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 * @package Services/Randomization
 */
class ilArrayElementShuffler extends ilBaseRandomElementProvider implements ilRandomArrayElementProvider
{
    /**
     * @return bool
     */
    private function isMtRandomizerAvailable() : bool
    {
        return function_exists('mt_srand') && function_exists('mt_rand');
    }

    /**
     * @return int
     */
    protected function getInitialSeed() : int
    {
        list($usec, $sec) = explode(' ', microtime());
        return (int) ($sec + ($usec * 100000));
    }

    /**
     * @param int $seed
     */
    private function initSeed(int $seed) : void
    {
        $seed = (int) $seed; // (mt_)srand seems to not cast to integer itself (string seeds avoid randomizing) !!

        if ($this->isMtRandomizerAvailable()) {
            mt_srand($seed);
        } else {
            srand($seed);
        }
    }

    /**
     * @param array $array
     * @return array
     */
    private function shuffleArray(array $array) : array
    {
        if ($this->isMtRandomizerAvailable()) {
            return $this->mtShuffle($array);
        }

        shuffle($array);
        return $array;
    }

    /**
     * @param array $orderedArray
     * @return array
     */
    private function mtShuffle(array $orderedArray) : array
    {
        $shuffledArray = [];

        while (count($orderedArray) > 0) {
            $key = mt_rand(0, (count($orderedArray) - 1));
            $splice = array_splice($orderedArray, $key, 1);
            $shuffledArray[] = current($splice);
        }

        return $shuffledArray;
    }

    /**
     * @inheritDoc
     */
    public function shuffle(array $array) : array
    {
        $this->initSeed($this->getSeed());
        $array = $this->shuffleArray($array);
        $this->initSeed($this->getInitialSeed());

        return $array;
    }
}
