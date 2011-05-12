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
		$tmp = array();
		$tmp[] = $this->object->getTitle();
		$tmp[] = $this->object->getDescription();
		$tmp[] = ilDatePresentation::formatDate($this->object->getProperty("issued_on"));
		$tmp[] = $this->object->getProperty("result");
		$tmp[] = $this->object->getProperty("success");
		$tmp[] = $this->object->getProperty("mark");
		
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