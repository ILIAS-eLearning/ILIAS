<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
include_once 'Services/Search/classes/class.ilUserFilterGUI.php';
include_once './Services/Tracking/classes/class.ilLPObjectsTableGUI.php';

/**
* Class ilObjUserTrackingGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilLPListOfObjectsGUI: ilUserFilterGUI, ilLPObjectsTableGUI, ilTrUserObjectsPropsTableGUI, ilTrSummaryTableGUI, ilTrObjectUsersPropsTableGUI, ilTrMatrixTableGUI
*
* @package ilias-tracking
*
*/
class ilLPListOfObjectsGUI extends ilLearningProgressBaseGUI
{
	var $details_id = 0;
	var $details_type = '';
	var $details_mode = 0;

	function ilLPListOfObjectsGUI($a_mode,$a_ref_id)
	{
		global $ilUser,$ilObjDataCache;

		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id);
		
		// Set item id for details
		$this->__initDetails((int) $_REQUEST['details_id']);
	}
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilUser;

		$this->ctrl->setReturn($this, "");

		switch($this->ctrl->getNextClass())
		{
			case 'iltruserobjectspropstablegui':
				$user_id = (int)$_GET["user_id"];
				$this->ctrl->setParameter($this, "user_id", $user_id);

				$this->ctrl->setParameter($this, "details_id", $this->details_id);

				include_once("./Services/Tracking/classes/class.ilTrUserObjectsPropsTableGUI.php");
				$table_gui = new ilTrUserObjectsPropsTableGUI($this, "userDetails",
					$user_id, $this->details_obj_id, $this->details_id);
				$this->ctrl->forwardCommand($table_gui);
				break;
			
			case 'illpobjectstablegui':
				include_once './Services/Tracking/classes/class.ilLPObjectsTableGUI.php';
			    $table_gui = new ilLPObjectsTableGUI($this, "", $this->tracked_user);
				$this->ctrl->forwardCommand($table_gui);
				break;

			case 'iltrsummarytablegui':
				$cmd = "showObjectSummary";
				if(!$this->details_id)
				{
					$this->details_id = ROOT_FOLDER_ID;
					$cmd =  "show";
				}
				include_once './Services/Tracking/classes/class.ilTrSummaryTableGUI.php';
			    $table_gui = new ilTrSummaryTableGUI($this, $cmd, $this->details_id);
				$this->ctrl->forwardCommand($table_gui);
				break;

			case 'iltrmatrixtablegui':
				include_once './Services/Tracking/classes/class.ilTrMatrixTableGUI.php';
			    $table_gui = new ilTrMatrixTableGUI($this, "showUserObjectMatrix", $this->details_id);
				$this->ctrl->forwardCommand($table_gui);
				break;

			case 'iltrobjectuserspropstablegui':
				$this->ctrl->setParameter($this, "details_id", $this->details_id);
			
				include_once './Services/Tracking/classes/class.ilTrObjectUsersPropsTableGUI.php';
			    $table_gui = new ilTrObjectUsersPropsTableGUI($this, "details", $this->details_obj_id, $this->details_id);
				$this->ctrl->forwardCommand($table_gui);
				break;

			default:
			    $cmd = $this->__getDefaultCommand();
				$this->$cmd();
		}

		return true;
	}

	function updateUser()
	{
		if(isset($_GET["userdetails_id"]))
		{
			$parent = $this->details_id;
			$this->__initDetails((int)$_GET["userdetails_id"]);
		}
		
		$this->__updateUser($_REQUEST['user_id'], $this->details_obj_id);
		ilUtil::sendSuccess($this->lng->txt('trac_update_edit_user'));

		if(!isset($_GET["userdetails_id"]))
		{
			$this->details();
		}
		else
		{
			$this->__initDetails($parent);
			$this->userDetails();
		}
	}

	function editUser()
	{
		global $ilObjDataCache;

		$parent_id = $this->details_id;
		if(isset($_GET["userdetails_id"]))
		{
			$this->__initDetails((int)$_GET["userdetails_id"]);
			$sub_id = $this->details_id;
			$cancel = "userdetails";
		}
		else
		{
			$sub_id = NULL;
			$cancel = "details";
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($this->ctrl->getFormAction($this));
		$this->__showObjectDetails($info, $this->details_obj_id);
		$this->__appendUserInfo($info, (int)$_GET['user_id']);
		// $this->__appendLPDetails($info,$this->details_obj_id,(int)$_GET['user_id']);

		$this->tpl->setVariable("ADM_CONTENT", $this->__showEditUser((int)$_GET['user_id'], $parent_id, $cancel, $sub_id)."<br />".$info->getHTML());
	}

	function details()
	{
		global $ilToolbar;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_loo.html','Services/Tracking');

		// Show back button
		if($this->getMode() == LP_MODE_PERSONAL_DESKTOP or
		   $this->getMode() == LP_MODE_ADMINISTRATION)
		{
			$print_view = false;
			$this->__showButton($this->ctrl->getLinkTarget($this,'show'),$this->lng->txt('trac_view_list'));
		}
		else
		{
			$print_view = (bool)$_GET['prt'];
			if(!$print_view)
			{
				$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
				$this->ctrl->setParameter($this, 'prt', 1);
				$ilToolbar->addButton($this->lng->txt('print_view'),$this->ctrl->getLinkTarget($this,'details'), '_blank');
				$this->ctrl->setParameter($this, 'prt', '');
			}
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($this->ctrl->getFormAction($this));
		if($this->__showObjectDetails($info, $this->details_obj_id))
		{
			$this->tpl->setCurrentBlock("info");
			$this->tpl->setVariable("INFO_TABLE",$info->getHTML());
			$this->tpl->parseCurrentBlock();
		}

		$this->__showUsersList($print_view);
	}

	function __showUsersList($a_print_view = false)
	{
		if($this->isAnonymized())
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'));
			return;
		}

		$this->ctrl->setParameter($this, "details_id", $this->details_id);

		include_once "Services/Tracking/classes/class.ilTrObjectUsersPropsTableGUI.php";
		$gui = new ilTrObjectUsersPropsTableGUI($this, "details", $this->details_obj_id, $this->details_id, $a_print_view);
		
		$this->tpl->setVariable("LP_OBJECTS", $gui->getHTML());
		$this->tpl->setVariable("LEGEND", $this->__getLegendHTML());

		if($a_print_view)
		{
			echo $this->tpl->get("DEFAULT", false, false, false, false, false, false);
			exit();
		}
	}

	function userDetails()
	{
		global $ilObjDataCache, $ilToolbar;

		if($this->isAnonymized())
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'));
			return;
		}

		$this->ctrl->setParameter($this, "details_id", $this->details_id);

		$print_view = (bool)$_GET['prt'];
		if(!$print_view)
		{
			// Show back button
			$ilToolbar->addButton($this->lng->txt('trac_view_list'), $this->ctrl->getLinkTarget($this,'details'));
		}

		$user_id = (int)$_GET["user_id"];
		$this->ctrl->setParameter($this, "user_id", $user_id);

		if(!$print_view)
		{
			$this->ctrl->setParameter($this, 'prt', 1);
			$ilToolbar->addButton($this->lng->txt('print_view'),$this->ctrl->getLinkTarget($this,'userDetails'), '_blank');
			$this->ctrl->setParameter($this, 'prt', '');
		};

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_loo.html','Services/Tracking');

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($this->ctrl->getFormAction($this));
		$this->__showObjectDetails($info, $this->details_obj_id);
		$this->__appendUserInfo($info, $user_id);
		// $this->__appendLPDetails($info,$this->details_obj_id,$user_id);
		$this->tpl->setVariable("INFO_TABLE",$info->getHTML());

		include_once("./Services/Tracking/classes/class.ilTrUserObjectsPropsTableGUI.php");
		$table = new ilTrUserObjectsPropsTableGUI($this, "userDetails", $user_id,
			$this->details_obj_id, $this->details_id, $print_view);
		$this->tpl->setVariable('LP_OBJECTS', $table->getHTML());
		$this->tpl->setVariable('LEGEND', $this->__getLegendHTML());

		if($print_view)
		{
			echo $this->tpl->get("DEFAULT", false, false, false, false, false, false);
			exit();
		}
	}

	function show()
	{
		// Clear table offset
		$this->ctrl->saveParameter($this,'offset',0);

		// Show only detail of current repository item if called from repository
		switch($this->getMode())
		{
			case LP_MODE_REPOSITORY:
				$this->__initDetails($this->getRefId());
				$this->details();
				return true;
		}

		$this->__listObjects();
	}

	function __listObjects()
	{
		global $ilUser,$ilObjDataCache;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_list_objects.html','Services/Tracking');

		include_once("./Services/Tracking/classes/class.ilTrSummaryTableGUI.php");
		$lp_table = new ilTrSummaryTableGUI($this, "", ROOT_FOLDER_ID);
		
		$this->tpl->setVariable("LP_OBJECTS", $lp_table->getHTML());
		$this->tpl->setVariable('LEGEND', $this->__getLegendHTML());
	}

	function __initDetails($a_details_id)
	{
		global $ilObjDataCache;

		if(!$a_details_id)
		{
			$a_details_id = $this->getRefId();
		}
		if($a_details_id)
		{
			$_GET['details_id'] = $a_details_id;
			$this->details_id = $a_details_id;
			$this->details_obj_id = $ilObjDataCache->lookupObjId($this->details_id);
			$this->details_type = $ilObjDataCache->lookupType($this->details_obj_id);
			$this->details_mode = ilLPObjSettings::_lookupMode($this->details_obj_id);
		}
	}

	/**
	 * Show object-based summarized tracking data
	 */
	function showObjectSummary()
	{
		global $tpl, $ilToolbar;

		$print_view = (bool)$_GET['prt'];
		if(!$print_view)
		{
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
			$this->ctrl->setParameter($this, 'prt', 1);
			$ilToolbar->addButton($this->lng->txt('print_view'),$this->ctrl->getLinkTarget($this,'showObjectSummary'), '_blank');
			$this->ctrl->setParameter($this, 'prt', '');
		}

		include_once("./Services/Tracking/classes/class.ilTrSummaryTableGUI.php");
		$table = new ilTrSummaryTableGUI($this, "showObjectSummary", $this->getRefId(), $print_view);
		if(!$print_view)
		{
			$tpl->setContent($table->getHTML());
		}
		else
		{
			$tpl->setVariable("ADM_CONTENT", $table->getHTML());
			echo $tpl->get("DEFAULT", false, false, false, false, false, false);
			exit();
		}
	}

	/**
	 * Show object user matrix
	 */
	function showUserObjectMatrix()
	{
		global $tpl;

		if($this->isAnonymized())
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'));
			return;
		}
		

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_loo.html','Services/Tracking');

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($this->ctrl->getFormAction($this));
		if($this->__showObjectDetails($info, $this->details_obj_id))
		{
			$this->tpl->setCurrentBlock("info");
			$this->tpl->setVariable("INFO_TABLE",$info->getHTML());
			$this->tpl->parseCurrentBlock();
		}

		include_once("./Services/Tracking/classes/class.ilTrMatrixTableGUI.php");
		$table = new ilTrMatrixTableGUI($this, "showUserObjectMatrix", $this->getRefId());
		$this->tpl->setVariable('LP_OBJECTS', $table->getHTML());
		$this->tpl->setVariable('LEGEND', $this->__getLegendHTML());
	}
}
?>