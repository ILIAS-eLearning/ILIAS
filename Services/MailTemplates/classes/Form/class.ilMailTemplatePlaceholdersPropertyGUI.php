<?php

include_once 'Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php';

class ilMailTemplatePlaceholdersPropertyGUI extends ilSubEnabledFormPropertyGUI
{
	/**
	 * @var array
	 */
	protected $placeholders = array();

	/**
	 * @var ilTextAreaInputGUI
	 */
	protected $textbox;

	/**
	 * @var null|ilMailTypeAdapter
	 */
	protected $additional_placeholder_instance = null;

	/**
	 * @param ilTextAreaInputGUI $textbox
	 * @param ilMailTypeAdapter  $a_additional_placeholder_instance
	 */
	public function __construct(ilTextAreaInputGUI $textbox, ilMailTypeAdapter $a_additional_placeholder_instance = null)
	{
		parent::__construct('', '');
		
		$this->textbox                         = $textbox;
		$this->additional_placeholder_instance = $a_additional_placeholder_instance;
	}

	/**
	 * @param string $place_holder
	 * @param string $description
	 */
	public function  addPlaceHolder($place_holder, $description)
	{
		$this->placeholders[] = array($place_holder, $description);
	}

	/**
	 * @param array $placeholders
	 */
	public function setPlaceholders($placeholders)
	{
		$this->placeholders = $placeholders;
	}

	/**
	 * @return array
	 */
	public function getPlaceholders()
	{
		return $this->placeholders;
	}
	
	/**
	 * Has to be implemented... (don't know why this isn't defined by an interface...)
	 * @param array $data
	 */
	public function setValueByArray($data)
	{
	}

	/**
	 * Return always true
	 * @return bool
	 */
	public function checkInput()
	{
		return true;
	}

	/**
	 * @param ilTemplate $a_tpl
	 */
	public function insert(ilTemplate $a_tpl)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;
		
		$tpl = new ilTemplate('tpl.mail_templates_frame_placerholder_input.html', true, true, 'Services/MailTemplates');
		$tpl->setVariable('ID', $this->textbox->getFieldId());
		$tpl->setVariable('TXT_USE_PLACEHOLDERS', $lng->txt('mail_templates_use_placeholder'));

		foreach($this->getPlaceholders() as $placeholder)
		{
			$tpl->setCurrentBlock('loop');
			$tpl->setVariable('LOOP_PLACEHOLDER', $placeholder[0]);
			$tpl->setVariable('LOOP_DESCRIPTION', $placeholder[1]);
			$tpl->parseCurrentBlock();
		}

		if ($this->additional_placeholder_instance != null)
		{
			$additional_placeholders = $this->additional_placeholder_instance->getPlaceholdersLocalized();
			foreach ($additional_placeholders as $placeholder)
			{
				$tpl->setCurrentBlock('loop');
				$tpl->setVariable('LOOP_PLACEHOLDER', $placeholder['placeholder_code']);
				$tpl->setVariable('LOOP_DESCRIPTION', $placeholder['placeholder_description']);
				$tpl->parseCurrentBlock();
			}
		}

		$a_tpl->setCurrentBlock('prop_generic');
		$a_tpl->setVariable('PROP_GENERIC', $tpl->get());
		$a_tpl->parseCurrentBlock();
	}
} 