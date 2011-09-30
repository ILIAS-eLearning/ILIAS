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
				$parent_id = $this->node_id;
				$this->node_id = null;
				$this->putObjectInTree($newObj, $parent_id);
				
				$this->afterSave($newObj);
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("msg_failed"));
			}
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("select_one"));
		}
		$this->create();
	}
	
	public function deliver()
	{
		$file = $path.$this->object->getFilePath();
		if($file)
		{
			ilUtil::deliverFile($file, $this->object->getTitle().".pdf");
		}
	}

	/**
	 * Render content
	 * 
	 * @param bool $a_return
	 */
	public function render($a_return = false)
	{
		global $ilUser;
		
		if(!$a_return)
		{					
			$this->deliver();
		}
		else
		{			
			$tree = new ilWorkspaceTree($ilUser->getId());
			$wsp_id = $tree->lookupNodeId($this->object->getId());
			
			$caption = $this->object->getTitle();			
			$link = $this->getAccessHandler()->getGotoLink($wsp_id, $this->object->getId());
			
			return "<a href=\"".$link."\">".$caption."</a>";
		}
	}
	
	function _goto($a_target)
	{
		$id = explode("_", $a_target);
		
		$_GET["baseClass"] = "ilsharedresourceGUI";	
		$_GET["wsp_id"] = $id[0];		
		include("ilias.php");
		exit;
	}
}

?>