<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilRegisterGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
*/
class ilRegisterGUI
{
	var $lng;
	var $ilias;
	var $tpl;
	var $tree;
	var $objDefinition;
	var $ctrl;
	var $cmd;
	var $ilErr;
	var $object;

	/**
	* Constructor
	* @access	public
	*/
	function ilRegisterGUI()
	{
		global $lng, $ilias, $tpl, $tree, $objDefinition, $ilCtrl, $ilErr;

		$this->lng =& $lng;
		$this->lng->loadLanguageModule('crs');

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->tree =& $tree;
		$this->objDefinition =& $objDefinition;
		$this->ilErr =& $ilErr;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id"));
		$this->ctrl->setParameter($this,"user_id",$this->ilias->account->getId());

		// get object of current ref id
		$this->object =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		if ($this->isUserAlreadyRegistered())
		{
			$this->ilErr->raiseError($this->lng->txt("grp_already_applied"),$this->ilErr->MESSAGE);
		}

		$cmd = $this->ctrl->getCmd();

		if (empty($cmd))
		{
			$cmd = "cancel";
		}

		$this->cmd = $cmd;
		$this->$cmd();	
	}
	
	function showRegistrationForm()
	{
		include_once 'Modules/Course/classes/class.ilObjCourseGrouping.php';

		global $rbacsystem, $ilias, $lng;
		
		switch ($this->object->getRegistrationFlag())
		{
			case 0:
				$stat = $this->lng->txt("group_no_registration");
				$msg  = $this->lng->txt("group_no_registration_msg");
				$readonly ="readonly";
				$subject ="";
				$cmd_submit = "subscribe";
				$txt_submit = $this->lng->txt("grp_register");
				break;

			case 1:
				if ($this->object->registrationPossible() == true)
				{
					$stat = $this->lng->txt("group_req_registration");
					$msg  = $this->lng->txt("group_req_registration_msg");
					$cmd_submit = "apply";
					$txt_submit = $this->lng->txt("request_membership");
					$txt_subject =$this->lng->txt("subject").":";
					$textfield = "<textarea name=\"subject\" value=\"{SUBJECT}\" cols=\"50\" rows=\"5\" size=\"255\"></textarea>";
				}
				else
				{
					$no_cancel = true;
					$msg = $this->lng->txt("group_registration_expired_msg");
					$msg_send = "ilias.php?baseClass=ilMailGUI&mobj_id=3&type=new&rcp_to=root";
					$cmd_submit = "cancel";
					$txt_submit = $this->lng->txt("grp_back");
					$readonly = "readonly";
					$stat = $this->lng->txt("group_registration_expired");
					ilUtil::sendInfo($this->lng->txt("registration_expired"));
				}
				break;

			case 2:
				if ($this->object->registrationPossible() == true)
				{
					$stat = $this->lng->txt("group_req_password");//"Registrierungpasswort erforderlich";
					$msg = $this->lng->txt("group_password_registration_msg");
					$txt_subject =$this->lng->txt("password").":";
					$txt_submit = $this->lng->txt("grp_register");
					$textfield = "<input name=\"subject\" value=\"{SUBJECT}\" type=\"password\" size=\"40\" maxlength=\"70\" style=\"width:300px;\"/>";
					$cmd_submit = "apply";
				}
				else
				{
					$no_cancel = true;
					$msg = $this->lng->txt("group_registration_expired_msg");
					$msg_send = "ilias.php?baseClass=ilMailGUI&mobj_id=3&type=new&rcp_to=root";
					$cmd_submit = "cancel";
					$txt_submit = $this->lng->txt("grp_back");
					$readonly = "readonly";
					$stat = $this->lng->txt("group_registration_expired");
					ilUtil::sendInfo($this->lng->txt("registration_expired"));
				}
				break;
		}

		if ($no_cancel !== true)
		{
			$this->tpl->setCurrentBlock("btn_cancel");
			$this->tpl->setVariable("CMD_CANCEL","cancel");
			$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
			$this->tpl->parseCurrentBlock();
		}

		if(!$rbacsystem->checkAccess("join", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
			return;
		}

		$submit_btn = true;
		if(!ilObjCourseGrouping::_checkGroupingDependencies($this->object))
		{
			ilUtil::sendInfo($this->object->getMessage());
			$submit_btn = false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "tbldesc", "tpl.grp_accessdenied.html");
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath("icon_grp.gif"));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt("obj_grp"));
		$this->tpl->setVariable("TITLE",$this->lng->txt("grp_registration"));
		$this->tpl->setVariable("TXT_MESSAGE",$msg);
		$this->tpl->setVariable("TXT_GRP_NAME", $this->lng->txt("group_name"));
		$this->tpl->setVariable("GRP_NAME",$this->object->getTitle());
		$this->tpl->setVariable("TXT_GRP_DESC",$this->lng->txt("group_desc"));
		$this->tpl->setVariable("GRP_DESC",$this->object->getDescription());
		//$this->tpl->setVariable("TXT_GRP_OWNER",$this->lng->txt("owner"));
		//$this->tpl->setVariable("GRP_OWNER",$owner->getLogin());
		//$this->tpl->setVariable("TXT_GRP_STATUS",$this->lng->txt("group_status"));
		//$this->tpl->setVariable("GRP_STATUS", $stat);
		$this->tpl->setVariable("TXT_INFO_REG",$this->lng->txt("group_info_reg"));
		$this->tpl->setVariable("INFO_REG", $msg);

		if(strlen($txt_subject))
		{
			$this->tpl->setVariable("TXT_SUBJECT",$txt_subject);
			$this->tpl->setVariable("SUBJECT",$textfield);
		}
		if(strlen($message = ilObjCourseGrouping::_getGroupingItemsAsString($this->object)))
		{
			$this->tpl->setVariable("TXT_MEMBER_LIMIT",$this->lng->txt('groupings'));
			$this->tpl->setVariable("MEMBER_LIMIT",$this->lng->txt('crs_grp_info_reg').$message);
		}

		if($submit_btn)
		{
			$this->tpl->setVariable("TXT_SUBMIT",$txt_submit);
			$this->tpl->setVariable("CMD_SUBMIT",$cmd_submit);
		}
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this,'subscribe'));
		$this->tpl->parseCurrentBlock();
	}
	
	function cancel()
	{
		ilUtil::sendInfo($this->lng->txt("action_aborted"),true);
		//$this->ctrl->setParameterByClass("ilRepositoryGUI","ref_id",$this->getReturnRefId());
		//$this->ctrl->redirectByClass("ilRepositoryGUI","ShowList");
		ilUtil::redirect("repository.php?ref_id=".$this->getReturnRefId());
	}
	
	function subscribe()
	{
		if (!$this->object->addMember($this->ilias->account->getId(), $this->object->getDefaultMemberRole()))
		{
			$this->ilErr->raiseError($this->lng->txt("err_unknown_error"),$this->ilErr->MESSAGE);
		}
		
		$this->ilias->account->addDesktopItem($this->object->getRefId(),"grp");
		
		ilUtil::sendInfo($this->lng->txt("grp_registration_completed"),true);		
		$this->ctrl->returnToParent($this);
	}
	
	function apply()
	{
		global $ilDB;
		
		// @todo: move queries to app
		
		switch ($this->object->getRegistrationFlag())
		{
			// registration
			case 1:
				$q = "INSERT INTO grp_registration VALUES (".
					$ilDB->quote($this->object->getId()).",".
					$ilDB->quote($this->ilias->account->getId()).",".
					$ilDB->quote($_POST["subject"]).",".
					$ilDB->quote(date("Y-m-d H:i:s")).")";
				$this->ilias->db->query($q);

				ilUtil::sendInfo($this->lng->txt("application_completed"),true);
				ilUtil::redirect("repository.php?ref_id=".$this->getReturnRefId());
				break;

			// passwort
			case 2:
				if (strcmp($this->object->getPassword(),$_POST["subject"]) == 0 && $this->object->registrationPossible() == true)
				{
					$this->object->addMember($this->ilias->account->getId(),$this->object->getDefaultMemberRole());

					$this->ilias->account->addDesktopItem($this->object->getRefId(),"grp");
		
					ilUtil::sendInfo($this->lng->txt("grp_registration_completed"),true);
					$this->ctrl->returnToParent($this);
				}
				
				//wrong passwd
				ilUtil::sendInfo($this->lng->txt("err_wrong_password"),true);
				$this->ctrl->returnToParent($this);

				//$this->ilErr->raiseError($this->lng->txt("registration_not_possible"),$this->ilErr->MESSAGE);
				break;
				
			default:
				$this->ilErr->raiseError($this->lng->txt("err_unknown_error"),$this->ilErr->MESSAGE);
				break;
		}
	}

	function getReturnRefId()
	{
		if ($_SESSION["il_rep_ref_id"] == $this->object->getRefId())
		{
			return $this->tree->getParentId($this->object->getRefId());
		}
		else
		{
			return $_SESSION["il_rep_ref_id"];
		}	
	}
	
	function isUserAlreadyRegistered ()
	{
		global $ilDB;
		
		// @todo: move query to app
		
		$q = "SELECT * FROM grp_registration WHERE grp_id=".
			$ilDB->quote($this->object->getId())." AND user_id=".
			$ilDB->quote($this->ilias->account->getId());
		$res = $this->ilias->db->query($q);
	
		if ($res->numRows() > 0)
		{
			return true;	
		}
		
		return false;
	}
} // END class.ilRegisterGUI
?>