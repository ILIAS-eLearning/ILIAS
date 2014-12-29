<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* 
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesMail
*/
include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");


class ilAddressbookTableGUI extends ilTable2GUI
{
	protected $lng = null;
	protected $ctrl;

	/**
	 * @var bool
	 */
	private $mailing_allowed = false;
	/**
	 * @var bool
	 */
	private $smtp_mailing_allowed = false;
	/**
	 * @var bool|string
	 */
	private $cron_upd_adrbook = false;
	/**
	 * @var bool
	 */
	private $chat_active = false;

	/**
	 * @var array
	 */
	private $filter = array();
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = '', $is_mailing_allowed = false, $is_chat_active = false)
	{
	 	global $lng, $ilCtrl, $ilSetting;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;

		$this->setMailingAllowed($is_mailing_allowed);
		$this->setChatActive($is_chat_active);

		$this->setId('addr_book');
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, 'setAddressbookFilter'));

		$this->setTitle($lng->txt("mail_addr_entries"));
		$this->setRowTemplate("tpl.mail_addressbook_row.html", "Services/Contact");

		$this->setDefaultOrderField('login');
		
		$this->cron_upd_adrbook = $ilSetting->get('cron_upd_adrbook', 0);
				
		$width = '20%';
		if($this->cron_upd_adrbook != 0)
		{
			$width = '16.6%';
		}

		$this->addColumn('', 'addr_id', '1px', true);
		$this->addColumn($this->lng->txt('login'), 'login', $width);
		$this->addColumn($this->lng->txt('firstname'), 'firstname', $width);
		$this->addColumn($this->lng->txt('lastname'), 'lastname', $width);
		$this->addColumn($this->lng->txt('email'), 'email', $width);
		
		if($ilSetting->get('cron_upd_adrbook', 0) != 0)
		{
			$this->addColumn($this->lng->txt('auto_update'), 'auto_update', $width);
		}
		$this->addColumn($this->lng->txt('actions'), 'actions', '20%');
		
		$this->enable('select_all');
		$this->setSelectAllCheckbox('addr_id');
		$this->addCommandButton('showAddressForm', $this->lng->txt('add'));

		if($this->mailing_allowed)
		{
			$this->addMultiCommand('mailToUsers', $this->lng->txt('send_mail_to'));
		}
		$this->addMultiCommand('confirmDelete', $this->lng->txt('delete'));

		if($this->chat_active)
		{
			$this->addMultiCommand('inviteToChat', $this->lng->txt('invite_to_chat'));
		}

		$this->initFilter();
		$this->setFilterCommand('setAddressbookFilter');
		$this->setResetCommand('resetAddressbookFilter');
	}

	/**
	 * @param $key
	 * @param $value
	 * @return string
	 */
	public function formatCellValue($key, $value)
	{
		switch($key)
		{
			case 'addr_id':
				$value =  ilUtil::formCheckbox(0, 'addr_id[]', $value);
				break;
			case 'auto_update':
				if($this->cron_upd_adrbook != 0)
				{
					$value = $value == 1 ? $this->lng->txt('yes') : $this->lng->txt('no');
				}
				else
				{
					$value = '';
				}
				break;
		}

		return $value;
	}
	
	/**
	 * Fill row
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function fillRow($row)
	{
		foreach ($row as $key => $value)
		{
			$value = $this->formatCellValue($key, $value);	
			$this->ctrl->setParameter($this->parent_obj, 'addr_id', $row['addr_id']);
			
			if($key == 'check')
			{
				$this->tpl->setVariable("VAL_LOGIN_LINKED_LOGIN", $value);
			}
	
			if($key == "login" && $value != "")
			{
				if($this->mailing_allowed)
				{
					$this->tpl->setVariable("VAL_LOGIN_LINKED_LINK", $this->ctrl->getLinkTarget($this->parent_obj, 'mailToUsers'));
					$this->tpl->setVariable("VAL_LOGIN_LINKED_LOGIN", $value);
				}
				else
				{
					$this->tpl->setVariable("VAL_LOGIN_UNLINKED", $value);
				}
			}
	
			if($key == "email")
			{
				if($_GET["baseClass"] == "ilMailGUI" && $this->smtp_mailing_allowed)
				{
					$this->tpl->setVariable("VAL_EMAIL_LINKED_LINK", $this->ctrl->getLinkTarget($this->parent_obj, 'mailToUsers'));
					$this->tpl->setVariable("VAL_EMAIL_LINKED_EMAIL", $value);
				}
				else
				{
					$this->tpl->setVariable("VAL_EMAIL_UNLINKED", $value);
				}
			}
	
			if($key == 'auto_update' && $this->cron_upd_adrbook != 0)
			{
				$this->tpl->setVariable("VAL_CRON_AUTO_UPDATE", $value);
			}

			$this->tpl->setVariable("VAL_".strtoupper($key), $value);
		}
	
		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($this->lng->txt("actions"));
		$current_selection_list->setId("act_" . $row['addr_id']);

		$current_selection_list->addItem($this->lng->txt("edit"), '', $this->ctrl->getLinkTarget($this->parent_obj, "showAddressForm"));

		if($this->mailing_allowed)
		{
			$current_selection_list->addItem($this->lng->txt("send_mail_to"), '', $this->ctrl->getLinkTarget($this->parent_obj, "mailToUsers"));
		}

		$current_selection_list->addItem($this->lng->txt("delete"), '', $this->ctrl->getLinkTarget($this->parent_obj, "confirmDelete"));


		if($this->chat_active)
		{
			$current_selection_list->addItem($this->lng->txt("invite_to_chat"), '', $this->ctrl->getLinkTarget($this->parent_obj, "inviteToChat"));
		}
		$this->tpl->setVariable("VAL_ACTIONS", $current_selection_list->getHTML());
	}

	
	public function initFilter()
	{
		include_once 'Services/Form/classes/class.ilTextInputGUI.php';
		$ul = new ilTextInputGUI($this->lng->txt('login').'/'.$this->lng->txt('email').'/'.$this->lng->txt('name'), 'query');
		$ul->setDataSource($this->ctrl->getLinkTarget($this->getParentObject(), 'lookupAddressbookAsync', '', true));
		$ul->setSize(20);
		$ul->setSubmitFormOnEnter(true);
		$this->addFilterItem($ul);
		$ul->readFromSession();
		$this->filter['query'] = $ul->getValue();
	}
	

	public function setMailingAllowed($mailing_allowed)
	{
		$this->mailing_allowed = $mailing_allowed;
	}

	/**
	 * @param $chat_active
	 */
	public function setChatActive($chat_active)
	{
		$this->chat_active = $chat_active;
	}

	/**
	 * @param $smtp_mailing_allowed
	 */
	public function setSmtpMailingAllowed($smtp_mailing_allowed)
	{
		$this->smtp_mailing_allowed = $smtp_mailing_allowed;
	}

	/**
	 * @return mixed
	 */
	public function getFilterQuery()
	{
		return $this->filter['query'];
	}
	
} 
?>
