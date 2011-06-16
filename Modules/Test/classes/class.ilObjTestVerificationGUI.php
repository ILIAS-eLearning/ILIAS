<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ('./Services/Object/classes/class.ilObject2GUI.php');

/**
* GUI class for test verification
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPersonalDesktopGUI.php 26976 2010-12-16 13:24:38Z akill $
*
* @ilCtrl_Calls ilObjTestVerificationGUI: ilWorkspaceAccessGUI
*
* @ingroup ModulesTest
*/
class ilObjTestVerificationGUI extends ilObject2GUI
{
	public function getType()
	{
		return "tstv";
	}

	/**
	 * List all tests in which current user participated
	 */
	public function create()
	{
		global $ilTabs;

		$this->lng->loadLanguageModule("tstv");

		$ilTabs->setBackTarget($this->lng->txt("back"),
			$this->ctrl->getLinkTarget($this, "cancel"));

		include_once "Modules/Test/classes/tables/class.ilTestVerificationTableGUI.php";
		$table = new ilTestVerificationTableGUI($this, "create");
		$this->tpl->setContent($table->getHTML());
	}

	/**
	 * create new instance and save it
	 */
	public function save()
	{
		global $ilUser;
		
		$test_id = $_REQUEST["tst_id"];
		if($test_id)
		{
			include_once "Modules/Test/classes/class.ilObjTest.php";
			$test = new ilObjTest($test_id, false);

			include_once "Modules/Test/classes/class.ilObjTestVerification.php";
			$newObj = ilObjTestVerification::createFromTest($test, $ilUser->getId());
			if($newObj)
			{
				$newObj->create();

				$parent_id = $this->node_id;
				$this->node_id = null;
				$this->putObjectInTree($newObj, $parent_id);
				
				$this->afterSave($newObj);
			}
		}

		ilUtil::sendFailure($this->lng->txt("select_one"));
		$this->create();
	}

	/**
	 * Render content
	 * 
	 * @param bool $a_return
	 */
	public function render($a_return = false)
	{
		global $lng;
		
		$lng->loadLanguageModule("assessment");
		
		$tmp = array();
		$tmp[] = $lng->txt("title").": ".$this->object->getTitle();
		$tmp[] = $lng->txt("description").": ".$this->object->getDescription();
		$tmp[] = $lng->txt("created").": ".ilDatePresentation::formatDate($this->object->getProperty("issued_on"));
		$tmp[] = $lng->txt("tst_score_reporting").": ".$this->object->getProperty("result");
		
		if($this->object->getProperty("success"))
		{
			$tmp[] = $lng->txt("result").": ".$lng->txt("tst_mark_passed");
		}
		else
		{
			$tmp[] = $lng->txt("result").": ".$lng->txt("failed_short");
		}		
		
		$tmp[] = $lng->txt("tst_mark").": ".$this->object->getProperty("mark");
		
		if(!$a_return)
		{
			$this->tpl->setContent(implode("<br>", $tmp));
		}
		else
		{
			return implode("<br>", $tmp);
		}
	}
}

?>