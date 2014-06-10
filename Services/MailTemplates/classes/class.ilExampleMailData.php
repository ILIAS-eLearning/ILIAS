<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailData.php';

class ilExampleMailData extends ilMailData
{
	protected $recipient_user_id = 0;
	protected $recipient_mail_address = '';
	protected $recipient_full_name = '';
	protected $carbon_copy_recipients = array();
	protected $blind_carbon_copy_recipients = array();
	protected $placeholders = array();
	
	protected $attachments;
	
	function getRecipientUserId()
	{
		return $this->recipient_user_id;
	}
	
	function setRecipientUserId($a_user_id)
	{
		$this->recipient_user_id = $a_user_id;
	}
	
	function getRecipientMailAddress()
	{
		return $this->recipient_mail_address;
	}

	function getRecipientFullName()
	{
		return $this->recipient_full_name;
	}

	function hasCarbonCopyRecipients()
	{
		if (count($this->carbon_copy_recipients) != 0)
		{
			return true;
		}
		return false;
	}

	function getCarbonCopyRecipients()
	{
		return $this->carbon_copy_recipients;
	}

	function hasBlindCarbonCopyRecipients()
	{
		if (count($this->blind_carbon_copy_recipients) != 0)
		{
			return true;
		}
		return false;
	}

	function getBlindCarbonCopyRecipients()
	{
		return $this->blind_carbon_copy_recipients;
	}

	function getPlaceholderLocalized($a_placeholder_code, $a_lng, $a_markup = false)
	{
		foreach ($this->placeholders as $placeholder)
		{
			if ($placeholder['placeholder_code'] == $a_placeholder_code)
			{
				if ($a_markup == true)
				{
					return '<strong>'.$placeholder['placeholder_content'] . '</strong>';
				} 
				else 
				{
					return $placeholder['placeholder_content'];
				}
			}
		}
		return '';
	}

	public function setBlindCarbonCopyRecipients($blind_carbon_copy_recipients)
	{
		$this->blind_carbon_copy_recipients[] = $blind_carbon_copy_recipients;
	}

	public function setCarbonCopyRecipients($carbon_copy_recipients)
	{
		$this->carbon_copy_recipients[] = $carbon_copy_recipients;
	}

	public function setPlaceholders($placeholders)
	{
		$this->placeholders = $placeholders;
	}

	public function setRecipientFullName($recipient_full_name)
	{
		$this->recipient_full_name = $recipient_full_name;
	}

	public function setRecipientMailAddress($recipient_mail_address)
	{
		$this->recipient_mail_address = $recipient_mail_address;
	}

	function hasAttachments()
	{
		if (count($this->attachments) != 0)
		{
			return true;
		}
		return false;
	}

	function addAttachment($a_attachment)
	{
		$this->attachments[] = $a_attachment;	
	}
	
	function getAttachments($a_lng)
	{
		return $this->attachments;
	}
}
