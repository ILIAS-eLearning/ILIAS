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
include_once('Services/Table/classes/class.ilTable2GUI.php');

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
		$_SESSION["mail_search_results_to"] = $_POST["search_name_to"];
		$_SESSION["mail_search_results_cc"] = $_POST["search_name_cc"];
		$_SESSION["mail_search_results_bcc"] = $_POST["search_name_bcc"];
		
		$this->saveMailData();
		
		$this->ctrl->returnToParent($this);
	}
	
	private function saveMailData()
	{
		$mail_data = $this->umail->getSavedData();
		
		$this->umail->savePostData(
			$mail_data["user_id"],
			$mail_data["attachments"],
			$mail_data["rcp_to"],
			$mail_data["rcp_cc"],
			$mail_data["rcp_bcc"],
			$mail_data["m_type"],
			$mail_data["m_email"],
			$mail_data["m_subject"],
			$mail_data["m_message"],
			$mail_data["use_placeholders"]
		);
	}
	
	public function cancel()
	{
		$this->ctrl->returnToParent($this);
	}
	
	function search()
	{
		$_SESSION["mail_search_search"] = $_POST["search"];
		$_SESSION["mail_search_type_system"] = $_POST["type_system"];
		$_SESSION["mail_search_type_addressbook"] = $_POST["type_addressbook"];

		// IF NO TYPE IS GIVEN SEARCH IN BOTH 'system' and 'addressbook'
		if(!$_SESSION["mail_search_type_system"] &&
		   !$_SESSION["mail_search_type_addressbook"])
		{
			$_SESSION["mail_search_type_system"] = 1;
			$_SESSION["mail_search_type_addressbook"] = 1;
		}
		if (strlen(trim($_SESSION["mail_search_search"])) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("mail_insert_query"));
		}
		else if(strlen(trim($_SESSION["mail_search_search"])) < 3)
		{
			$this->lng->loadLanguageModule('search');
			ilUtil::sendInfo($this->lng->txt('search_minimum_three'));
		}
		
		$this->showResults();
		
		return true;
	}

	public function showResults()
	{	
		global $rbacsystem, $lng, $ilUser;
		
		$this->saveMailData();

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_search.html", "Services/Mail");
		$this->tpl->setVariable("HEADER", $this->lng->txt("mail"));

		$this->tpl->setVariable("ACTION", $this->ctrl->getFormAction($this));
		$this->ctrl->clearParameters($this);
		
		$this->tpl->setVariable("TXT_SEARCH_FOR",$this->lng->txt("search_for"));
		$this->tpl->setVariable("TXT_SEARCH_SYSTEM",$this->lng->txt("mail_search_system"));
		$this->tpl->setVariable("TXT_SEARCH_ADDRESS",$this->lng->txt("mail_search_addressbook"));
		$this->tpl->setVariable("BUTTON_SEARCH",$this->lng->txt("search"));
		$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));
		
		if (strlen(trim($_SESSION["mail_search_search"])) > 0)
		{
			$this->tpl->setVariable("VALUE_SEARCH_FOR", ilUtil::prepareFormOutput(trim($_SESSION["mail_search_search"]), true));
		}
		
		if (!$_SESSION['mail_search_type_system'] && !$_SESSION['mail_search_type_addressbook'])
		{
			$this->tpl->setVariable('CHECKED_TYPE_SYSTEM', "checked=\"checked\"");
		}
		else
		{
			if ($_SESSION['mail_search_type_addressbook']) $this->tpl->setVariable('CHECKED_TYPE_ADDRESSBOOK', "checked=\"checked\"");
			if ($_SESSION['mail_search_type_system'])$this->tpl->setVariable('CHECKED_TYPE_SYSTEM', "checked=\"checked\"");
		}		

		if ($_SESSION['mail_search_type_addressbook'] && strlen(trim($_SESSION["mail_search_search"])) >= 3)
		{
			$abook = new ilAddressbook($ilUser->getId());
			$entries = $abook->searchUsers(addslashes(urldecode($_SESSION['mail_search_search'])));

			if (count($entries))
			{
				$tbl_addr = new ilTable2GUI($this);
				$tbl_addr->setTitle($lng->txt('mail_addressbook'));
				$tbl_addr->setRowTemplate('tpl.mail_search_addr_row.html', 'Services/Mail');				
				
				$result = array();
				$counter = 0;		
				foreach ($entries as $entry)
				{
					$result[$counter]['check']	= ilUtil::formCheckbox(0, 'search_name_to[]', ($entry['login'] ? $entry['login'] : $entry['email'])) . 
										          ilUtil::formCheckbox(0, 'search_name_cc[]', ($entry['login'] ? $entry['login'] : $entry['email'])) .
										          ilUtil::formCheckbox(0, 'search_name_bcc[]', ($entry['login'] ? $entry['login'] : $entry['email']));		
					$result[$counter]['login'] = $entry['login'];
					$result[$counter]['firstname'] = $entry['firstname'];
					$result[$counter]['lastname'] = $entry['lastname'];	
					
					$id = ilObjUser::_lookupId($entry['login']);						
					if (ilObjUser::_lookupPref($id, 'public_email') == 'y' || !$entry['login'])
					{
						$has_mail_addr = true;
						$result[$counter]['email'] = $entry['email'];
					}					
					
					++$counter;
				}							

				$tbl_addr->addColumn($this->lng->txt('mail_to') . '/' . $this->lng->txt('cc') . '/' . $this->lng->txt('bc'), 'check', '10%');
			 	$tbl_addr->addColumn($this->lng->txt('login'), 'login', "15%");
			 	$tbl_addr->addColumn($this->lng->txt('firstname'), 'firstname', "15%");
			 	$tbl_addr->addColumn($this->lng->txt('lastname'), 'lastname', "15%");
			 	if ($has_mail_addr)
			 	{
			 		foreach ($result as $key => $val)
			 		{
			 			if ($val['email'] == '') $result[$key]['email'] = '&nbsp;';
			 		}
			 		
			 		$tbl_addr->addColumn($this->lng->txt('email'), 'email', "15%");
			 	}
			 	$tbl_addr->setData($result);

			 	$tbl_addr->setDefaultOrderField('login');							
				$tbl_addr->setPrefix('addr_');			
				$tbl_addr->enable('select_all');				
				$tbl_addr->setSelectAllCheckbox('search_name_to');					

				$this->tpl->setVariable('TABLE_ADDR', $tbl_addr->getHTML());				
			}
		}		
		if ($_SESSION['mail_search_type_system'] && strlen(trim($_SESSION["mail_search_search"])) >= 3)
		{
			include_once 'Services/Search/classes/class.ilQueryParser.php';
			include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
			include_once 'Services/Search/classes/class.ilSearchResult.php';
		
			$all_results = new ilSearchResult();
		
			$query_parser = new ilQueryParser(ilUtil::stripSlashes($_SESSION['mail_search_search']));
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
			
			$users = $all_results->getResults();
			if (count($users))
			{
				$tbl_users = new ilTable2GUI($this);
				$tbl_users->setTitle($lng->txt('system').': '.$lng->txt('persons'));
				$tbl_users->setRowTemplate('tpl.mail_search_users_row.html','Services/Mail');
				
				$result = array();				
				$counter = 0;				
				foreach ($users as $user)
				{					
					$login = ilObjUser::_lookupLogin($user['obj_id']);			

					$result[$counter]['check']	= ilUtil::formCheckbox(0, 'search_name_to[]', $login) . 
							  					  ilUtil::formCheckbox(0, 'search_name_cc[]', $login) .
												  ilUtil::formCheckbox(0, 'search_name_bcc[]', $login);		
					$result[$counter]['login'] = $login;
					
					if (ilObjUser::_lookupPref($user['obj_id'], 'public_profile') == 'y')
					{
						$name = ilObjUser::_lookupName($user['obj_id']);
						$result[$counter]['firstname'] = $name['firstname'];
						$result[$counter]['lastname'] = $name['lastname'];
					}
					else
					{
						$result[$counter]['firstname'] = '';
						$result[$counter]['lastname'] = '';
					}
					
					if (ilObjUser::_lookupPref($user['obj_id'], 'public_email') == 'y')
					{
						$has_mail_usr = true;
						$result[$counter]['email'] = ilObjUser::_lookupEmail($user['obj_id']);
					}
						
					++$counter;
				}							
				
				$tbl_users->addColumn($this->lng->txt('mail_to') . '/' . $this->lng->txt('cc') . '/' . $this->lng->txt('bc'), 'check', '10%');
			 	$tbl_users->addColumn($this->lng->txt('login'), 'login', '15%');
			 	$tbl_users->addColumn($this->lng->txt('firstname'), 'firstname', '15%');
			 	$tbl_users->addColumn($this->lng->txt('lastname'), 'lastname', '15%');
			 	if ($has_mail_usr == true)
			 	{
			 		foreach ($result as $key => $val)
			 		{
			 			if ($val['email'] == '') $result[$key]['email'] = '&nbsp;';
			 		}
			 		
			 		$tbl_users->addColumn($this->lng->txt('email'), 'email', '15%');
			 	}
			 	$tbl_users->setData($result);

			 	$tbl_users->setDefaultOrderField('login');						
				$tbl_users->setPrefix('usr_');			
				$tbl_users->enable('select_all');				
				$tbl_users->setSelectAllCheckbox('search_name_to');				
	
				$this->tpl->setVariable('TABLE_USERS', $tbl_users->getHTML());
			}			
		
			$groups = ilUtil::searchGroups(addslashes(urldecode($_SESSION['mail_search_search'])));
			if (count($groups))
			{					
				$tbl_grp = new ilTable2GUI($this);
				$tbl_grp->setTitle($lng->txt('system').': '.$lng->txt('groups'));
				$tbl_grp->setRowTemplate('tpl.mail_search_groups_row.html','Services/Mail');
				
				$result = array();				
				$counter = 0;
				foreach ($groups as $grp)
				{	
					$result[$counter]['check']	= ilUtil::formCheckbox(0, 'search_name_to[]', '#'.$grp['title']) . 
							  					  ilUtil::formCheckbox(0, 'search_name_cc[]', '#'.$grp['title']) .
												  ilUtil::formCheckbox(0, 'search_name_bcc[]', '#'.$grp['title']);		
					$result[$counter]['title'] = $grp['title'];
					$result[$counter]['description'] = $grp['description'];
											
					++$counter;
				}
				$tbl_grp->setData($result);			

				$tbl_grp->addColumn($this->lng->txt('mail_to') . '/' . $this->lng->txt('cc') . '/' . $this->lng->txt('bc'), 'check', '10%');
			 	$tbl_grp->addColumn($this->lng->txt('title'), 'title', '15%');
			 	$tbl_grp->addColumn($this->lng->txt('description'), 'description', '15%');

			 	$tbl_grp->setDefaultOrderField('title');							
				$tbl_grp->setPrefix('grp_');			
				$tbl_grp->enable('select_all');				
				$tbl_grp->setSelectAllCheckbox('search_name_to');				
	
				$this->tpl->setVariable('TABLE_GRP', $tbl_grp->getHTML());
			}
		}
		
		if (count($users) || count($groups) || count($entries))
		{
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setVariable("ALT_ARROW", '');
			$this->tpl->setVariable('BUTTON_ADOPT', $this->lng->txt('adopt'));	
		}
		else if (strlen(trim($_SESSION["mail_search_search"])) >= 3)
		{
			$this->lng->loadLanguageModule('search');			
			ilUtil::sendInfo($this->lng->txt('search_no_match'));
		}		
		
		$this->tpl->show();
	}
}
?>