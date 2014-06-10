<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailData.php';

class ilSampleMailData extends ilMailData
{
	public function getRecipientUserId()
	{
		return 6;
	}
	
	public function getRecipientMailAddress()
	{
		return "mbecker@databay.de";
	}

	public function getRecipientFullName()
	{
		return "Maximilian Becker";
	}

	public function hasCarbonCopyRecipients()
	{
		return true;
	}

	public function getCarbonCopyRecipients()
	{
		$recipients = array(
			array(
				'cc_name'		=> 'Maximilian Becker',
				'cc_address'	=> 'mbecker@databay.de'
			)
		);
		
		return $recipients;
	}

	public function hasBlindCarbonCopyRecipients()
	{
		return true;
	}

	public function getBlindCarbonCopyRecipients()
	{
		$recipients = array(
			array(
				'bcc_name'		=> 'Maximilian Becker',
				'bcc_address'	=> 'mbecker@databay.de'
			)
		);

		return $recipients;
	}

	public function getPlaceholderLocalized($a_placeholder_code, $a_lng, $a_markup = false)
	{
		if ($a_markup == false)
		{
			// JBO-Songs as a sample.
			switch (strtolower($a_placeholder_code))
			{
				case 'placeholder_1':
					return 'Ich will Lärm!';
				case 'placeholder_2':
					return 'Fränkisches Bier';
				case 'placeholder_3':
					return 'Verteidiger des Blödsinns';
				case 'placeholder_4':
					return 'Gänseblümchen';
			}
		}
		
		if ($a_markup == true)
		{
			switch (strtolower($a_placeholder_code))
			{
				case 'placeholder_1':
					return 'Ich <strong>will</strong> L&auml;rm!';
				case 'placeholder_2':
					return 'Fr&auml;nkisches <strong>Bier</strong>';
				case 'placeholder_3':
					return 'Verteidiger <strong>des</strong> Bl&ouml;dsinns';
				case 'placeholder_4':
					return '<h3>G&auml;nsebl&uuml;mchen</h3>';
			}
		}
	}

	function hasAttachments()
	{
		return true;
	}

	function getAttachments($a_lng)
	{
		return array('unzip_test_file.zip');
	}
}
