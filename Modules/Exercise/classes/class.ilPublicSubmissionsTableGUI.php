<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Exercise/classes/class.ilExAssignment.php");

/**
* Exercise member table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilPublicSubmissionsTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_exc, $a_ass_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->exc = $a_exc;
		$this->exc_id = $this->exc->getId();
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		
		$this->ass_id = $a_ass_id;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData(ilExAssignment::getMemberListData($this->exc_id, $this->ass_id));
		$this->setTitle($lng->txt("exc_assignment").": ".
			ilExAssignment::lookupTitle($a_ass_id));
		$this->setTopCommands(true);
		//$this->setLimit(9999);
		
		$this->addColumn($this->lng->txt("name"), "name");
		$this->addColumn($this->lng->txt("exc_submission"), "");
		
		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.exc_public_submissions_row.html", "Modules/Exercise");
		//$this->disable("footer");
		$this->setEnableTitle(true);
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($member)
	{
		global $lng, $ilCtrl;

		include_once "./Services/Object/classes/class.ilObjectFactory.php";		
		$member_id = $member["usr_id"];
		if(!($mem_obj = ilObjectFactory::getInstanceByObjId($member_id,false)))
		{
			continue;
		}

		// name and login
		$this->tpl->setVariable("TXT_NAME",
			$member["name"]);
		$this->tpl->setVariable("TXT_LOGIN",
			"[".$member["login"]."]");
			
		// image
		$this->tpl->setVariable("USR_IMAGE",
			$mem_obj->getPersonalPicturePath("xxsmall"));
		$this->tpl->setVariable("USR_ALT", $lng->txt("personal_picture"));

		// submission:
		// see if files have been resubmmited after solved
		$last_sub =
			ilExAssignment::getLastSubmission($this->ass_id, $member_id);
		if ($last_sub)
		{
			$last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub,IL_CAL_DATETIME));
		}
		else
		{
			$last_sub = "---";
		}

		// nr of submitted files
		$this->tpl->setVariable("TXT_SUBMITTED_FILES",
			$lng->txt("exc_files_returned"));
		$sub_cnt = count(ilExAssignment::getDeliveredFiles($this->exc_id, $this->ass_id, $member_id));
		$this->tpl->setVariable("VAL_SUBMITTED_FILES",
			$sub_cnt);
		
		// download command
		$ilCtrl->setParameter($this->parent_obj, "member_id", $member_id);
		if ($sub_cnt > 0)
		{
			// #15126
			include_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");
			$button = ilLinkButton::getInstance();				
			$button->setCaption("exc_download_files");
			$button->setUrl($ilCtrl->getLinkTarget($this->parent_obj, "downloadReturned"));							
			$button->setOmitPreventDoubleSubmission(true);			
			$this->tpl->setVariable("BTN_DOWNLOAD", $button->render());			
		}

		$this->tpl->parseCurrentBlock();
	}

}
?>