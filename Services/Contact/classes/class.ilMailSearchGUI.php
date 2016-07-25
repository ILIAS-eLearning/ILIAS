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
require_once 'Services/Mail/classes/class.ilMailbox.php';
require_once 'Services/Mail/classes/class.ilFormatMail.php';
include_once 'Services/Table/classes/class.ilTable2GUI.php';
include_once 'Services/Search/classes/class.ilQueryParser.php';
include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
include_once 'Services/Search/classes/class.ilSearchResult.php';

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
*/
class ilMailSearchGUI
{
	/**
	 * @var ilTemplate
	 */
	private $tpl;

	/**
	 * @var ilCtrl
	 */
	private $ctrl;

	/**
	 * @var ilRbacReview
	 */
	protected $rbacreview;

	/**
	 * @var ilObjectDataCache
	 */
	protected $object_data_cache;

	/**
	 * @var ilLanguage
	 */
	private $lng;
	
	private $umail = null;

	private $errorDelete = false;

	public function __construct($wsp_access_handler = null, $wsp_node_id = null)
	{
		global $DIC;

		$this->tpl               = $DIC['tpl'];
		$this->ctrl              = $DIC['ilCtrl'];
		$this->lng               = $DIC['lng'];
		$this->rbacreview        = $DIC['rbacreview'];
		$this->object_data_cache = $DIC['ilObjDataCache'];

		// personal workspace
		$this->wsp_access_handler = $wsp_access_handler;
		$this->wsp_node_id = $wsp_node_id;
		
		$this->ctrl->saveParameter($this, "mobj_id");
		$this->ctrl->saveParameter($this, "ref");

		$this->umail = new ilFormatMail($DIC->user()->getId());
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
		// necessary because of select all feature of ilTable2GUI
		$recipients = array();
		$recipients = array_merge($recipients, (array)$_POST['search_name_to_addr']);
		$recipients = array_merge($recipients, (array)$_POST['search_name_to_usr']);
		$recipients = array_merge($recipients, (array)$_POST['search_name_to_grp']);

		$recipients = array_unique($recipients);

		$_SESSION["mail_search_results_to"] = $recipients;
		$_SESSION["mail_search_results_cc"] = $_POST["search_name_cc"];
		$_SESSION["mail_search_results_bcc"] = $_POST["search_name_bcc"];

		if($_GET["ref"] != "wsp")
		{	
			$this->saveMailData();
		}
		else
		{
			$this->addPermission($recipients);
		}
		
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
			$mail_data["use_placeholders"],
			$mail_data['tpl_ctx_id'],
			$mail_data['tpl_ctx_params']
		);
	}
	
	public function cancel()
	{
		$this->ctrl->returnToParent($this);
	}
	
	function search()
	{
		$_SESSION["mail_search_search"] = $_POST["search"];

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
	
	protected function initSearchForm()
	{
		if($_GET["ref"] != "wsp")
		{
			$this->saveMailData();
			$title = $this->lng->txt('search_recipients');
		}
		else
		{
			$this->lng->loadLanguageModule("wsp");
			$title = $this->lng->txt("wsp_share_search_users");
		}
		
		// searchform
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTitle($title);
		$form->setId('search_rcp');
		$form->setFormAction($this->ctrl->getFormAction($this, 'search'));
		
		$inp = new ilTextInputGUI($this->lng->txt("search_for"), 'search');
		$inp->setSize(30);
		$dsDataLink = $this->ctrl->getLinkTarget($this, 'lookupRecipientAsync', '', true, false);
		$inp->setDataSource($dsDataLink);
		
		if (strlen(trim($_SESSION["mail_search_search"])) > 0)
		{
			$inp->setValue(ilUtil::prepareFormOutput(trim($_SESSION["mail_search_search"]), true));
		}
		$form->addItem($inp);

		$form->addCommandButton('search', $this->lng->txt("search"));
		$form->addCommandButton('cancel', $this->lng->txt("cancel"));
		
		return $form;
	}

	public function lookupRecipientAsync()
	{
		include_once 'Services/JSON/classes/class.ilJsonUtil.php';
		include_once 'Services/Mail/classes/class.ilMailForm.php';

		$search = $_REQUEST["term"];
		$result = array();
		if (!$search)
		{
			echo ilJsonUtil::encode($result);
			exit;
		}
		
		// #14768
		$quoted = ilUtil::stripSlashes($search);
		$quoted = str_replace('%', '\%', $quoted);
		$quoted = str_replace('_', '\_', $quoted);
		
		$search_recipients = ($_GET["ref"] != "wsp");

		$mailFormObj = new ilMailForm;
		$result      = $mailFormObj->getRecipientAsync("%" . $quoted . "%", ilUtil::stripSlashes($search), $search_recipients);

		echo ilJsonUtil::encode($result);
		exit;
	}


	public function showResults()
	{
		$form = $this->initSearchForm();

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_search.html", "Services/Contact");
		$this->tpl->setVariable("ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setTitle($this->lng->txt("mail"));
		$this->tpl->setVariable('SEARCHFORM', $form->getHtml());
		
		// #14109
		if(strlen($_SESSION['mail_search_search']) < 3)
		{
			if($_GET["ref"] != "wsp")
			{
				$this->tpl->show();
			}
			return;
		}

		require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
		$relations = ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations();
		if(count($relations))
		{
			$contacts_search_result = new ilSearchResult();

			$query_parser = new ilQueryParser(addcslashes($_SESSION['mail_search_search'], '%_'));
			$query_parser->setCombination(QP_COMBINATION_AND);
			$query_parser->setMinWordLength(3);
			$query_parser->parse();

			$user_search = ilObjectSearchFactory::_getUserSearchInstance($query_parser);
			$user_search->enableActiveCheck(true);
			$user_search->setFields(array('login'));
			$result_obj = $user_search->performSearch();
			$contacts_search_result->mergeEntries($result_obj);

			$user_search->setFields(array('firstname'));
			$result_obj = $user_search->performSearch();
			$contacts_search_result->mergeEntries($result_obj);

			$user_search->setFields(array('lastname'));
			$result_obj = $user_search->performSearch();
			$contacts_search_result->mergeEntries($result_obj);

			$contacts_search_result->setMaxHits(100000);
			$contacts_search_result->preventOverwritingMaxhits(true);
			$contacts_search_result->filter(ROOT_FOLDER_ID, true);

			// Filter users (depends on setting in user accounts)
			include_once 'Services/User/classes/class.ilUserFilter.php';
			$users = ilUserFilter::getInstance()->filter($contacts_search_result->getResultIds());
			$users = array_intersect($users, $relations->getKeys());

			$tbl_contacts = new ilTable2GUI($this);
			$tbl_contacts->setTitle($this->lng->txt('mail_addressbook'));
			$tbl_contacts->setRowTemplate('tpl.mail_search_addr_row.html', 'Services/Contact');

			$has_mail_addr = false;
			$result        = array();
			$counter       = 0;
			foreach($users as $user)
			{
				$login = ilObjUser::_lookupLogin($user);

				if($_GET['ref'] == 'wsp')
				{
					$result[$counter]['check'] = ilUtil::formCheckbox(0, 'search_name_to_addr[]', $user);
				}
				else
				{
					$result[$counter]['check'] =
						ilUtil::formCheckbox(0, 'search_name_to_addr[]', $login) .
						ilUtil::formCheckbox(0, 'search_name_cc[]', $login) .
						ilUtil::formCheckbox(0, 'search_name_bcc[]', $login);
				}

				$result[$counter]['login'] = $login;
				if(ilObjUser::_lookupPref($user, 'public_email') == 'y')
				{
					$has_mail_addr             = true;
					$result[$counter]['email'] = ilObjUser::_lookupEmail($user, 'email');
				}

				if(in_array(ilObjUser::_lookupPref($user, 'public_profile'), array('y', "g")))
				{
					$name                          = ilObjUser::_lookupName($user);
					$result[$counter]['firstname'] = $name['firstname'];
					$result[$counter]['lastname']  = $name['lastname'];
				}
				else
				{
					$result[$counter]['firstname'] = '';
					$result[$counter]['lastname']  = '';
				}

				++$counter;
			}

			if($_GET['ref'] == 'wsp')
			{
				$tbl_contacts->addColumn("", "", "1%", true);
			}
			else
			{
				$tbl_contacts->addColumn($this->lng->txt('mail_to') . '/' . $this->lng->txt('cc') . '/' . $this->lng->txt('bc'), 'check', '10%');
			}
			$tbl_contacts->addColumn($this->lng->txt('login'), 'login', '15%');
			$tbl_contacts->addColumn($this->lng->txt('firstname'), 'firstname', '15%');
			$tbl_contacts->addColumn($this->lng->txt('lastname'), 'lastname', '15%');
			if($has_mail_addr)
			{
				foreach($result as $key => $val)
				{
					if($val['email'] == '') $result[$key]['email'] = '&nbsp;';
				}

				$tbl_contacts->addColumn($this->lng->txt('email'), 'email', "15%");
			}
			$tbl_contacts->setData($result);

			$tbl_contacts->setDefaultOrderField('login');
			$tbl_contacts->setPrefix('addr_');
			$tbl_contacts->enable('select_all');
			$tbl_contacts->setSelectAllCheckbox('search_name_to_addr');
			$tbl_contacts->setFormName('recipients');

			$this->tpl->setVariable('TABLE_ADDR', $tbl_contacts->getHTML());
		}

		$all_results = new ilSearchResult();

		$query_parser = new ilQueryParser(addcslashes($_SESSION['mail_search_search'], '%_'));
		$query_parser->setCombination(QP_COMBINATION_AND);
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

		$all_results->setMaxHits(100000);
		$all_results->preventOverwritingMaxhits(true);
		$all_results->filter(ROOT_FOLDER_ID, true);

		// Filter users (depends on setting in user accounts)
		include_once 'Services/User/classes/class.ilUserFilter.php';
		$users = ilUserFilter::getInstance()->filter($all_results->getResultIds());
		if(count($users))
		{
			$tbl_users = new ilTable2GUI($this);
			$tbl_users->setTitle($this->lng->txt('system') . ': ' . $this->lng->txt('persons'));
			$tbl_users->setRowTemplate('tpl.mail_search_users_row.html', 'Services/Contact');

			$result  = array();
			$counter = 0;
			foreach($users as $user)
			{
				$login = ilObjUser::_lookupLogin($user);

				if($_GET["ref"] != "wsp")
				{
					$result[$counter]['check'] = ilUtil::formCheckbox(0, 'search_name_to_usr[]', $login) .
						ilUtil::formCheckbox(0, 'search_name_cc[]', $login) .
						ilUtil::formCheckbox(0, 'search_name_bcc[]', $login);
				}
				else
				{
					$result[$counter]['check'] = ilUtil::formCheckbox(0, 'search_name_to_usr[]', $user);
				}
				$result[$counter]['login'] = $login;

				if(in_array(ilObjUser::_lookupPref($user, 'public_profile'), array('y', "g")))
				{
					$name                          = ilObjUser::_lookupName($user);
					$result[$counter]['firstname'] = $name['firstname'];
					$result[$counter]['lastname']  = $name['lastname'];
				}
				else
				{
					$result[$counter]['firstname'] = '';
					$result[$counter]['lastname']  = '';
				}

				if(ilObjUser::_lookupPref($user, 'public_email') == 'y')
				{
					$has_mail_usr              = true;
					$result[$counter]['email'] = ilObjUser::_lookupEmail($user);
				}

				++$counter;
			}

			if($_GET["ref"] != "wsp")
			{
				$tbl_users->addColumn($this->lng->txt('mail_to') . '/' . $this->lng->txt('cc') . '/' . $this->lng->txt('bc'), 'check', '10%');
			}
			else
			{
				$tbl_users->addColumn("", "", "1%");
			}
			$tbl_users->addColumn($this->lng->txt('login'), 'login', '15%');
			$tbl_users->addColumn($this->lng->txt('firstname'), 'firstname', '15%');
			$tbl_users->addColumn($this->lng->txt('lastname'), 'lastname', '15%');
			if($has_mail_usr == true)
			{
				foreach($result as $key => $val)
				{
					if($val['email'] == '') $result[$key]['email'] = '&nbsp;';
				}

				$tbl_users->addColumn($this->lng->txt('email'), 'email', '15%');
			}
			$tbl_users->setData($result);

			$tbl_users->setDefaultOrderField('login');
			$tbl_users->setPrefix('usr_');
			$tbl_users->enable('select_all');
			$tbl_users->setSelectAllCheckbox('search_name_to_usr');
			$tbl_users->setFormName('recipients');

			$this->tpl->setVariable('TABLE_USERS', $tbl_users->getHTML());
		}

		include_once 'Services/Search/classes/class.ilQueryParser.php';
		include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
		include_once 'Services/Search/classes/class.ilSearchResult.php';
		include_once 'Services/Membership/classes/class.ilParticipants.php';

		$group_results = new ilSearchResult();

		$query_parser = new ilQueryParser(addcslashes($_SESSION['mail_search_search'], '%_'));
		$query_parser->setCombination(QP_COMBINATION_AND);
		$query_parser->setMinWordLength(3);
		$query_parser->parse();

		$search = ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
		$search->setFilter(array('grp'));
		$result = $search->performSearch();
		$group_results->mergeEntries($result);
		$group_results->setMaxHits(PHP_INT_MAX);
		$group_results->preventOverwritingMaxhits(true);
		$group_results->setRequiredPermission('read');
		$group_results->filter(ROOT_FOLDER_ID, true);

		$visible_groups = array();
		if($group_results->getResults())
		{
			$tbl_grp = new ilTable2GUI($this);
			$tbl_grp->setTitle($this->lng->txt('system') . ': ' . $this->lng->txt('groups'));
			$tbl_grp->setRowTemplate('tpl.mail_search_groups_row.html', 'Services/Contact');

			$result  = array();
			$counter = 0;

			$this->object_data_cache->preloadReferenceCache(array_keys($group_results->getResults()));

			$groups = $group_results->getResults();
			foreach($groups as $grp)
			{
				if(!ilParticipants::hasParticipantListAccess($grp['obj_id']))
				{
					continue;
				}

				if($_GET["ref"] != "wsp")
				{
					$members = array();
					$roles   = $this->rbacreview->getAssignableChildRoles($grp['ref_id']);
					foreach($roles as $role)
					{
						if(substr($role['title'], 0, 14) == 'il_grp_member_' ||
							substr($role['title'], 0, 13) == 'il_grp_admin_'
						)
						{
							// FIX for Mantis: 7523
							array_push($members, '#' . $role['title']);
						}
					}
					$str_members = implode(',', $members);

					$result[$counter]['check'] =
						ilUtil::formCheckbox(0, 'search_name_to_grp[]', $str_members) .
						ilUtil::formCheckbox(0, 'search_name_cc[]', $str_members) .
						ilUtil::formCheckbox(0, 'search_name_bcc[]', $str_members);
				}
				else
				{
					$result[$counter]['check'] = ilUtil::formCheckbox(0, 'search_name_to_grp[]', $grp['obj_id']);
				}
				$result[$counter]['title']       = $this->object_data_cache->lookupTitle($grp['obj_id']);
				$result[$counter]['description'] = $this->object_data_cache->lookupDescription($grp['obj_id']);

				++$counter;
				$visible_groups[] = $grp;
			}
			
			if($visible_groups)
			{
				$tbl_grp->setData($result);
	
				if($_GET["ref"] != "wsp")
				{
					$tbl_grp->addColumn($this->lng->txt('mail_to') . '/' . $this->lng->txt('cc') . '/' . $this->lng->txt('bc'), 'check', '10%');
				}
				else
				{
					$tbl_grp->addColumn("", "", "1%");
				}
				$tbl_grp->addColumn($this->lng->txt('title'), 'title', '15%');
				$tbl_grp->addColumn($this->lng->txt('description'), 'description', '15%');
	
				$tbl_grp->setDefaultOrderField('title');
				$tbl_grp->setPrefix('grp_');
				$tbl_grp->enable('select_all');
				$tbl_grp->setSelectAllCheckbox('search_name_to_grp');
				$tbl_grp->setFormName('recipients');
	
				$this->tpl->setVariable('TABLE_GRP', $tbl_grp->getHTML());
			}
		}

		if(count($users) || count($visible_groups) || count($relations))
		{
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
			$this->tpl->setVariable("ALT_ARROW", '');

			if($_GET["ref"] != "wsp")
			{
				$this->tpl->setVariable('BUTTON_ADOPT', $this->lng->txt('adopt'));
			}
			else
			{
				$this->tpl->setVariable('BUTTON_ADOPT', $this->lng->txt('wsp_share_with_users'));
			}
		}
		else
		{
			$this->lng->loadLanguageModule('search');
			ilUtil::sendInfo($this->lng->txt('search_no_match'));
		}

		if($_GET["ref"] != "wsp")
		{
			$this->tpl->show();
		}
	}
	
	protected function addPermission($a_obj_ids)
	{
		if(!is_array($a_obj_ids))
		{
			$a_obj_ids = array($a_obj_ids);
		}
		
		$existing = $this->wsp_access_handler->getPermissions($this->wsp_node_id);
		$added = false;
		foreach($a_obj_ids as $object_id)
		{
			if(!in_array($object_id, $existing))
			{
				$added = $this->wsp_access_handler->addPermission($this->wsp_node_id, $object_id);
			}
		}
		
		if($added)
		{
			ilUtil::sendSuccess($this->lng->txt("wsp_share_success"), true);
		}		
	}
}
?>
