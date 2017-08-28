<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetQuestion.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
// hey: fixRandomTestBuildable - iterator interface for collection
class ilTestRandomQuestionSetQuestionCollection implements Iterator
// hey.
{
	private $questions = array();

	public function setQuestions($questions)
	{
		$this->questions = $questions;
	}

	public function getQuestions()
	{
		return $this->questions;
	}

	public function addQuestion(ilTestRandomQuestionSetQuestion $question)
	{
		$this->questions[] = $question;
	}

	// hey: fixRandomTestBuildable - iterator interface for collection
	/* @return ilTestRandomQuestionSetQuestion */
	public function current() { return current($this->questions); }
	/* @return ilTestRandomQuestionSetQuestion */
	public function next() { return next($this->questions); }
	/* @return string */
	public function key() { return key($this->questions); }
	/* @return bool */
	public function valid() { return key($this->questions) !== null; }
	/* @return ilTestRandomQuestionSetQuestion */
	public function rewind() { return reset($this->questions); }
	// hey.
	
	public function isGreaterThan($amount)
	{
		return count($this->questions) > $amount;
	}

	public function isSmallerThan($amount)
	{
		return count($this->questions) < $amount;
	}
	
	/**
	 * @param int $requiredAmount
	 * @return int
	 */
	public function getMissingCount($requiredAmount)
	{
		// hey: fixRandomTestBuildable - fix returning missing count instead of difference (neg values!)
		$difference = $requiredAmount - count($this->questions);
		$missingCount = $difference < 0 ? 0 : $difference;
		return $missingCount;
		// hey.
	}

	public function shuffleQuestions()
	{
		shuffle($this->questions);
	}

	public function mergeQuestionCollection(self $questionCollection)
	{
		$this->questions = array_merge( $this->questions, $questionCollection->getQuestions() );
	}

	public function getUniqueQuestionCollection()
	{
		$uniqueQuestions = array();

		foreach($this->getQuestions() as $question)
		{
			/* @var ilTestRandomQuestionSetQuestion $question */

			if( !isset($uniqueQuestions[$question->getQuestionId()]) )
			{
				$uniqueQuestions[$question->getQuestionId()] = $question;
			}
		}

		$uniqueQuestionCollection = new self();
		$uniqueQuestionCollection->setQuestions($uniqueQuestions);

		return $uniqueQuestionCollection;
	}

	public function getRelativeComplementCollection(self $questionCollection)
	{
		// hey: fixRandomTestBuildable - comment for refactoring
		/**
		 * actually i would like to consider $this as quantity A
		 * passed $questionCollection is should be considered as quantity B
		 * 
		 * --> relative complement usually means all element from B missing in A
		 * 
		 * indeed we are considering $questionCollection as A and $this as B currently (!)
		 * when changing, do not forget to switch caller and param for all usages (!)
		 */
		// hey.
		
		$questionIds = array_flip( $questionCollection->getInvolvedQuestionIds() );

		$relativeComplementCollection = new self();

		foreach($this->getQuestions() as $question)
		{
			if( !isset($questionIds[$question->getQuestionId()]) )
			{
				$relativeComplementCollection->addQuestion($question);
			}
		}

		return $relativeComplementCollection;
	}
	
	// hey: fixRandomTestBuildable - advanced need for quantity tools
	/**
	 * @param ilTestRandomQuestionSetQuestionCollection $questionCollection
	 * @return ilTestRandomQuestionSetQuestionCollection
	 */
	public function getIntersectionCollection(self $questionCollection)
	{
		$questionIds = array_flip( $questionCollection->getInvolvedQuestionIds() );
		
		$intersectionCollection = new self();
		
		foreach($this->getQuestions() as $question)
		{
			if( !isset($questionIds[$question->getQuestionId()]) )
			{
				continue;
			}
			
			$intersectionCollection->addQuestion($question);
		}
		
		return $intersectionCollection;
	}
	
	/**
	 * @return int
	 */
	public function getQuestionAmount()
	{
		return count($this->getQuestions());
	}
	// hey.

	public function getInvolvedQuestionIds()
	{
		$questionIds = array();

		foreach($this->getQuestions() as $question)
		{
			$questionIds[] = $question->getQuestionId();
		}

		return $questionIds;
	}

	public function getRandomQuestionCollection($requiredAmount)
	{
		$randomKeys = $this->getRandomArrayKeys($this->questions, $requiredAmount);

		$randomQuestionCollection = new self();

		foreach($randomKeys as $randomKey)
		{
			$randomQuestionCollection->addQuestion( $this->questions[$randomKey] );
		}

		return $randomQuestionCollection;
	}

	private function getRandomArrayKeys($array, $numKeys)
	{
		if( $numKeys < 1 )
		{
			return array();
		}

		if( $numKeys > 1 )
		{
			return array_rand($array, $numKeys);
		}

		return array( array_rand($array, $numKeys) );
	}
}