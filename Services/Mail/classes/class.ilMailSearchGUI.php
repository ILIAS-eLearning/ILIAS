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

require_once "classes/class.ilObjUser.php";
require_once "Services/Mail/classes/class.ilMailbox.php";
require_once "Services/Mail/classes/class.ilFormatMail.php";
require_once "Services/Mail/classes/class.ilAddressbook.php";

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
*/
class ilMailSearchGUI
{
	private $tpl = null;
	private $ctrl = null;
	private $lng = null;
	
	private $umail = null;

	private $errorDelete = false;

	public function __construct()
	{
		global $tpl, $ilCtrl, $lng, $ilUser;

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		
		$this->ctrl->saveParameter($this, "mobj_id");

		$this->umail = new ilFormatMail($ilUser->getId());
	}

	public function executeCommand()
	{
		$forward_class = $this->ctrl->getNextClass($this);
		switch($forward_class)
		{
			default:
				if (!($cmd = $this->ctrl->getCmd()))
				{
					$cmd = "showResults";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	public function adopt()
	{
		$_SESSION["mail_search_results"] = $_POST["search_name"];
		$this->ctrl->returnToParent($this);
	}
	
	public function cancel()
	{
		$this->ctrl->returnToParent($this);
	}

	public function showResults()
	{
		global $rbacsystem, $lng, $ilUser;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_search.html", "Services/Mail");
		$this->tpl->setVariable("HEADER", $this->lng->txt("mail"));

		$this->ctrl->setParameter($this, "cmd", "post");
		$this->tpl->setVariable("ACTION", $this->ctrl->getLinkTarget($this));
		$this->ctrl->clearParameters($this);

		if ($_GET["addressbook"])
		{
			$this->tpl->setCurrentBlock("addr");
			$abook = new ilAddressbook($ilUser->getId());
			$entries = $abook->searchUsers(addslashes(urldecode($_GET["search"])));
		
			if ($entries)
			{
				$counter = 0;
				$this->tpl->setCurrentBlock("addr_search");
		
				foreach ($entries as $entry)
				{
					$this->tpl->setVariable("ADDR_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
					$this->tpl->setVariable("ADDR_LOGIN_A",$entry["login"]);
					$this->tpl->setVariable("ADDR_LOGIN_B",$entry["login"]);
					$this->tpl->setVariable("ADDR_FIRSTNAME",$entry["firstname"]);
					$this->tpl->setVariable("ADDR_LASTNAME",$entry["lastname"]);
					$this->tpl->setVariable("ADDR_EMAIL_A",$entry["email"]);
					$this->tpl->setVariable("ADDR_EMAIL_B",$entry["email"]);
					$this->tpl->parseCurrentBlock();
				}		
				
				$this->tpl->setVariable("TXT_ADDR_LOGIN",$lng->txt("login"));
				$this->tpl->setVariable("TXT_ADDR_FIRSTNAME",$lng->txt("firstname"));
				$this->tpl->setVariable("TXT_ADDR_LASTNAME",$lng->txt("lastname"));
				$this->tpl->setVariable("TXT_ADDR_EMAIL",$lng->txt("email"));
			}
			else
			{
				$this->tpl->setCurrentBlock("addr_no_content");
				$this->tpl->setVariable("TXT_ADDR_NO",$lng->txt("mail_search_no"));
				$this->tpl->parseCurrentBlock();
			}
			
			// SET TXT VARIABLES ADDRESSBOOK
			$this->tpl->setVariable("TXT_ADDR",$lng->txt("mail_addressbook"));
			if (count($entries)) $this->tpl->setVariable("BUTTON_ADOPT",$lng->txt("adopt"));
			$this->tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
			$this->tpl->parseCurrentBlock();
		}
		
		if ($_GET["system"])
		{
			include_once 'Services/Search/classes/class.ilQueryParser.php';
			include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
			include_once 'Services/Search/classes/class.ilSearchResult.php';
		
			$all_results = new ilSearchResult();
		
			$query_parser = new ilQueryParser(ilUtil::stripSlashes($_GET['search']));
			$query_parser->setCombination(QP_COMBINATION_OR);
			$query_parser->setMinWordLength(3);
			$query_parser->parse();
		
			$user_search =& ilObjectSearchFactory::_getUserSearchInstance($query_parser);
			$user_search->enableActiveCheck(true);
			$user_search->setFields(array('login'));
			$result_obj = $user_search->performSearch();
			$all_results->mergeEntries($result_obj);
		
			$user_search->setFields(array('firstname'));
			$result_obj = $user_search->performSearch();
			$all_results->mergeEntries($result_obj);
		
			
			$user_search->setFields(array('lastname'));
			$result_obj = $user_search->performSearch();
			$all_results->mergeEntries($result_obj);
		
			$all_results->filter(ROOT_FOLDER_ID,QP_COMBINATION_OR);
		
			$counter = 0;
			foreach(($users = $all_results->getResults()) as $result)
			{
				global $rbacsystem;
		
#				if($rbacsystem->checkAccess("smtp_mail",$this->umail->getMailObjectReferenceId()) 
#				   and (ilObjUser::_lookupPref($result['obj_id'],'public_email') == 'y'))
			   if (ilObjUser::_lookupPref($result['obj_id'],'public_email') == 'y')
				{
					$has_mail = true;
					$this->tpl->setCurrentBlock("smtp_row");
					$this->tpl->setVariable("PERSON_EMAIL_A",ilObjUser::_lookupEmail($result['obj_id']));
					$this->tpl->setVariable("PERSON_EMAIL_B",ilObjUser::_lookupEmail($result['obj_id']));
					$this->tpl->parseCurrentBlock();
				}
				else
				{
					$this->tpl->setCurrentBlock("no_smtp_row");
					$this->tpl->setVariable("PERSON_NO_EMAIL",'');
					$this->tpl->parseCurrentBlock();
				}
				
				$name = ilObjUser::_lookupName($result['obj_id']);
				$login = ilObjUser::_lookupLogin($result['obj_id']);
		
				$this->tpl->setCurrentBlock("person_search");
				$this->tpl->setVariable("CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
				$this->tpl->setVariable("PERSON_LOGIN_A",$login);
				$this->tpl->setVariable("PERSON_LOGIN_B",$login);
				$this->tpl->setVariable("PERSON_FIRSTNAME",$name["firstname"]);
				$this->tpl->setVariable("PERSON_LASTNAME",$name["lastname"]);
				$this->tpl->parseCurrentBlock();
			}		
		
			if (!count($users))
			{
				$this->tpl->setCurrentBlock("no_system_content");
				$this->tpl->setVariable("TXT_SYSTEM_NO",$lng->txt("mail_search_no"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setVariable("TXT_SYSTEM_LOGIN",$lng->txt("login"));
				$this->tpl->setVariable("TXT_SYSTEM_FIRSTNAME",$lng->txt("firstname"));
				$this->tpl->setVariable("TXT_SYSTEM_LASTNAME",$lng->txt("lastname"));
				if ($has_mail)
				{
					$this->tpl->setCurrentBlock("smtp");
					$this->tpl->setVariable("TXT_SYSTEM_EMAIL",$lng->txt("email"));
					$this->tpl->parseCurrentBlock();
				}
				else
				{
					$this->tpl->touchBlock('no_smtp');
				}
			}
		
			$groups = ilUtil::searchGroups(addslashes(urldecode($_GET["search"])));
			if (count($groups))
			{
				$counter = 0;
				$this->tpl->setCurrentBlock("group_search");
		
				foreach ($groups as $group_data)
				{
					$this->tpl->setVariable("GROUP_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
					$this->tpl->setVariable("GROUP_NAME","#".$group_data["title"]);
					$this->tpl->setVariable("GROUP_TITLE",$group_data["title"]);
					$this->tpl->setVariable("GROUP_DESC",$group_data["description"]);
					$this->tpl->parseCurrentBlock();
				}
			}

			if (!count($groups))
			{
				$this->tpl->setCurrentBlock("no_groups_content");
				$this->tpl->setVariable("TXT_GROUPS_NO",$lng->txt("mail_search_no"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setVariable("TXT_GROUP_TITLE",$lng->txt("title"));
				$this->tpl->setVariable("TXT_GROUP_DESC",$lng->txt("description"));
			}
		
			$this->tpl->setCurrentBlock("system");
			$this->tpl->setVariable("TXT_SYSTEM_PERSONS",$lng->txt("system").": ".$lng->txt("persons"));
			$this->tpl->setVariable("TXT_SYSTEM_GROUPS",$lng->txt("system").": ".$lng->txt("groups"));
			if (count($users) || count($groups)) $this->tpl->setVariable("BUTTON_ADOPT",$lng->txt("adopt"));
			$this->tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
			$this->tpl->parseCurrentBlock();
		} 		
		
		$this->tpl->show();
	}

}

?>
