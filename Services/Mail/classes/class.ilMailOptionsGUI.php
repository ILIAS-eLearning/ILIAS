<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Mail/classes/class.ilMailbox.php";
require_once "Services/Mail/classes/class.ilFormatMail.php";
require_once 'Services/Mail/classes/class.ilMailOptions.php';

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
*/
class ilMailOptionsGUI
{
	private $tpl = null;
	private $ctrl = null;
	private $lng = null;
	
	private $umail = null;
	private $mbox = null;

	private $errorDelete = false;

	public function __construct()
	{
		global $tpl, $ilCtrl, $lng, $ilUser;

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		
		$this->ctrl->saveParameter($this, "mobj_id");

		$this->umail = new ilFormatMail($ilUser->getId());
		$this->mbox = new ilMailBox($ilUser->getId());
	}

	public function executeCommand()
	{
		$forward_class = $this->ctrl->getNextClass($this);
		switch($forward_class)
		{
			default:
				if (!($cmd = $this->ctrl->getCmd()))
				{
					$cmd = "showOptions";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/** 
	* Called if the user pushes the submit button of the mail options form.
	* Passes the post data to the mail options model instance to store them.
	* 
	* @access public
	* 
	*/
	public function saveOptions()
	{
		global $lng, $ilUser, $ilSetting;
		
		$this->tpl->setTitle($lng->txt('mail'));
		$this->initMailOptionsForm();
		
		$mailOptions = new ilMailOptions($ilUser->getId());			
		if($ilSetting->get('usr_settings_hide_mail_incoming_mail') != '1' && 
		   $ilSetting->get('usr_settings_disable_mail_incoming_mail') != '1')
		{
			$incoming_type = (int)$_POST['incoming_type'];
		}
		else
		{
			$incoming_type = $mailOptions->getIncomingType();
		}		
		
		if($this->form->checkInput())
		{			
			$mailOptions->updateOptions(
				ilUtil::stripSlashes($_POST['signature']),
				(int)$_POST['linebreak'],
				$incoming_type,
				(int)$_POST['cronjob_notification']
			);
			
			ilUtil::sendSuccess($lng->txt('mail_options_saved'));			
		}
		
		$this->form->setValuesByPost();
		
		$this->tpl->setContent($this->form->getHTML());
		$this->tpl->show();
	}

	/** 
	* Called to display the mail options form
	* 
	* @access public
	* 
	*/
	public function showOptions()
	{
		global $lng;
		
		$this->tpl->setTitle($lng->txt('mail'));
		
		$this->initMailOptionsForm();
		$this->setMailOptionsValuesByDB();		
		
		$this->tpl->setContent($this->form->getHTML());
		$this->tpl->show();
	}
	
	/** 
	* Fetches data from model and loads this data into form
	* 
	* @access private
	* 
	*/
	private function setMailOptionsValuesByDB()
	{
		global $ilUser, $ilSetting;		
		
		$mailOptions = new ilMailOptions($ilUser->getId());
		
		$data= array(
			'linebreak' => $mailOptions->getLinebreak(),
			'signature' => $mailOptions->getSignature(),
			'cronjob_notification' => $mailOptions->getCronjobNotification()
		);
		
		if($ilSetting->get('usr_settings_hide_mail_incoming_mail') != '1')
		{		
			$data['incoming_type'] = $mailOptions->getIncomingType();
		}
		
		$this->form->setValuesByArray($data);	
	}

	/** 
	* Initialises the mail options form
	* 
	* @access private
	* 
	*/
	private function initMailOptionsForm()
	{
		global $ilCtrl, $ilSetting, $lng, $ilUser;	
		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->form = new ilPropertyFormGUI();
		
		$this->form->setFormAction($ilCtrl->getFormAction($this, 'saveOptions'));
		$this->form->setTitle($lng->txt('mail_settings'));
			
		// BEGIN INCOMING
		if($ilSetting->get('usr_settings_hide_mail_incoming_mail') != '1')
		{
			$options = array(
				IL_MAIL_LOCAL => $lng->txt('mail_incoming_local'), 
				IL_MAIL_EMAIL => $lng->txt('mail_incoming_smtp'),
				IL_MAIL_BOTH => $lng->txt('mail_incoming_both')
			);		
			$si = new ilSelectInputGUI($lng->txt('mail_incoming'), 'incoming_type');
			$si->setOptions($options);
			if(!strlen(ilObjUser::_lookupEmail($ilUser->getId())) ||
			   $ilSetting->get('usr_settings_disable_mail_incoming_mail') == '1')
			{
				$si->setDisabled(true);
			}		
			$this->form->addItem($si);
		}
		
		// BEGIN LINEBREAK_OPTIONS
		$options = array();
		for($i = 50; $i <= 80; $i++)
		{
			$options[$i] = $i; 
		}	
		$si = new ilSelectInputGUI($lng->txt('linebreak'), 'linebreak');
		$si->setOptions($options);			
		$this->form->addItem($si);
		
		// BEGIN SIGNATURE
		$ta = new ilTextAreaInputGUI($lng->txt('signature'), 'signature');
		$ta->setRows(10);
		$ta->setCols(60);			
		$this->form->addItem($ta);
		
		// BEGIN CRONJOB NOTIFICATION
		if($ilSetting->get('mail_notification'))
		{
			$cb = new ilCheckboxInputGUI($lng->txt('cron_mail_notification'), 'cronjob_notification');			
			$cb->setInfo($lng->txt('mail_cronjob_notification_info'));
			$cb->setValue(1);
			$this->form->addItem($cb);
		}		
		
		$this->form->addCommandButton('saveOptions', $lng->txt('save'));
	}
}

?>