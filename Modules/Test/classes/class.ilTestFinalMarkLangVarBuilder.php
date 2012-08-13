<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * builds the language variable identifier corresponding to the given passed status
 * considering the given obligations answered status
 * and the fact wether obligations are enabled or not in general
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package Modules/Test
 * @ingroup ModulesTest
 */
class ilTestFinalMarkLangVarBuilder
{
	/**
	 * the passed status calculated by achieved points
	 *
	 * @var boolean
	 */
	private $passedStatus = null;
	
	/**
	 * the fact wether all obligatory questions was answered or not
	 *
	 * @var boolean
	 */
	private $obligationsAnsweredStatus = null;

	/**
	 * the fact wether obligations are enabled for the test in general or not
	 *
	 * @var boolean
	 */
	private $obligationsEnabled = null;
	
	
	/**
	 * constructer
	 *
	 * @param boolean $passedStatus
	 * @param boolean $obligationsAnsweredStatus
	 * @param boolean $obligationsEnabled 
	 */
	public function __construct($passedStatus, $obligationsAnsweredStatus, $obligationsEnabled)
	{
		$this->passedStatus = (bool)$passedStatus;
		
		$this->obligationsAnsweredStatus = (bool)$obligationsAnsweredStatus;
		
		$this->obligationsEnabled = (bool)$obligationsEnabled;
	}

	/**
	 * getter for passedStatus
	 *
	 * @return boolean
	 */
	private function getPassedStatus()
	{
		return $this->passedStatus;
	}

	/**
	 * setter for passedStatus
	 *
	 * @param boolean $passedStatus 
	 */
	private function setPassedStatus($passedStatus)
	{
		$this->passedStatus = $passedStatus;
	}

	/**
	 * getter for obligationsAnsweredStatus
	 *
	 * @return boolean
	 */
	private function getObligationsAnsweredStatus()
	{
		return $this->obligationsAnsweredStatus;
	}

	/**
	 * setter for obligationsAnsweredStatus
	 *
	 * @param boolean $obligationsAnsweredStatus 
	 */
	private function setObligationsAnsweredStatus($obligationsAnsweredStatus)
	{
		$this->obligationsAnsweredStatus = $obligationsAnsweredStatus;
	}

	/**
	 * getter for obligationsEnabled
	 *
	 * @return boolean 
	 */
	private function areObligationsEnabled()
	{
		return $this->obligationsEnabled;
	}

	/**
	 * setter for obligationsEnabled
	 *
	 * @param boolean $obligationsEnabled 
	 */
	private function setObligationsEnabled($obligationsEnabled)
	{
		$this->obligationsEnabled = $obligationsEnabled;
	}
	
	
	/**
	 * returns the final statement language variable identifier basename
	 * that regards to given passed status as well as to the fact
	 * wether obligations are answered or not if obligations are enabled in general
	 *
	 * @return string 
	 */
	private function getPassedLangVarBasename()
	{
		if( $this->getPassedStatus() && (!$this->areObligationsEnabled() || $this->getObligationsAnsweredStatus()) )
		{
			return  'mark_tst_passed';
		}
		
		return 'mark_tst_failed';
	}
	
	/**
	 * returns the final statement language variable identifier extension
	 * that regards to the given obligations status if
	 *
	 * @return type 
	 */
	private function getObligationsLangVarExtension()
	{
		if( $this->areObligationsEnabled() && $this->getObligationsAnsweredStatus() )
		{
			return '_obligations_answered';
		}
		elseif( $this->areObligationsEnabled() && !$this->getObligationsAnsweredStatus() )
		{
			return '_obligations_missing';
		}
		
		return '';
	}
	
	
	/**
	 * builds the final statement language variable identifier
	 * based on "Passed-LangVar-Basename" and "Obligations-LangVar-Extension" 
	 *
	 * @return string
	 */
	public function build()
	{
		$langVar = $this->getPassedLangVarBasename();
		
		$langVar .= $this->getObligationsLangVarExtension();
		
		return $langVar;
	}
}
