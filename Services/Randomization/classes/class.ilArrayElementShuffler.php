<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Services/Randomization
 */
class ilArrayElementShuffler
{
    /**
     * @var integer
     */
    protected $seed;

    /**
     */
    public function __construct()
    {
        $this->setSeed($this->buildRandomSeed());
    }
    
    /**
     * @return integer
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * @param integer $seed
     */
    public function setSeed($seed)
    {
        $this->seed = $seed;
    }

    /**
     * @return integer
     */
    public function buildRandomSeed()
    {
        list($usec, $sec) = explode(' ', microtime());
        return (int) ($sec + ($usec * 100000));
    }

    /**
     * @param string $string
     * @return integer
     */
    public function buildSeedFromString($string)
    {
        return hexdec(substr(md5($string), 0, 10));
    }

    /**
     * @param array $array
     * @return array
     */
    public function shuffle($array)
    {
        $this->initSeed($this->getSeed());
        $array = $this->shuffleArray($array);
        $this->initSeed($this->buildRandomSeed());
        return $array;
    }

    /**
     * @param integer $seed
     */
    private function initSeed($seed)
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
    private function shuffleArray($array)
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
    private function mtShuffle($orderedArray)
    {
        $shuffledArray = array();
        
        while (count($orderedArray) > 0) {
            $key = mt_rand(0, (count($orderedArray) - 1));
            $splice = array_splice($orderedArray, $key, 1);
            $shuffledArray[] = current($splice);
        }
        
        return $shuffledArray;
    }

    /**
     * @return bool
     */
    private function isMtRandomizerAvailable()
    {
        return function_exists('mt_srand') && function_exists('mt_rand');
    }
}
