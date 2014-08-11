<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilMailTemplateManagementAPI
{
	/**
	 * @var string
	 */
	protected $from_address = '';

	/**
	 * @var string
	 */
	protected $from_name = '';

	/**
	 * @param string $from_address
	 */
	public function setFromAddress($from_address)
	{
		$this->from_address = $from_address;
	}

	/**
	 * @return string
	 */
	public function getFromAddress()
	{
		return $this->from_address;
	}

	/**
	 * @param string $from_name
	 */
	public function setFromName($from_name)
	{
		$this->from_name = $from_name;
	}

	/**
	 * @return string
	 */
	public function getFromName()
	{
		return $this->from_name;
	}
	
	
	public function getCategoryExists($a_category_name)
	{
		throw new Exception('Not implemented.');
	}

	public function getTemplateExists($a_category_name, $a_template_type)
	{
		throw new Exception('Not implemented.');
	}

	public function getTemplateTypesByCategory($a_category_name)
	{
		global $ilDB;
		$query = "
		SELECT cat_mail_variants.id, cat_mail_variants.language, cat_mail_templates.template_type
		FROM cat_mail_templates
		JOIN cat_mail_variants ON cat_mail_variants.mail_types_fi = cat_mail_templates.id
		WHERE cat_mail_templates.category_name = %s";
		
		$result = $ilDB->queryF($query, array('text'), array($a_category_name));
		
		$retval = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			$type = array();
			$type['id'] = $row['id'];
			$type['language'] = $row['language'];
			$type['type'] = $row['template_type'];
			$retval[] = $type;
		}
		
		return $retval;
	}
	
	// gev-patch start
	public function getTemplateCategoriesByType($a_type_name) {
		global $ilDB;
		$query = "
		SELECT cat_mail_variants.id, cat_mail_variants.language, cat_mail_templates.category_name
		FROM cat_mail_templates
		JOIN cat_mail_variants ON cat_mail_variants.mail_types_fi = cat_mail_templates.id
		WHERE cat_mail_templates.template_type = %s";
		
		$result = $ilDB->queryF($query, array('text'), array($a_type_name));
		
		$retval = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			$type = array();
			$type['id'] = $row['id'];
			$type['language'] = $row['language'];
			$type['category'] = $row['category_name'];
			$retval[] = $type;
		}
		
		return $retval;
	}
	// gev-patch end
	
	public function createCategory($a_category_name)
	{
		throw new Exception('Not implemented.');
		/* Categories are not an entity, they are created solely as organisational help.
		   Categories are "created" during the creation of a template type and are kept in the according
		   template type records.
		*/
	}

	public function createTemplateType($a_category_name, $a_template_type, $a_consumer_location)
	{
		require_once './Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php';
		$entity = new ilMailTemplateSettingsEntity();
		global $ilDB;
		$entity->setIlDB($ilDB);
		$entity->setTemplateCategoryName($a_category_name);
		$entity->setTemplateTemplateType($a_template_type);
		$entity->setTemplateConsumerLocation($a_consumer_location);
		$entity->save();
	}

	public function createTemplateVariant(
		$a_category_name,
		$a_template,
		$a_language,
		$a_subject,
		$a_message_plain,
		$a_message_html,
		$a_active
		)
	{
		require_once './Services/MailTemplates/classes/class.ilMailTemplateVariantEntity.php';
		$entity = new ilMailTemplateVariantEntity();
		global $ilDB;
		$entity->setIlDB($ilDB);

		/* Get mail Types Fi from category / template */
		global $ilDB;
		$resultset = $ilDB->queryF(
			'SELECT id FROM cat_mail_templates WHERE category_name = %s AND language = %s',
			array ('text', 'text'),
			array ($a_category_name, $a_language)
		);
		
		$row = $ilDB->fetchAssoc($resultset);
		$entity->setMailTypesFi($row['id']);

		$entity->setLanguage($a_language);

		$entity->setMessageSubject($a_subject);
		$entity->setMessageHtml($a_message_html);
		$entity->setMessagePlain($a_message_plain);
		$entity->setTemplateActive($a_active);

		$entity->setCreatedDate(time());
		$entity->setUpdatedDate(time());
		
		global $ilUser;
		$entity->setUpdatedUsrFi($ilUser->getId());
		$entity->save();
	}
	
	/** @param $a_mail_data ilMailData */
	public function sendMail(
		$a_category_name, 
		$a_template, 
		$a_language, 
		$a_mail_data)
	{
		global $ilDB;
		
		require_once './Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php';
		require_once './Services/MailTemplates/classes/class.ilMailTemplateVariantEntity.php';
		
		$settings = new ilMailTemplateSettingsEntity();
		$settings->setIlDB($ilDB);
		$settings->loadByCategoryAndTemplate($a_category_name, $a_template);
		$adapter = $settings->getAdapterClassInstance();

		$variant = new ilMailTemplateVariantEntity();
		$variant->setIlDB($ilDB);
		$variant->loadByTypeAndLanguage($settings->getTemplateTypeId(), $a_language);
		
		
		// content
		
		$placeholders = $adapter->getPlaceholdersLocalized($a_category_name, $a_template, $a_language);		
		$messages = $this->getPopulatedVariantMessages($variant, $placeholders, $a_mail_data, $a_language);
		
		
		// transport
		
		require_once './Services/MailTemplates/lib/phpmailer/class.phpmailer.php';
		$mail = new PHPMailer();

		// SETTINGS MACHEN!
		// $mail->IsSMTP();
		// $mail->Host = "smtp.example.com";	

		if($this->getFromAddress())
		{
			$mail->From = $this->getFromAddress();
		}

		if($this->getFromName())
		{
			$mail->FromName = $this->getFromName();
		}
		else if($mail->From)
		{
			// To prevent something like Root User <Email_Address> if only the address is set
			$mail->FromName = '';
		}
		
		$mail->IsMail();
		
		$dev_recipient = $plain_prefix = $plain_html = null;
		if(DEVMODE)
		{
			include_once "Services/Administration/classes/class.ilSetting.php";
			$vofue_set = new ilSetting("vofue");
			$dev_recipient = $vofue_set->get("mail_setting_dev_recipient");		
		}
		
		if(!$dev_recipient)
		{
			$mail->AddAddress(
				$a_mail_data->getRecipientMailAddress(), 
				$a_mail_data->getRecipientFullName()
			);						

			if ($a_mail_data->hasCarbonCopyRecipients())
			{
				foreach ($a_mail_data->getCarbonCopyRecipients() as $cc_recipient)
				{
					$mail->AddCC(
						$cc_recipient['cc_address'], 
						$cc_recipient['cc_name']
					);				
				}
			}		

			if ($a_mail_data->hasBlindCarbonCopyRecipients())
			{
				foreach ($a_mail_data->getBlindCarbonCopyRecipients() as $bcc_recipient)
				{
					$mail->AddBCC(
						$bcc_recipient['bcc_address'], 
						$bcc_recipient['bcc_name']
					);								
				}
			}
		}
		else
		{
			$mail->AddAddress($dev_recipient);	
			
			$plain_prefix = array("To: ".$a_mail_data->getRecipientMailAddress()." (". 
				$a_mail_data->getRecipientFullName().")");
			
			if ($a_mail_data->hasCarbonCopyRecipients())
			{
				foreach ($a_mail_data->getCarbonCopyRecipients() as $cc_recipient)
				{
					$plain_prefix[] = "CC: ".$cc_recipient['cc_address']." (".
						$cc_recipient['cc_name'].")";			
				}
			}		

			if ($a_mail_data->hasBlindCarbonCopyRecipients())
			{
				foreach ($a_mail_data->getBlindCarbonCopyRecipients() as $bcc_recipient)
				{
					$plain_prefix[] = "BCC: ".$bcc_recipient['bcc_address']." (".
						$bcc_recipient['bcc_name'].")";								
				}
			}
			
			$plain_html = "<p>".implode("<br />", $plain_prefix)."<hr></p>";
			$plain_prefix = implode("\n", $plain_prefix)."\n-------------------------------\n\n";		
		}
		
		$mail->Subject = $messages['subject'];
		
		require_once 'Services/MailTemplates/classes/class.ilMailTemplateFrameSettingsEntity.php';
		$frame_entity = new ilMailTemplateFrameSettingsEntity($ilDB, new ilSetting('mail_tpl'));
		if(strlen($messages['html']) != 0)
		{
			$mail->Body = str_ireplace('[content]', $plain_html . $messages['html'], $frame_entity->getHtmlFrame());
			if($frame_entity->doesImageExist())
			{
				$mail->Body = str_ireplace('[image]', '<img src="cid:frame_image_path" ' . ($frame_entity->getImageStyles() ? 'style="' . $frame_entity->getImageStyles() . '" ' : '') . '/>', $mail->Body);
				$mail->AddEmbeddedImage($frame_entity->getFileSystemBasePath() . '/' . $frame_entity->getImageName(), 'frame_image_path');
			}
			if(strlen($messages['plain']) != 0)
			{
				$mail->AltBody = str_ireplace('[content]', $plain_prefix . $messages['plain'], $frame_entity->getPlainTextFrame());
			}
		}
		else
		{
			$mail->Body = str_ireplace('[content]', $messages['plain'], $frame_entity->getPlainTextFrame());
		}
		
		$mail->CharSet = 'utf-8';

		if ($a_mail_data->hasAttachments())
		{
			foreach ($a_mail_data->getAttachments($a_language) as $attname => $attachment)
			{
				$mail->AddAttachment($attachment, $attname);
			}
		}
		
		
		if(!$mail->Send()) {
			$a = false;
		} 
		else 
		{
			$a = true;
		}
	}
	
	/**
	 * Get messages from variant and populate placeholders
	 *
	 * @param ilMailTemplateVariantEntity $variant
	 * @param array $placeholders
	 * @param ilMailData $a_mail_data
	 * @param string $a_language
	 * @return array
	 */
	public function getPopulatedVariantMessages(
		ilMailTemplateVariantEntity $variant, 
		array $placeholders, 
		ilMailData $a_mail_data, 
		$a_language)
	{
		global $lng;
		
		$messages			 = array();
		$messages['plain']   = $variant->getMessagePlain();
		$messages['html']    = $variant->getMessageHtml();
		$messages['subject'] = $variant->getMessageSubject();

		// gev-patch start
		if ($a_mail_data->deliversStandardPlaceholders()) {
			$salutation = $a_mail_data->getPlaceholderLocalized("SALUTATION");
			$login = $a_mail_data->getPlaceholderLocalized("LOGIN");
			$first_name = $a_mail_data->getPlaceholderLocalized("FIRST_NAME");
			$last_name = $a_mail_data->getPlaceholderLocalized("LAST_NAME");
		}
		// gev-patch end
		else if ($a_mail_data->getRecipientUserId())
		{
			$user = self::getCachedUserInstance($a_mail_data->getRecipientUserId());
			$salutation = $this->getSalutation($user, $lng);
			$login = $user->getLogin();
			$first_name = $user->getFirstname();
			$last_name = $user->getLastname();
		}
		else
		{
			$salutation = $lng->txt('salutation_n'); // Sehr geehrte Damen und Herren
			$login = $lng->txt('login_none');
			
			// Parse out of recipient full name:
			$name_parts = explode(' ', $a_mail_data->getRecipientFullName());
			$first_name = $name_parts[0];
			$last_name = $name_parts[1];
		}
			
		$ilias_url = ilUtil::_getHttpPath().'/login.php?client_id='.CLIENT_ID;
		foreach ($messages as $key => $message)
		{
			if ($key == 'html')
			{
				$allow_markup = true;
			}
			else
			{
				$allow_markup = false;
			}
			
			$message = $this->populatePlaceholder($message, '[SALUTATION]', $salutation);
			$message = $this->populatePlaceholder($message, '[LOGIN]', $login);
			$message = $this->populatePlaceholder($message, '[FIRST_NAME]', $first_name);
			$message = $this->populatePlaceholder($message, '[LAST_NAME]', $last_name);
			// @todo dirty hack
			if($allow_markup)
			{
				$message = $this->populatePlaceholder($message, '[ILIAS_URL]', '<a href="'.$ilias_url.'">'.$ilias_url.'</a>');
			}
			else
			{
				$message = $this->populatePlaceholder($message, '[ILIAS_URL]', $ilias_url);
			}
			$message = $this->populatePlaceholder($message, '[CLIENT_NAME]', CLIENT_NAME);
			
			foreach ($placeholders as $placeholder)
			{
				$message = $this->populatePlaceholder(
					$message, 
					'[' . $placeholder['placeholder_code'] . ']', 
					$a_mail_data->getPlaceholderLocalized(
									$placeholder['placeholder_code'], 
									$a_language,
									$allow_markup
					)
				);
			}

			$messages[$key] = $message;
		}
		
		return $messages;
	}
	
	public function getSalutation($user, $lng)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;
		
		// determine salutation
		switch ($user->getGender())
		{
			case 'f':
				$gender_salut = $lng->txt( 'salutation_f' );
				break;
			case 'm':
				$gender_salut = $lng->txt( 'salutation_m' );
				break;
		}
		return $gender_salut;
	}

	private function populatePlaceholder($subject, $code, $content)
	{
		return str_replace($code, $content, $subject);
	}

	/**
	 * @var array<ilObjUser>
	 */
	protected static $userInstances = array();

	/**
	 *
	 * Returns a cached instance of ilObjUser
	 *
	 * @static
	 * @param integer $a_usr_id
	 * @return ilObjUser
	 */
	protected static function getCachedUserInstance($a_usr_id)
	{
		if(isset(self::$userInstances[$a_usr_id]))
		{
			return self::$userInstances[$a_usr_id];
		}

		self::$userInstances[$a_usr_id] = new ilObjUser($a_usr_id);
		return self::$userInstances[$a_usr_id];
	}
}
