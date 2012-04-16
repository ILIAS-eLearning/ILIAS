<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/exceptions/class.ilTestQuestionPoolException.php';

/**
 * Model class for managing a question hint
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHint
{
	private $id = null;
	
	private $questionId = null;
	
	private $index = null;
	
	private $points = null;
	
	private $text = null;
	
	public function __construct()
	{
		
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = (int)$id;
	}

	public function getQuestionId()
	{
		return $this->questionId;
	}

	public function setQuestionId($questionId)
	{
		$this->questionId = (int)$questionId;
	}

	public function getIndex()
	{
		return $this->index;
	}

	public function setIndex($index)
	{
		$this->index = (int)$index;
	}

	public function getPoints()
	{
		return $this->points;
	}

	public function setPoints($points)
	{
		$this->points = (float)$points;
	}

	public function getText()
	{
		return $this->text;
	}

	public function setText($text)
	{
		$this->text = $text;
	}
	
	public function load($id)
	{
		global $ilDB;
		
		$query = "
			SELECT	qht_hint_id,
					qht_question_fi,
					qht_hint_index,
					qht_hint_points,
					qht_hint_text
					
			FROM	qpl_hints
			
			WHERE	qht_hint_id = %s
		";
		
		$res = $ilDB->queryF(
				$query, array('integer'), array((int)$id)
		);
		
		while( $row = $ilDB->fetchAssoc($res) )
		{
			self::assignDbRow($this, $row);
			
			return true;
		}
		
		return false;
	}
	
	public function save()
	{
		if( $this->getId() )	return $this->update();
		else					return $this->insert();
	}
	
	private function update()
	{
		global $ilDB;
		
		return $ilDB->update(
				'qpl_hints',
				array(
					'qht_question_fi'	=> array('integer', $this->getQuestionId()),
					'qht_hint_index'	=> array('integer', $this->getIndex()),
					'qht_hint_points'	=> array('float', $this->getPoints()),
					'qht_hint_text'		=> array('text', $this->getText())
				),
				array(
					'qht_hint_id'		=> array('integer', $this->getId())
				)
		);
	}
	
	private function insert()
	{
		global $ilDB;
		
		$this->setId($ilDB->nextId('qpl_hints'));
		
		return $ilDB->insert('qpl_hints', array(
			'qht_hint_id'		=> array('integer', $this->getId()),
			'qht_question_fi'	=> array('integer', $this->getQuestionId()),
			'qht_hint_index'	=> array('integer', $this->getIndex()),
			'qht_hint_points'	=> array('float', $this->getPoints()),
			'qht_hint_text'		=> array('text', $this->getText())
		));
	}
	
	public function delete()
	{
		return self::deleteById($this->getId());
	}
	
	public static function assignDbRow(self $questionHint, $hintDbRow)
	{
		foreach($hintDbRow as $field => $value)
		{
			switch($field)
			{
				case 'qht_hint_id':			$questionHint->setId($value); break;
				case 'qht_question_fi':		$questionHint->setQuestionId($value); break;
				case 'qht_hint_index':		$questionHint->setIndex($value); break;
				case 'qht_hint_points':		$questionHint->setPoints($value); break;
				case 'qht_hint_text':		$questionHint->setText($value); break;
				
				default:	throw new ilTestQuestionPoolException("invalid db field identifier ($field) given!");
			}
		}
	}
	
	public static function deleteById($hintId)
	{
		global $ilDB;
		
		$query = "
			DELETE FROM		qpl_hints
			WHERE			qht_hint_id = %s
		";
		
		return $ilDB->manipulateF(
				$query, array('integer'), array($hintId)
		);
	}
	
	public static function getInstanceById($hintId)
	{
		$questionHint = new self();
		$questionHint->load($hintId);
		return $questionHint;
	}
}
