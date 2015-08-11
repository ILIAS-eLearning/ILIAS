<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionAnswerShuffler
{
	/**
	 * @var integer
	 */
	protected $seed;
	/**
	 * @return int
	 */
	public function getSeed()
	{
		return $this->seed;
	}

	/**
	 * @param int $seed
	 */
	public function setSeed($seed)
	{
		$this->seed = $seed;
	}
	
	public function shuffle($array)
	{
		$this->initSeed($this->getSeed());
		$array = $this->shuffleArray($array);
		$this->initSeed(self::buildRandomSeed());
		return $array;
	}

	private function shuffleArray($array)
	{
		if( $this->isMtRandomizerAvailable() )
		{
			return $this->mtShuffle($array);
		}
		
		return shuffle($array);
	}

	private function initSeed($seed)
	{
		if( !$seed )
		{
			throw new ilTestQuestionPoolException('set seed first');
		}
		
		$seed = (int)$seed; // (mt_)srand seems to not cast to integer itself (string seeds avoid randomizing) !!

		if( $this->isMtRandomizerAvailable() )
		{
			mt_srand($seed);
		}
		else
		{
			srand($seed);
		}
	}
	
	private function isMtRandomizerAvailable()
	{
		return function_exists('mt_srand') && function_exists('mt_rand');
	}
	
	private function mtShuffle($orderedArray)
	{
		$shuffledArray = array();
		
		while( count($orderedArray) > 0 )
		{
			$key = mt_rand(0, (count($orderedArray)-1));
			$splice = array_splice($orderedArray, $key, 1);
			$shuffledArray[] = current($splice);
		}
		
		return $shuffledArray;
	}

	public static function buildRandomSeed()
	{
		list($usec, $sec) = explode(' ', microtime());
		return $sec + ($usec * 100000);
	}
}