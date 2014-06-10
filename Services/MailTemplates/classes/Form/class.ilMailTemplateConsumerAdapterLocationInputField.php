<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilTextInputGUI.php';

class ilMailTemplateConsumerAdapterLocationInputField extends ilTextInputGUI
{
	/**
	 * @return bool
	 */
	public function checkInput()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;
		
		$valid = parent::checkInput();

		if(!$valid)
		{
			return false;
		}
		
		$value = $_POST[$this->getPostVar()];
		
		if(!is_file($value) || !is_readable($value))
		{
			$this->setAlert($lng->txt('mail_template_consumer_not_exists'));
			return false;
		}

		$filename = basename($value);
		$filename_parts = explode('.', $filename);
		if(!count($filename_parts) == 3 || $filename_parts[0] != 'class' || $filename_parts[2] != 'php')
		{
			$this->setAlert($lng->txt('mail_template_consumer_invalid_file_or_classname'));
			return false;
		}

		include_once $value;
		
		$classname = $filename_parts[1];
		if(!class_exists($classname))
		{
			$this->setAlert($lng->txt('mail_template_consumer_invalid_file_or_classname'));
			return false;
		}

		return true;
	}
}
