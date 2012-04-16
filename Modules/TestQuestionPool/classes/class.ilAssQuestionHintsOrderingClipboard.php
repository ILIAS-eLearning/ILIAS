<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class for managing a clipboard via php session, that is used to re order a hint list
 * (user cuts a hint with a first request and is able to paste it at another position with a further request)
 * 
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHintsOrderingClipboard
{
	private $questionId = null;
	
	public function __construct(assQuestion $questionOBJ)
	{
		$this->questionId = $questionOBJ->getId();
		
		if( !isset($_SESSION[__CLASS__]) )
		{
			$_SESSION[__CLASS__] = array();
		}
		
		if( !isset($_SESSION[__CLASS__][$this->questionId]) )
		{
			$_SESSION[__CLASS__][$this->questionId] = null;
		}
	}
	
	public function resetStored()
	{
		$_SESSION[__CLASS__][$this->questionId] = null;
	}
	
	public function setStored($hintId)
	{
		$_SESSION[__CLASS__][$this->questionId] = $hintId;
	}
	
	public function getStored()
	{
		return $_SESSION[__CLASS__][$this->questionId];
	}
	
	public function isStored($hintId)
	{
		if( $_SESSION[__CLASS__][$this->questionId] === $hintId )
		{
			return true;
		}
		
		return false;
	}
	
	public function hasStored()
	{
		if( $_SESSION[__CLASS__][$this->questionId] !== null )
		{
			return true;
		}
		
		return false;
	}
}

