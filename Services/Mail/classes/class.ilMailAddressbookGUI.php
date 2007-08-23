<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once './Services/User/classes/class.ilObjUser.php';
require_once "Services/Mail/classes/class.ilMailbox.php";
require_once "Services/Mail/classes/class.ilFormatMail.php";
require_once "Services/Mail/classes/class.ilAddressbook.php";

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
* @ilCtrl_Calls ilMailAddressbookGUI: ilMailSearchCoursesGUI, ilMailSearchGroupsGUI
*/
class ilMailAddressbookGUI
{
	private $tpl = null;
	private $ctrl = null;
	private $lng = null;
	private $tabs_gui = null;
	
	private $umail = null;
	private $abook = null;

	public function __construct()
	{
		global $tpl, $ilCtrl, $lng, $ilUser, $ilTabs;

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tabs_gui =& $ilTabs;
		
		$this->ctrl->saveParameter($this, "mobj_id");

		$this->umail = new ilFormatMail($ilUser->getId());
		$this->abook = new ilAddressbook($ilUser->getId());
	}

	public function executeCommand()
	{
		$this->showSubTabs();

		$forward_class = $this->ctrl->getNextClass($this);
		switch($forward_class)
		{
			case 'ilmailformgui':
				include_once 'Services/Mail/classes/class.ilMailFormGUI.php';

				$this->ctrl->forwardCommand(new ilMailFormGUI());
				break;

			case 'ilmailsearchcoursesgui':
				include_once 'Services/Mail/classes/class.ilMailSearchCoursesGUI.php';

				$this->tabs_gui->setSubTabActive('mail_my_courses');

				$this->ctrl->setReturn($this, "showAddressbook");
				$this->ctrl->forwardCommand(new ilMailSearchCoursesGUI());
				break;

			case 'ilmailsearchgroupsgui':
				include_once 'Services/Mail/classes/class.ilMailSearchGroupsGUI.php';

				$this->tabs_gui->setSubTabActive('mail_my_groups');

				$this->ctrl->setReturn($this, "showAddressbook");
				$this->ctrl->forwardCommand(new ilMailSearchGroupsGUI());
				break;

			default:
				$this->tabs_gui->setSubTabActive('mail_my_entries');

				if (!($cmd = $this->ctrl->getCmd()))
				{
					$cmd = "showAddressbook";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Check user's input
	 */
	function checkInput($confirm = false)
	{
			// check if user login and e-mail-address are empty 
			if (!strcmp(trim($_POST["login"]),"") &&
				!strcmp(trim($_POST["email"]),""))
			{
				ilUtil::sendInfo($this->lng->txt("mail_enter_login_or_email_addr"));
				$this->error = true;
			}
			else if ($_POST["login"] != "" && 
					 !(ilObjUser::_lookupId($_POST["login"])))
			{
				ilUtil::sendInfo($this->lng->txt("mail_enter_valid_login"));
				$this->error = true;
			}
			else if ($_POST["email"] &&
					 !(ilUtil::is_email($_POST["email"])))
			{
				ilUtil::sendInfo($this->lng->txt("mail_enter_valid_email_addr"));
				$this->error = true;
			}

			if ($confirm == false)
			{
				if (($this->existingEntry = $this->abook->checkEntry($_POST["login"])) > 0 &&
					$this->existingEntry != $_POST["entry_id"][0])
				{
					ilUtil::sendInfo($this->lng->txt("mail_entry_exists"));
					$this->error = true;
				}
			}

			return $this->error ? false : true; 
	}

	/**
	 * Change entry
	 */
	function change()
	{
		global $lng;

		if(trim($_POST["entry_id"][0]) == "")
		{
			ilUtil::sendInfo($lng->txt("mail_select_one"));
		}
		else if ($this->checkInput())
		{
			$this->abook->updateEntry($_POST["entry_id"][0],
								$_POST["login"],
								$_POST["firstname"],
								$_POST["lastname"],
								$_POST["email"]);
			ilUtil::sendInfo($lng->txt("mail_entry_changed"));

			unset($_POST["entry_id"][0]);
			unset($this->existingEntry);
		}

		$this->showAddressbook();
	}	

	/**
	 * Add new entry
	 */
	public function add()
	{	
		global $lng;

		if ($this->checkInput())
		{
			$this->abook->addEntry($_POST["login"],
						 $_POST["firstname"],
						 $_POST["lastname"],
						 $_POST["email"]);
			ilUtil::sendInfo($lng->txt("mail_entry_added"));

			unset($_POST["entry_id"]);
			unset($this->existingEntry);

			$_GET["offset"] = 0;
		}
	
	$this->showAddressbook();
	}
	
	/**
	 * Do not overwrite existing entry
	 */
	function cancelOverwrite()
	{
		unset($_POST["action"]);
		unset($_POST["entry_id"]);
		$this->showAddressbook();
	}
	
	/**
	 * Overwrite existing entry
	 */
	function confirmOverwrite()
	{
		global $lng;

		if(!is_array($_POST["entry_id"]))
		{
			ilUtil::sendInfo($lng->txt("mail_select_one"));
		}
		else if ($this->checkInput(true))
		{
			$this->abook->updateEntry($_POST["entry_id"][0],
								$_POST["login"],
								$_POST["firstname"],
								$_POST["lastname"],
								$_POST["email"]);
			ilUtil::sendInfo($lng->txt("mail_entry_changed"));

			unset($_POST["entry_id"]);
			unset($_POST["action"]);
			unset($this->existingEntry);
		}
		
		$this->showAddressbook();
	}	
	
	/**
	 * Do not delete entry
	 */
	function cancelDelete()
	{
		unset($_POST["action"]);
		unset($_POST["entry_id"]);
		$this->showAddressbook();
	}
	
	/**
	 * Delete entry
	 */
	function confirmDelete()
	{
		global $lng;

		if(!is_array($_POST["entry_id"]))
		{
			ilUtil::sendInfo($lng->txt("mail_select_one_entry"));
		}
		else if($this->abook->deleteEntries($_POST["entry_id"]))
		{
			$_GET["offset"] = 0;
			ilUtil::sendInfo($lng->txt("mail_deleted_entry"));
		}
		else
		{
			ilUtil::sendInfo($lng->txt("mail_delete_error"));
		}
		
		$_GET["offset"] = 0;

		unset($_POST["action"]);
		unset($_POST["entry_id"]);
		$this->showAddressbook();
	}

	/**
	 * Cancel action
	 */
	function cancel()
	{
		$this->showAddressbook();
	}
	
	/**
	 * Show user's addressbook
	 */
	public function showAddressbook()
	{
		global $rbacsystem, $lng, $ilUser;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_addressbook.html", "Services/Mail");
		$this->tpl->setVariable("HEADER", $this->lng->txt("mail"));

		if ($_POST["cmd"]["showAddressbook"])
		{
			switch($_POST["action"])
			{
				case 'edit':
					if(!is_array($_POST["entry_id"]))
					{
						unset($_POST["action"]);
						ilUtil::sendInfo($lng->txt("mail_select_one_entry"));
					}
					else if(count($_POST["entry_id"]) > 1)
					{
						unset($_POST["action"]);
						ilUtil::sendInfo($lng->txt("mail_select_only_one_entry"));
					}
					else
					{
						$tmp_abook = new ilAddressbook($_SESSION["AccountId"]);
						$data = $tmp_abook->getEntry($_POST["entry_id"][0]);
					}
					break;
				case 'delete':
					if(!is_array($_POST["entry_id"]))
					{
						ilUtil::sendInfo($lng->txt("mail_select_one_entry"));
						$this->errorDelete = true;
					}
					else
					{
						ilUtil::sendInfo($lng->txt("mail_sure_delete_entry"));
					}
			}
		}

		if ($_GET["offset"] == "") $_GET["offset"] = 0;

		$this->ctrl->setParameter($this, "cmd", "post");
		$this->ctrl->setParameter($this, "offset", $_GET["offset"]);
		$this->tpl->setVariable("ACTION", $this->ctrl->getLinkTarget($this));
		$this->ctrl->clearParameters($this);

		$this->tpl->setVariable("TXT_ENTRIES",$lng->txt("mail_addr_entries"));
		
		// CASE ERROR OCCURED
		if ((isset($_POST["cmd"]["add"]) ||
			 isset($_POST["cmd"]["confirmOverwrite"]) ||
			 isset($_POST["cmd"]["change"])) &&
			$this->error)
		{
			$data["login"] = $_POST["login"];
			$data["firstname"] = $_POST["firstname"];
			$data["lastname"] = $_POST["lastname"];
			$data["email"] = $_POST["email"];
		}

		// CASE ENTRY EXISTS
		if ((isset($_POST["cmd"]["add"]) ||
			 isset($_POST["cmd"]["confirmOverwrite"])) &&
			$this->existingEntry > 0)
		{
			$this->tpl->setCurrentBlock("entry_exists");
			$this->tpl->setVariable("ENTRY_EXISTS_ENTRY_ID",$this->existingEntry);
			$this->tpl->setVariable("ENTRY_EXISTS_BUTTON_OVERWRITE",$lng->txt("overwrite"));
			$this->tpl->setVariable("ENTRY_EXISTS_BUTTON_CANCEL",$lng->txt("cancel"));
			$this->tpl->parseCurrentBlock();
		}
		
		// CASE CONFIRM DELETE
		if($_POST["action"] == "delete" and !$this->errorDelete and !isset($_POST["cmd"]["confirmDelete"]))
		{
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("BUTTON_CONFIRM",$lng->txt("confirm"));
			$this->tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
			$this->tpl->parseCurrentBlock();
		}
		
		// SET TXT VARIABLES ADDRESSBOOK
		$this->tpl->setVariable("TXT_LOGIN",$lng->txt("username"));
		$this->tpl->setVariable("TXT_FIRSTNAME",$lng->txt("firstname"));
		$this->tpl->setVariable("TXT_LASTNAME",$lng->txt("lastname"));
		$this->tpl->setVariable("TXT_EMAIL",$lng->txt("email"));

		$entries = $this->abook->getEntries();
		$entries_count = count($entries);
		
		// TODO: READ FROM MAIL_OPTIONS
		$entries_max_hits = $ilUser->getPref('hits_per_page');
		
		// SHOW ENTRIES
		if($entries)
		{
			// LINKBAR
			if($entries_count > $entries_max_hits)
			{
				$params = array(
					"mobj_id"		=> $_GET["mobj_id"]);
			}
			$start = $_GET["offset"];
			$linkbar = ilUtil::Linkbar($this->ctrl->getLinkTarget($this),$entries_count,$entries_max_hits,$start,$params);
			if ($linkbar)
			{
				$this->tpl->setVariable("LINKBAR", $linkbar);
			}
			// END LINKBAR

			$counter = 0;
			foreach($entries as $entry)
			{
				if ($counter >= $start &&
					$counter < ($start + $entries_max_hits))
				{
					if($rbacsystem->checkAccess("smtp_mail",$this->umail->getMailObjectReferenceId()))
					{
						$this->tpl->setCurrentBlock("smtp");
						$this->tpl->setVariable("EMAIL_SMTP",$entry["email"]);
						$this->ctrl->setParameterByClass("ilmailformgui", "type", "address");
						$this->ctrl->setParameterByClass("ilmailformgui", "rcp", urlencode($entry["email"]));
						$this->tpl->setVariable("EMAIL_LINK", $this->ctrl->getLinkTargetByClass("ilmailformgui"));
						$this->ctrl->clearParametersByClass("ilmailformgui");
						$this->tpl->parseCurrentBlock();
					}
					else
					{
						$this->tpl->setCurrentBlock("no_smtp");
						$this->tpl->setVariable("EMAIL",$entry["email"]);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("addr_search");
			
					$this->tpl->setVariable("CSSROW", ilUtil::switchColor(++$couter,'tblrow1', 'tblrow2'));		
					if(is_array($_POST["entry_id"]))
					{
						$this->tpl->setVariable("CHECKED",in_array($entry["addr_id"],$_POST["entry_id"]) ? "checked='checked'" : "");
					}
					$this->tpl->setVariable("ENTRY_ID",$entry["addr_id"]);
					if ($entry["login"] != "")
					{
						$this->ctrl->setParameterByClass("ilmailformgui", "type", "address");
						$this->ctrl->setParameterByClass("ilmailformgui", "rcp", urlencode($entry["login"]));
						$this->tpl->setVariable("LOGIN_LINK", $this->ctrl->getLinkTargetByClass("ilmailformgui"));
						$this->ctrl->clearParametersByClass("ilmailformgui");
						$this->tpl->setVariable("LOGIN",$entry["login"]);
					}
					$this->tpl->setVariable("FIRSTNAME",$entry["firstname"]);
					$this->tpl->setVariable("LASTNAME",$entry["lastname"]);
					$this->tpl->parseCurrentBlock();
				}
				$counter++;
			}
			
			$this->tpl->setVariable("SELECT_ALL",$lng->txt('select_all'));	
			$this->tpl->setVariable("ROWCLASS", ilUtil::switchColor(++$couter,'tblrow1', 'tblrow2'));

			$this->tpl->setVariable("BUTTON_SUBMIT",$lng->txt("submit"));
			
			// ACTIONS
			$this->tpl->setCurrentBlock("actions");
			$this->tpl->setVariable("ACTION_NAME","edit");
			$this->tpl->setVariable("ACTION_VALUE",$lng->txt("edit"));
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setVariable("ACTION_NAME","delete");
			$this->tpl->setVariable("ACTION_VALUE",$lng->txt("delete"));
			$this->tpl->setVariable("ACTION_SELECTED",$_POST["action"] == 'delete' ? 'selected' : '');
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("addr_no_content");
			$this->tpl->setVariable("TXT_ADDR_NO",$lng->txt("mail_search_no"));
			$this->tpl->parseCurrentBlock();
		}

		// SHOW FORM
		$this->tpl->setVariable("CSSROW_LOGIN",'tblrow1');
		$this->tpl->setVariable("HEADER_LOGIN",$lng->txt("username"));
		$this->tpl->setVariable("VALUE_LOGIN",$data["login"]);
		$this->tpl->setVariable("CSSROW_FIRSTNAME",'tblrow2');
		$this->tpl->setVariable("HEADER_FIRSTNAME",$lng->txt("firstname"));
		$this->tpl->setVariable("VALUE_FIRSTNAME",$data["firstname"]);
		$this->tpl->setVariable("CSSROW_LASTNAME",'tblrow1');
		$this->tpl->setVariable("HEADER_LASTNAME",$lng->txt("lastname"));
		$this->tpl->setVariable("VALUE_LASTNAME",$data["lastname"]);
		$this->tpl->setVariable("CSSROW_EMAIL",'tblrow2');
		$this->tpl->setVariable("HEADER_EMAIL",$lng->txt("email"));
		$this->tpl->setVariable("VALUE_EMAIL",$data["email"]);

		// SUBMIT VALUE DEPENDS ON $_POST["cmd"]
	
		$this->tpl->setVariable("TXT_NEW_EDIT",$_POST["entry_id"][0] != "" && ($_POST["action"] == "edit" || isset($_POST["cmd"]["change"])) ? $lng->txt("mail_edit_entry") : $lng->txt("mail_new_entry"));
		$this->tpl->setVariable("BUTTON_EDIT_ADD",$_POST["entry_id"][0] != "" && ($_POST["action"] == "edit" || isset($_POST["cmd"]["change"]))  ? $lng->txt("change") : $lng->txt("add"));
		$this->tpl->setVariable("BUTTON_EDIT_ADD_NAME",$_POST["entry_id"][0] != "" && ($_POST["action"] == "edit" || isset($_POST["cmd"]["change"]))  ? "cmd[change]" : "cmd[add]");
		
/*		$this->ctrl->setParameter($this, "cmd", "showMyCourses");
		$this->ctrl->setParameter($this, "view", "mycourses");
		$this->tpl->setVariable("LINK_MYCOURSES", $this->ctrl->getLinkTarget($this));
		$this->ctrl->clearParameters($this);
		$this->tpl->setVariable("TXT_MYCOURSES", $lng->txt("my_courses"));

		$this->ctrl->setParameter($this, "cmd", "showMyGroups");
		$this->ctrl->setParameter($this, "view", "mygroups");
		$this->tpl->setVariable("LINK_MYGROUPS", $this->ctrl->getLinkTarget($this));
		$this->ctrl->clearParameters($this);
		$this->tpl->setVariable("TXT_MYGROUPS", $lng->txt("my_grps"));*/

		$this->tpl->show();
	}

	function showSubTabs()
	{
		$this->tabs_gui->addSubTabTarget('mail_my_entries',
										 $this->ctrl->getLinkTarget($this));
		$this->tabs_gui->addSubTabTarget('mail_my_courses',
										 $this->ctrl->getLinkTargetByClass('ilmailsearchcoursesgui'));
		$this->tabs_gui->addSubTabTarget('mail_my_groups',
										 $this->ctrl->getLinkTargetByClass('ilmailsearchgroupsgui'));
	}

}

?>
