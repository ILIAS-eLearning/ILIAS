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
* @package ilias-core
*/
class ilRegisterGUI
{
	var $lng;
	var $ilias;
	var $tpl;
	var $tree;
	var $rbacsystem;
	var $cur_ref_id;
	var $cmd;
	var $mode;
	var $ctrl;

	/**
	* Constructor
	* @access	public
	*/
	function ilRegisterGUI()
	{
		global $lng, $ilias, $tpl, $tree, $objDefinition, $ilCtrl;

		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->tree =& $tree;
		$this->objDefinition =& $objDefinition;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id"));

		// get object of current ref id
		$this->object =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
	}

	
	function prepareOutput()
	{
		// output objects
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.repository.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output locator
		$this->setLocator();

		// output message
		if($this->message)
		{
			sendInfo($this->message);
		}

		// display infopanel if something happened
		infoPanel();

		// set header
		$this->setHeader();
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		
		if (empty($cmd))
		{
			$cmd = "checkStatus";
		}
//vd($cmd);exit;
		//$this->prepareOutput();
		$this->cmd = $cmd;
		$this->$cmd();	
	}
	
	function checkStatus()
	{
		$owner = new ilObjUser($this->object->getOwner());

		$_SESSION["saved_post"]["user_id"][0] = $this->ilias->account->getId();
		$_SESSION["status"] 	= $this->object->getDefaultMemberRole();

		switch ($this->object->getRegistrationFlag())
		{
			case 0:
				$stat = $this->lng->txt("group_no_registration");
				$msg  = $this->lng->txt("group_no_registration_msg");
				$readonly ="readonly";
				$subject ="";
				$cmd_submit = "subscribe";
				break;

			case 1:
				$stat = $this->lng->txt("group_req_registration");
				$msg  = $this->lng->txt("group_req_registration_msg");
				$cmd_submit = "apply";
				$txt_subject =$this->lng->txt("subject").":";
				$textfield = "<textarea name=\"subject\" value=\"{SUBJECT}\" cols=\"50\" rows=\"5\" size=\"255\"></textarea>";
				break;

			case 2:
				if ($this->object->registrationPossible() == true)
				{
					$stat = $this->lng->txt("group_req_password");//"Registrierungpasswort erforderlich";
					$msg = $this->lng->txt("group_password_registration_msg");
					$txt_subject =$this->lng->txt("password").":";
					$textfield = "<input name=\"subject\" value=\"{SUBJECT}\" type=\"password\" size=\"40\" maxlength=\"70\" style=\"width:300px;\"/>";
					$cmd_submit = "apply";
				}
				else
				{
					$msg = $this->lng->txt("group_password_registration_expired_msg");
					$msg_send = "mail_new.php?mobj_id=3&type=new&mail_data[rcp_to]=root";
					$cmd_submit = "cancel";
					$readonly ="readonly";
					$stat = $this->lng->txt("group_registration_expired");
					sendInfo($this->lng->txt("registration_expired"),true);
				}
				break;
		}


		$this->tpl->setVariable("HEADER",  $this->lng->txt("group_access"));
		$this->tpl->addBlockFile("CONTENT", "tbldesc", "tpl.grp_accessdenied.html");
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("group_access_denied"));
		$this->tpl->setVariable("TXT_MESSAGE",$msg);
		$this->tpl->setVariable("TXT_GRP_NAME", $this->lng->txt("group_name").":");
		$this->tpl->setVariable("GRP_NAME",$this->object->getTitle());
		$this->tpl->setVariable("TXT_GRP_DESC",$this->lng->txt("group_desc").":");
		$this->tpl->setVariable("GRP_DESC",$this->object->getDescription());
		$this->tpl->setVariable("TXT_GRP_OWNER",$this->lng->txt("owner").":");
		$this->tpl->setVariable("GRP_OWNER",$owner->getLogin());
		$this->tpl->setVariable("TXT_GRP_STATUS",$this->lng->txt("group_status").":");
		$this->tpl->setVariable("GRP_STATUS", $stat);
		$this->tpl->setVariable("TXT_SUBJECT",$txt_subject);
		$this->tpl->setVariable("SUBJECT",$textfield);
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("grp_register"));
		$this->tpl->setVariable("CMD_CANCEL","cancel");
		$this->tpl->setVariable("CMD_SUBMIT",$cmd_submit);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getLinkTarget($this,"post")."&user_id=".$this->ilias->account->getId());
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}
	
	function cancel()
	{
		sendInfo($this->lng->txt("action_aborted"),true);
		ilUtil::redirect("repository.php?ref_id=".$this->getReturnRefId());
	}
	
	function subscribe()
	{
		if ($this->object->addMember($this->ilias->account->getId(), $this->object->getDefaultMemberRole()))
		{
			sendInfo($this->lng->txt("grp_registration_completed"),true);
		}
		
		ilUtil::redirect("repository.php?ref_id=".$this->object->getRefId());
	}
	
	function apply()
	{
		if ($this->object->getRegistrationFlag() == 1)
		{
			$q = "SELECT * FROM grp_registration WHERE grp_id=".$this->object->getId()." AND user_id=".$this->ilias->account->getId();
			$res = $this->ilias->db->query($q);

			if ($res->numRows() > 0)
			{
				sendInfo($this->lng->txt("already_applied"),true);
			}
			else
			{
				$q = "INSERT INTO grp_registration VALUES (".$this->object->getId().",".$this->ilias->account->getId().",'".$_POST["subject"]."','".date("Y-m-d H:i:s")."')";
				$res = $this->ilias->db->query($q);
				sendInfo($this->lng->txt("application_completed"),true);
			}
		}
		else if ($this->object->getRegistrationFlag() == 2)	//PASSWORD REGISTRATION
		{
			if (strcmp($this->object->getPassword(),$_POST["subject"]) == 0 && $this->object->registrationPossible() == true)
			{
				$this->object->addMember($this->ilias->account->getId(),$this->object->getDefaultMemberRole());
				sendInfo($this->lng->txt("grp_registration_completed"),true);
			}
			else if (strcmp($this->object->getPassword(),$_POST["subject"]) != 0 && $this->object->registrationPossible() == true)
			{
				sendInfo($this->lng->txt("err_wrong_password"),true);
			}
			else
				sendInfo($this->lng->txt("registration_not_possible"),true);
			
		}

		ilUtil::redirect("repository.php?ref_id=".$this->getReturnRefId());
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

} // END class ilRepository

?>
