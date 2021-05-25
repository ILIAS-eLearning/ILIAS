<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Björn Heyser <bheyser@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 * @package Services/Randomization
 */
class ilArrayElementShuffler extends ilBaseRandomElementProvider implements ilRandomArrayElementProvider
{
    private function isMtRandomizerAvailable() : bool
    {
        return function_exists('mt_srand') && function_exists('mt_rand');
    }

    protected function getInitialSeed() : int
    {
        [$usec, $sec] = explode(' ', microtime());

        return (int) ($sec + ($usec * 100000));
    }

    private function initSeed(int $seed) : void
    {
        if ($this->isMtRandomizerAvailable()) {
            mt_srand($seed);
        } else {
            srand($seed);
        }
    }

    private function shuffleArray(array $array) : array
    {
        if ($this->isMtRandomizerAvailable()) {
            return $this->mtShuffle($array);
        }

        shuffle($array);

        return $array;
    }

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

    public function shuffle(array $array) : array
    {
        $this->initSeed($this->getSeed());
        $array = $this->shuffleArray($array);
        $this->initSeed($this->getInitialSeed());

        return $array;
    }
}
