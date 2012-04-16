<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintList.php';

/**
 * abstract parent class for concrete question hint GUI classes
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
abstract class ilAssQuestionHintAbstractGUI
{
	/**
	 * @var assQuestionGUI
	 */
	protected $questionGUI = null;
	
	/**
	 * @var assQuestion 
	 */
	protected $questionOBJ = null;
	
	/**
	 * Constructor
	 * 
	 * @param	assQuestionGUI	$questionGUI 
	 */
	public function __construct(assQuestionGUI $questionGUI)
	{
		$this->questionGUI = $questionGUI;
		$this->questionOBJ = $questionGUI->object;
	}
}
