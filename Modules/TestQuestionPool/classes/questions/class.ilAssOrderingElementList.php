<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElement.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssOrderingElementList implements Iterator
{
	const SOLUTION_IDENTIFIER_BUILD_MAX_TRIES = 1000;
	const SOLUTION_IDENTIFIER_VALUE_INTERVAL = 1;
	const SOLUTION_IDENTIFIER_START_VALUE = 0;
	
	const RANDOM_IDENTIFIER_BUILD_MAX_TRIES = 1000;
	const RANDOM_IDENTIFIER_RANGE_LOWER_BOUND = 1;
	const RANDOM_IDENTIFIER_RANGE_UPPER_BOUND = 100000;
	
	const IDENTIFIER_TYPE_SOLUTION = 'SolutionIds';
	const IDENTIFIER_TYPE_RANDOM = 'RandomIds';
	
	/**
	 * @var array[integer]
	 */
	protected static $identifierRegistry = array(
		self::IDENTIFIER_TYPE_SOLUTION => array(),
		self::IDENTIFIER_TYPE_RANDOM => array()
	);
	
	/**
	 * @var integer
	 */
	protected $questionId = null;
	
	/**
	 * @var array
	 */
	protected $elements = array();
	
	/**
	 * @return integer
	 */
	public function getQuestionId()
	{
		return $this->questionId;
	}
	
	/**
	 * @param integer $questionId
	 */
	public function setQuestionId($questionId)
	{
		$this->questionId = $questionId;
	}
	
	/**
	 * load elements from database
	 */
	public function loadFromDb()
	{
		$ilDB = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['ilDB'] : $GLOBALS['ilDB'];
		
		$result = $ilDB->queryF(
			"SELECT * FROM qpl_a_ordering WHERE question_fi = %s ORDER BY solution_key ASC",
			array('integer'), array($this->getQuestionId())
		);
		
		while( $row = $ilDB->fetchAssoc($result) )
		{
			$element = new ilAssOrderingElement();
			
			$element->setRandomIdentifier($row['random_id']);
			$element->setSolutionIdentifier($row['solution_key']);
			
			$element->setPosition($row['order_position']);
			$element->setIndentation($row["depth"]);
			
			$element->setContent($row['answertext']);
			
			$this->addElement($element);
			$this->registerIdentifiers($element);
		}
	}
	
	/**
	 * TODO: refactor to a select/update/insert strategy incl. deleting all except existing
	 */
	public function saveToDb()
	{
		/** @var ilDBInterface $ilDB */
		$ilDB = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['ilDB'] : $GLOBALS['DIC']['ilDB'];  
		
		$ilDB->manipulateF(
			"DELETE FROM qpl_a_ordering WHERE question_fi = %s",
			array( 'integer' ), array( $this->getQuestionId() )
		);
		
		foreach($this as $orderElement)
		{
			$this->ensureValidIdentifiers($orderElement);
			
			$ilDB->insert('qpl_a_ordering', array(
				'answer_id' => array( 'integer', $ilDB->nextId('qpl_a_ordering') ),
				'question_fi' => array( 'integer', $this->getQuestionId() ),
				'answertext' => array( 'text', $orderElement->getContent()),
				'solution_keyvalue' => array( 'integer', $orderElement->getSolutionIdentifier() ),
				'random_id' => array( 'integer', $orderElement->getRandomIdentifier() ),
				'order_position' => array( 'integer', $orderElement->getPosition() ),
				'depth' => array( 'integer', $orderElement->getIndentation() ),
				'tstamp' => array( 'integer', time() )
			));
		}
	}
	
	/**
	 * resets elements
	 */
	public function resetElements()
	{
		$this->elements = array();
	}
	
	/**
	 * @param $elements
	 */
	public function setElements($elements)
	{
		foreach($elements as $element)
		{
			$this->addElement($element);
		}
	}
	
	/**
	 * @return array
	 */
	public function getElements()
	{
		return $this->elements;
	}
	
	/**
	 * @return array
	 */
	public function getRandomIdentifierIndexedElements()
	{
		return $this->getIndexedElements(self::IDENTIFIER_TYPE_RANDOM);
	}
	
	/**
	 * @return array
	 */
	public function getSolutionIdentifierIndexedElements()
	{
		return $this->getIndexedElements(self::IDENTIFIER_TYPE_SOLUTION);
	}
	
	/**
	 * @return array
	 */
	protected function getIndexedElements($identifierType)
	{
		$elements = array();
		
		foreach($this as $element)
		{
			$elements[$this->fetchIdentifier($element, $identifierType)] = $element;
		}
		
		return $elements;
	}
	
	/**
	 * @param ilAssOrderingElement $element
	 */
	public function addElement(ilAssOrderingElement $element)
	{
		$this->registerIdentifiers($element);
		$this->elements[] = $element;
	}
	
	/**
	 * @param $randomIdentifier
	 * @return array
	 */
	public function getElementByRandomIdentifier($randomIdentifier)
	{
		foreach($this as $element)
		{
			if( $element->getRandomIdentifier() != $randomIdentifier )
			{
				continue;
			}
			
			return $element;
		}
		
		return null;
	}
	
	/**
	 * @param $randomIdentifier
	 * @return array
	 */
	public function getElementBySolutionIdentifier($solutionIdentifier)
	{
		foreach($this as $element)
		{
			if( $element->getSolutionIdentifier() != $solutionIdentifier )
			{
				continue;
			}
			
			return $element;
		}
		return null;
	}
	
	/**
	 * @return array
	 */
	protected function getRegisteredSolutionIdentiers()
	{
		return $this->getRegisteredIdentifiers(self::IDENTIFIER_TYPE_SOLUTION);
	}
	
	/**
	 * @return array
	 */
	protected function getRegisteredRandomIdentiers()
	{
		return $this->getRegisteredIdentifiers(self::IDENTIFIER_TYPE_RANDOM);
	}
	
	/**
	 * @param string $identifierType
	 * @return array
	 */
	protected function getRegisteredIdentifiers($identifierType)
	{
		if( !isset(self::$identifierRegistry[$identifierType][$this->getQuestionId()]) )
		{
			return array();
		}
		
		return self::$identifierRegistry[$identifierType][$this->getQuestionId()];
	}
	
	/**
	 * @param ilAssOrderingElement $element
	 */
	protected function ensureValidIdentifiers(ilAssOrderingElement $element)
	{
		$this->ensureValidIdentifier($element, self::IDENTIFIER_TYPE_SOLUTION);
		$this->ensureValidIdentifier($element, self::IDENTIFIER_TYPE_RANDOM);
	}
	
	/**
	 * @param ilAssOrderingElement $element
	 * @param string $identifierType
	 */
	protected function ensureValidIdentifier(ilAssOrderingElement $element, $identifierType)
	{
		$identifier = $this->fetchIdentifier($element, $identifierType);
		
		if( !$this->isValidIdentifier($identifierType, $identifier) )
		{
			$identifier = $this->buildIdentifier($identifierType);
			$this->populateIdentifier($element, $identifierType, $identifier);
		}
	}
	
	/**
	 * @param ilAssOrderingElement $element
	 */
	protected function registerIdentifiers(ilAssOrderingElement $element)
	{
		$this->registerIdentifier($element, self::IDENTIFIER_TYPE_SOLUTION);
		$this->registerIdentifier($element, self::IDENTIFIER_TYPE_RANDOM);
	}
	
	/**
	 * @param ilAssOrderingElement $element
	 * @param string $identifierType
	 * @throws ilTestQuestionPoolException
	 */
	protected function registerIdentifier(ilAssOrderingElement $element, $identifierType)
	{
		$registry = self::$identifierRegistry[$identifierType];
		
		if( !isset($registry[$this->getQuestionId()]) )
		{
			$registry[$this->getQuestionId()] = array();
		}
		
		$registry[$this->getQuestionId()][] = $this->fetchIdentifier($element, $identifierType);
	}
	
	/**
	 * @param ilAssOrderingElement $element
	 * @param string $identifierType
	 * @return bool
	 * @throws ilTestQuestionPoolException
	 */
	protected function isIdentifierRegistered(ilAssOrderingElement $element, $identifierType)
	{
		$registry = self::$identifierRegistry[$identifierType];
		
		if( !isset($registry[$this->getQuestionId()]) )
		{
			return false;
		}
		
		$identifier = $this->fetchIdentifier($element, $identifierType);
		
		if( !in_array($identifier, $registry[$this->getQuestionId()]) )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param ilAssOrderingElement $element
	 * @param string $identifierType
	 * @return int
	 * @throws ilTestQuestionPoolException
	 */
	protected function fetchIdentifier(ilAssOrderingElement $element, $identifierType)
	{
		switch($identifierType)
		{
			case self::IDENTIFIER_TYPE_SOLUTION: return $element->getSolutionIdentifier();
			case self::IDENTIFIER_TYPE_RANDOM: return $element->getRandomIdentifier();
		}
		
		$this->throwUnknownIdentifierTypeException($identifierType);
	}
	
	/**
	 * @param ilAssOrderingElement $element
	 * @param string $identifierType
	 * @param $identifier
	 * @throws ilTestQuestionPoolException
	 */
	protected function populateIdentifier(ilAssOrderingElement $element, $identifierType, $identifier)
	{
		switch($identifierType)
		{
			case self::IDENTIFIER_TYPE_SOLUTION: $element->setSolutionIdentifier($identifier); break;
			case self::IDENTIFIER_TYPE_RANDOM: $element->setRandomIdentifier($identifier); break;
		}
		
		$this->throwUnknownIdentifierTypeException($identifierType);
	}
	
	/**
	 * @param string $identifierType
	 * @param $identifier
	 * @return mixed
	 * @throws ilTestQuestionPoolException
	 */
	protected function isValidIdentifier($identifierType, $identifier)
	{
		switch($identifierType)
		{
			case self::IDENTIFIER_TYPE_SOLUTION: return $this->isValidSolutionIdentifier($identifier);
			case self::IDENTIFIER_TYPE_RANDOM: return $this->isValidRandomIdentifier($identifier);
		}
		
		$this->throwUnknownIdentifierTypeException($identifierType);
	}
	
	/**
	 * @param string $identifierType
	 * @return integer
	 * @throws ilTestQuestionPoolException
	 */
	protected function buildIdentifier($identifierType)
	{
		switch($identifierType)
		{
			case self::IDENTIFIER_TYPE_SOLUTION: return $this->buildSolutionIdentifier();
			case self::IDENTIFIER_TYPE_RANDOM: return $this->buildRandomIdentifier();
		}
		
		$this->throwUnknownIdentifierTypeException($identifierType);
	}
	
	/**
	 * @param string $identifierType
	 * @throws ilTestQuestionPoolException
	 */
	protected function throwUnknownIdentifierTypeException($identifierType)
	{
		throw new ilTestQuestionPoolException('unknown identifier type: '.$identifierType);
	}
	
	/**
	 * @param string $identifierType
	 * @throws ilTestQuestionPoolException
	 */
	protected function throwCouldNotBuildRandomIdentifierException($maxTries)
	{
		throw new ilTestQuestionPoolException('could not build random identifier (max tries: '.$maxTries.')');
	}
	
	protected function isValidSolutionIdentifier($identifier)
	{
		if( !is_numeric($identifier) )
		{
			return false;
		}
		
		if( $identifier != (int)$identifier )
		{
			return false;
		}
		
		if( $identifier < 0 )
		{
			return false;
		}
		
		return true;
	}
	
	protected function isValidRandomIdentifier($identifier)
	{
		if( !is_numeric($identifier) )
		{
			return false;
		}
		
		if( $identifier != (int)$identifier )
		{
			return false;
		}
		
		if( $identifier < self::RANDOM_IDENTIFIER_RANGE_LOWER_BOUND )
		{
			return false;
		}
		
		if( $identifier > self::RANDOM_IDENTIFIER_RANGE_UPPER_BOUND )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * @return integer|null $lastSolutionIdentifier
	 */
	protected function getLastSolutionIdentifier()
	{
		$lastSolutionIdentifier = null;
		
		foreach($this->getRegisteredSolutionIdentiers() as $registeredIdentifier)
		{
			if( $lastSolutionIdentifier > $registeredIdentifier )
			{
				continue;
			}
			
			$lastSolutionIdentifier = $registeredIdentifier;
		}
		
		return $lastSolutionIdentifier;
	}
	
	/**
	 * @return integer|null $nextSolutionIdentifier
	 */
	protected function buildSolutionIdentifier()
	{
		$nextSolutionIdentifier = $this->getLastSolutionIdentifier() + self::SOLUTION_IDENTIFIER_VALUE_INTERVAL;
		return $nextSolutionIdentifier;
	}
	
	/**
	 * @return int $randomIdentifier
	 * @throws ilTestQuestionPoolException
	 */
	protected function buildRandomIdentifier()
	{
		$usedTriesCounter = 0;
		
		do
		{
			if( $usedTriesCounter >= self::RANDOM_IDENTIFIER_BUILD_MAX_TRIES )
			{
				$this->throwCouldNotBuildRandomIdentifierException(self::RANDOM_IDENTIFIER_BUILD_MAX_TRIES);
			}
			
			$usedTriesCounter++;
			
			$lowerBound = self::RANDOM_IDENTIFIER_RANGE_LOWER_BOUND;
			$upperBound = self::RANDOM_IDENTIFIER_RANGE_UPPER_BOUND;
			$randomIdentifier = mt_rand($lowerBound, $upperBound);
			
			
		}
		while( $this->isIdentifierRegistered($randomIdentifier, self::IDENTIFIER_TYPE_RANDOM)  );
		
		return $randomIdentifier;
	}

	/**
	 * @return ilAssOrderingElement
	 */
	public function current() { return current($this->elements); }
	
	/**
	 * @return ilAssOrderingElement
	 */
	public function next() { return next($this->elements); }
	
	/**
	 * @return integer|bool
	 */
	public function key() { return key($this->elements); }
	
	/**
	 * @return bool
	 */
	public function valid() { return ($this->key() !== null); }
	
	/**
	 * @return ilAssOrderingElement
	 */
	public function rewind() { return reset($this->elements); }
}