<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ('./Services/Object/classes/class.ilObject2GUI.php');

/**
* GUI class for test verification
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPersonalDesktopGUI.php 26976 2010-12-16 13:24:38Z akill $
*
* @ilCtrl_Calls ilObjTestVerificationGUI:
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
		global $ilUser;

		include_once "Modules/Test/classes/class.ilObjTest.php";
		foreach(ilObjTest::_lookupFinishedUserTests($ilUser->getId()) as $test_id => $passed)
		{
			$this->ctrl->setParameter($this, "tst_id", $test_id);
			$content .= "<a href=\"".$this->ctrl->getLinkTarget($this, "save").
				"\">".$test_id." (".$passed.")</a>";
		}

		$this->tpl->setContent($content);
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
				$this->putObjectInTree($newObj);
				
				$this->afterSave($newObj);
			}
		}

		ilUtil::sendFailure($this->lng->txt("select_one"));
		$this->create();
	}
}

?>