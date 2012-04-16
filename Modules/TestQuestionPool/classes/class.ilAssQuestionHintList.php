<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHint.php';

/**
 * Model class for managing lists of hints for a question
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHintList implements Iterator
{
	/**
	 * @var array
	 */
	private $questionHints = array();
	
	/**
	 * @return mixed 
	 */
	public function current() { return current($this->questionHints); }

	/**
	 * @return mixed 
	 */
	public function rewind() { return reset($this->questionHints); }
	
	/**
	 * @return mixed 
	 */
	public function next() { return next($this->questionHints); }
	
	/**
	 * @return mixed 
	 */
	public function key() { return key($this->questionHints); }
	
	/**
	 * @return boolean
	 */
	public function valid() { return key($this->questionHints) !== null; }
	
	/**
	 * constructor
	 */
	public function __construct() { }
	
	/**
	 * adds a question hint object to the current list instance
	 * 
	 * @param	ilAssQuestionHint	$questionHint
	 */
	public function addHint(ilAssQuestionHint $questionHint)
	{
		$this->questionHints[] = $questionHint;
	}
	
	/**
	 * returns the question hint object relating to the passed hint id
	 *
	 * @param	integer				$hintId
	 * @return	ilAssQuestionHint	$questionHint
	 */
	public function getHint($hintId)
	{
		foreach($this as $questionHint)
		{
			/* @var $questionHint ilAssQuestionHint */

			if( $questionHint->getId() == $hintId )
			{
				return $questionHint;
			}
		}
		
		require_once 'Modules/TestQuestionPool/exceptions/class.ilTestQuestionPoolException.php';
		throw new ilTestQuestionPoolException("hint with id $hintId does not exist in this list");
	}
	
	/**
	 * checks wether a question hint object
	 * relating to the passed id exists or not
	 *
	 * @param	integer		$hintId
	 * @return	boolean		$hintExists
	 */
	public function hintExists($hintId)
	{
		foreach($this as $questionHint)
		{
			/* @var $questionHint ilAssQuestionHint */

			if( $questionHint->getId() == $hintId )
			{
				return true;
			}
		}
		
		return false;
	}
	
	public function reIndex()
	{
		$counter = 0;
		
		foreach($this as $questionHint)
		{
			/* @var $questionHint ilAssQuestionHint */
			
			$questionHint->setIndex(++$counter);
			$questionHint->save();
		}
	}
	
	/**
	 * returns an array with data of the hints in this list
	 * that is adopted to be used as table gui data
	 * 
	 * @return array
	 */
	public function getTableData()
	{
		$tableData = array();
		
		foreach($this as $questionHint)
		{
			/* @var $questionHint ilAssQuestionHint */

			$tableData[] = array(
				'hint_id'		=> $questionHint->getId(),
				'hint_index'	=> $questionHint->getIndex(),
				'hint_points'	=> $questionHint->getPoints(),
				'hint_text'		=> $questionHint->getText()
			);
		}
		
		return $tableData;
	}
	
	/**
	 * instantiates a question hint list for the passed question id
	 * 
	 * @global	ilDB	$ilDB
	 * @param	integer	$questionId
	 * @return	self	$questionHintList
	 */
	public static function getListByQuestionId($questionId)
	{
		global $ilDB;
		
		$query = "
			SELECT		qht_hint_id,
						qht_question_fi,
						qht_hint_index,
						qht_hint_points,
						qht_hint_text
					
			FROM		qpl_hints
			
			WHERE		qht_question_fi = %s
			
			ORDER BY	qht_hint_index ASC
		";
		
		$res = $ilDB->queryF(
				$query, array('integer'), array((int)$questionId)
		);
		
		$questionHintList = new self();
		
		while( $row = $ilDB->fetchAssoc($res) )
		{
			$questionHint = new ilAssQuestionHint();
			
			ilAssQuestionHint::assignDbRow($questionHint, $row);
			
			$questionHintList->addHint($questionHint);
		}
		
		return $questionHintList;
	}
	
	/**
	 * instantiates a question hint list for the passed hint ids
	 * 
	 * @global	ilDB	$ilDB
	 * @param	array	$hintIds
	 * @return	self	$questionHintList
	 */
	public static function getListByHintIds($hintIds)
	{
		global $ilDB;
		
		$qht_hint_id__IN__hintIds = $ilDB->in('qht_hint_id', $hintIds, false, 'integer');
		
		$query = "
			SELECT		qht_hint_id,
						qht_question_fi,
						qht_hint_index,
						qht_hint_points,
						qht_hint_text
					
			FROM		qpl_hints
			
			WHERE		$qht_hint_id__IN__hintIds
			
			ORDER BY	qht_hint_index ASC
		";
		
		$res = $ilDB->query($query);
		
		$questionHintList = new self();
		
		while( $row = $ilDB->fetchAssoc($res) )
		{
			$questionHint = new ilAssQuestionHint();
			
			ilAssQuestionHint::assignDbRow($questionHint, $row);
			
			$questionHintList->addHint($questionHint);
		}
		
		return $questionHintList;
	}
	
	/**
	 * determines the next index to be used for a new hint
	 * that is to be added to the list of existing hints
	 * regarding to the question with passed question id
	 *
	 * @global	ilDB		$ilDB $ilDB
	 * @param	integer		$questionId
	 * @return	integer		$nextIndex 
	 */
	public static function getNextIndexByQuestionId($questionId)
	{
		global $ilDB;
		
		$query = "
			SELECT		( MAX(qht_hint_index) + 1 ) next_index
					
			FROM		qpl_hints
			
			WHERE		qht_question_fi = %s
		";
		
		$res = $ilDB->queryF(
				$query, array('integer'), array((int)$questionId)
		);
		
		$row = $ilDB->fetchAssoc($res);
		
		return $row['next_index'];
	}
}
