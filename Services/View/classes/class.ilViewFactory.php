<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * View factory
 * 
 * @author Stefan Schneider <schneider@hrz.uni-marburg.de>
 * @version $Id$
 * 
 *
 * @ingroup ServicesView
 */
class ilViewFactory
{
	/**
	 * @var int
	 * Full ILIAS View
	 */
	const FULL 	= 1;

	/**
	 * @var int
	 * LTI View
	 */
	const LTI 	= 2;
	
	/**
	 * @var int
	 * Exam (ToDo)
	 */
	const EXAM	= 3;
	
	/**
	 * @var int
	 * MemberView (ToDo)
	 */
	const MEMBERVIEW = 4;

	/**
	 * @var array
	 */
	private static $views = array(
					'ilFullViewGUI' => self::FULL,
					'ilLTIViewGUI' => self::LTI
					);
	
	
	/** 
	 * @return void
	 */ 
	public static function initViews() {
		global $DIC, $ilLog;
		foreach (self::$views as $key => $value) {
			$view = self::factory($value);	
			$GLOBALS[$key] = $view;
			$DIC[$key] = function ($c) use ($key) {
				return $GLOBALS[$key];
			};
			if ($DIC[$key]->checkActivation()) {
				$_SESSION['il_view_mode'] = get_class($DIC[$key]);
			}
		}
	}
	
	/**
	 * The factory
	 * @return object ilViewBase
	 */
	public static function factory($view)
	{	
		switch($view)
		{
			case self::FULL:
				include_once './Services/View/classes/class.ilFullViewGUI.php';
				return ilFullViewGUI::getInstance();
				break;
			case self::LTI:
				include_once './Services/LTI/classes/class.ilLTIViewGUI.php';
				return ilLTIViewGUI::getInstance();
				break;
			/*
			case self::EXAM:
				include_once './Modules/Test/classes/class.ilExamViewGUI.php';
				return ilExamViewGUI::getInstance();
				break;
			case self::SEB:
				include_once './Services/SEB/classes/class.ilViewSEB.php';
				return new ilViewSEB();
			case self::MEMBERVIEW:
				include_once './Services/XXXX';
				return new ilViewMemberView();
			*/
		}
	}
}
?>
