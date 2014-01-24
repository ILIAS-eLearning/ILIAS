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
* Class FormMailCodesGUI
*
* @author		Helmut Schottmüller <ilias@aurealis.de>
* @version  $Id$
*
* @extends ilPropertyFormGUI
* @ingroup ModulesSurvey
*/

include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

class FormMailCodesGUI extends ilPropertyFormGUI
{
	private $lng;
	private $guiclass;
	private $subject;
	private $messagetype;
	private $sendtype;
	private $savedmessages;
	private $mailmessage;
	private $savemessage;
	private $savemessagetitle;
	
	function __construct($guiclass)
	{
		parent::__construct();

		global $lng;
		global $ilAccess;
		
		$this->lng = &$lng;
		$this->guiclass = &$guiclass;
		
		$this->setFormAction($guiclass->ctrl->getFormAction($this->guiclass));
		$this->setTitle($this->lng->txt('compose'));

		$this->subject = new ilTextInputGUI($this->lng->txt('subject'), 'm_subject');
		$this->subject->setSize(50);
		$this->subject->setRequired(true);
		$this->addItem($this->subject);

		$this->sendtype = new ilRadioGroupInputGUI($this->lng->txt('recipients'), "m_notsent");
		$this->sendtype->addOption(new ilCheckboxOption($this->lng->txt("send_to_all"), 0, ''));
		$this->sendtype->addOption(new ilCheckboxOption($this->lng->txt("not_sent_only"), 1, ''));
		$this->sendtype->addOption(new ilCheckboxOption($this->lng->txt("send_to_unanswered"), 3, ''));
		$this->sendtype->addOption(new ilCheckboxOption($this->lng->txt("send_to_answered"), 2, ''));
		$this->addItem($this->sendtype);

		$existingdata = $this->guiclass->object->getExternalCodeRecipients();
		$existingcolumns = array();
		if (count($existingdata))
		{
			$first = array_shift($existingdata);
			foreach ($first as $key => $value)
			{
				if (strcmp($key, 'code') != 0 && strcmp($key, 'email') != 0 && strcmp($key, 'sent') != 0) array_push($existingcolumns, '[' . $key . ']');
			}
		}

		global $ilUser;
		$settings = $this->guiclass->object->getUserSettings($ilUser->getId(), 'savemessage');
		if (count($settings))
		{
			$options = array(0 => $this->lng->txt('please_select'));
			foreach ($settings as $setting)
			{
				$options[$setting['settings_id']] = $setting['title'];
			}
			$this->savedmessages = new ilSelectInputGUI($this->lng->txt("saved_messages"), "savedmessage");
			$this->savedmessages->setOptions($options);
			$this->addItem($this->savedmessages);
		}

		$this->mailmessage = new ilTextAreaInputGUI($this->lng->txt('message_content'), 'm_message');
		$this->mailmessage->setRequired(true);
		$this->mailmessage->setCols(80);
		$this->mailmessage->setRows(10);
		$this->mailmessage->setInfo(sprintf($this->lng->txt('message_content_info'), join($existingcolumns, ', ')));
		$this->addItem($this->mailmessage);

		// save message
		$this->savemessage = new ilCheckboxInputGUI('', "savemessage");
		$this->savemessage->setOptionTitle($this->lng->txt("save_reuse_message"));
		$this->savemessage->setValue(1);

		$this->savemessagetitle = new ilTextInputGUI($this->lng->txt('save_reuse_title'), 'savemessagetitle');
		$this->savemessagetitle->setSize(60);
		$this->savemessage->addSubItem($this->savemessagetitle);

		$this->addItem($this->savemessage);

		if (count($settings))
		{
			if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $this->addCommandButton("deleteSavedMessage", $this->lng->txt("delete_saved_message"));
			if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $this->addCommandButton("insertSavedMessage", $this->lng->txt("insert_saved_message"));
		}
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $this->addCommandButton("sendCodesMail", $this->lng->txt("send"));
	}
	
	public function getSavedMessages()
	{
		return $this->savedmessages;
	}
	
	public function getMailMessage()
	{
		return $this->mailmessage;
	}
}

?>