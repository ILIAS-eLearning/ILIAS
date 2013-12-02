<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/class.assAnswerSimple.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for ordering question answers
* 
* ASS_AnswerOrdering is a class for ordering question answers used in ordering questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @see ASS_AnswerSimple
*/
class ASS_AnswerOrdering extends ASS_AnswerSimple {
/**
* The random id of the answer
*
* @var integer
*/
	protected $random_id;
  
	public $answer_id = 0;
	public $ordering_depth = 0;
	
/**
* ASS_AnswerOrdering constructor
* 
* The constructor takes possible arguments an creates an instance of the ASS_AnswerOrdering object.
*
* @param string $answertext A string defining the answer text
* @param integer $random_id A random ID
* @access public
*/
	function ASS_AnswerOrdering (
		$answertext = "",
		$random_id = 0,
		$depth = 0
	)
	{
		parent::__construct($answertext, 0, 0);
		$this->setRandomID($random_id);
		$this->setOrderingDepth($depth);
	}
  
  
/**
* Returns the random ID of the answer
*
* @return integer Random ID
* @see $random_id
*/
	public function getRandomID() 
	{
		return $this->random_id;
	}

/**
* Sets the random ID of the answer
*
* @param integer $random_id A random integer value
* @see $random_id
*/
	public function setRandomID($random_id = 0) 
	{
		$this->random_id = $random_id;
	}

	public function getAdditionalOrderingFieldsByRandomId($a_random_id)
	{
		global $ilDB;

		$res = $ilDB->queryF('SELECT * FROM qpl_a_ordering WHERE random_id = %s',
			array('integer'), array($a_random_id));

		while($row = $ilDB->fetchAssoc($res))
		{
			$this->setAnswerId($row['answer_id']);
			$this->setOrderingDepth($row['depth']);
}
	}

	public function setAnswerId($a_answer_id)
	{
		$this->answer_id = $a_answer_id;
	}
	public function getAnswerId()
	{
		return $this->answer_id;
	}
	
	public function setOrderingDepth($a_ordering_depth)
	{
		$this->ordering_depth = (int)$a_ordering_depth;
	}
	public function getOrderingDepth()
	{
		return $this->ordering_depth;
	}
}
?>