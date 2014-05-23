<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* 
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesMail
*/
include_once('Services/Table/classes/class.ilTable2GUI.php');


class ilMailSearchCoursesMembersTableGUI extends ilTable2GUI
{
	protected $lng = null;
	protected $ctrl;
	protected $parentObject;
	protected $mode;
	protected $mailing_allowed;
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object	parent object
	 * @param string	type; valid values are 'crs' for courses and
	 *			and 'grp' for groups
	 * 
	 */
	public function __construct($a_parent_obj, $type = 'crs', $context = 'mail')
	{
	 	global $lng,$ilCtrl, $ilUser, $lng, $rbacsystem;

		$this->context = $context;
		if($this->context == "mail")
		{
			// check if current user may send mails
			include_once "Services/Mail/classes/class.ilMail.php";
			$mail = new ilMail($_SESSION["AccountId"]);
			$this->mailing_allowed = $rbacsystem->checkAccess('internal_mail',$mail->getMailObjectReferenceId());
		}

		parent::__construct($a_parent_obj, 'showMembers');
		$lng->loadLanguageModule('crs');
		$this->parentObject = $a_parent_obj;
		$mode = array();
		if ($type == 'crs')
		{
			$mode["checkbox"] = 'search_crs';
			$mode["short"] = 'crs';
			$mode["long"] = 'course';
			$mode["lng_type"] = $lng->txt('course');
			$mode["view"] = "crs_members";
			$mode["tableprefix"] = "crstable_members";
		}
		else if ($type == 'grp')
		{
			$mode["checkbox"] = 'search_grp';
			$mode["short"] = 'grp';
			$mode["long"] = 'group';
			$mode["lng_type"] = $lng->txt('group');
			$mode["view"] = "grp_members";
			$mode["tableprefix"] = "grptable_members";
		}
		$this->setTitle($lng->txt('members'));
		$this->mode = $mode;
		$ilCtrl->setParameter($a_parent_obj, 'view', $mode['view']);
		if ($_GET['ref'] != '')
			$ilCtrl->setParameter($a_parent_obj, 'ref', $_GET['ref']);
		if (is_array($_POST[$mode["checkbox"]]))
			$ilCtrl->setParameter($a_parent_obj, $mode["checkbox"], implode(',', $_POST[$mode["checkbox"]]));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$ilCtrl->clearParameters($a_parent_obj);

		$this->setPrefix($mode['tableprefix']);
		$this->setSelectAllCheckbox('search_members[]');
		$this->setRowTemplate('tpl.mail_search_courses_members_row.html', 'Services/Contact');

		// setup columns
		$this->addColumn('', '', '1%', true);
		$this->addColumn($lng->txt('login'), 'members_login', '22%');
		$this->addColumn($lng->txt('name'), 'members_name', '22%');
		$this->addColumn($lng->txt($mode['long']), 'members_crs_grp', '22%');
		$this->addColumn($lng->txt('mail_in_addressbook'), 'members_in_addressbook', '23%');
		$this->addColumn($lng->txt('actions'), '', '10%');
		
		if($this->context == "mail")
		{
			if ($this->mailing_allowed)
				$this->addMultiCommand('mail', $lng->txt('mail_members'));
			$this->addMultiCommand('adoptMembers', $lng->txt("mail_into_addressbook"));
		}
		else
		{
			$lng->loadLanguageModule("wsp");
			$this->addMultiCommand('share', $lng->txt("wsp_share_with_members"));
		}
		
		$this->addCommandButton('cancel', $lng->txt('cancel'));
	}
	
	/**
	 * Fill row
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function fillRow($a_set)
	{
		global $ilCtrl;
		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($this->lng->txt("actions"));
		$current_selection_list->setId("act_".md5($a_set['members_id'].'::'.$a_set['search_' . $this->mode['short']]));

		$ilCtrl->setParameter($this->parentObject, 'search_members', $a_set['members_id']);
		$ilCtrl->setParameter($this->parentObject, 'search_' . $this->mode['short'], 
			is_array($_REQUEST['search_' . $this->mode['short']]) ?
			implode(',', array_filter(array_map('intval', $_REQUEST['search_' . $this->mode['short']]))) :
			(int)$_REQUEST['search_' . $this->mode['short']]
		);
		$ilCtrl->setParameter($this->parentObject, 'view', $this->mode['view']);

		if($this->context == "mail")
		{
			if ($this->mailing_allowed)
				$current_selection_list->addItem($this->lng->txt("mail_member"), '', $ilCtrl->getLinkTarget($this->parentObject, "mail"));
			$current_selection_list->addItem($this->lng->txt("mail_into_addressbook"), '', $ilCtrl->getLinkTarget($this->parentObject, "adoptMembers"));
		}
		else
		{
			$current_selection_list->addItem($this->lng->txt("wsp_share_with_members"), '', $ilCtrl->getLinkTarget($this->parentObject, "share"));
		}
		
		$this->tpl->setVariable(strtoupper('CURRENT_ACTION_LIST'), $current_selection_list->getHTML());
		
		foreach ($a_set as $key => $value)
		{
			$this->tpl->setVariable(strtoupper($key), $value);
		}
	}	
} 
?>
