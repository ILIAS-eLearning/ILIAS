<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetQuestion.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestRandomQuestionSetQuestionCollection
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

	public function isGreaterThan($amount)
	{
		return count($this->questions) > $amount;
	}

	public function isSmallerThan($amount)
	{
		return count($this->questions) < $amount;
	}

	public function getMissingCount($amount)
	{
		return $amount - count($this->questions);
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