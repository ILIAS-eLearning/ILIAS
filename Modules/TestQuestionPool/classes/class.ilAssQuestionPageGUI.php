<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/COPage/classes/class.ilPageObjectGUI.php');
require_once('./Modules/TestQuestionPool/classes/class.ilAssQuestionPage.php');

/**
 * Question page GUI class
 * 
 * @author Alex Killing <alex.killing@gmx.de> 
 *
 * @ilCtrl_Calls ilAssQuestionPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilAssQuestionPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilAssQuestionPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 *
 * @ingroup ModulesTestQuestionPool
 */
class ilAssQuestionPageGUI extends ilPageObjectGUI
{
	/**
	 * Constructor
	 *
	 * @param int $a_id
	 * @param int $a_old_nr
	 *
	 * @return \ilAssQuestionPageGUI
	 */
	public function __construct($a_id = 0, $a_old_nr = 0)
	{
		parent::__construct('qpl', $a_id, $a_old_nr);
		$this->setEnabledPageFocus(false);
	}

	protected function isPageContainerToBeRendered()
	{
		return $this->getRenderPageContainer();
	}
} 

