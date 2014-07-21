<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Services/MailTemplates/classes/Form/class.ilMailTemplatePlaceholdersPropertyGUI.php';
require_once 'Services/MailTemplates/classes/class.ilMailTemplateHtmlPurifier.php';

/**
 * Class ilMailTemplateFrameForm
 */
class ilMailTemplateFrameForm
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilPropertyFormGUI|null
	 */
	protected $form = null;

	/**
	 * @var ilCtrl
	 */
	protected $ilCtrl;

	/**
	 * @param ilLanguage $a_lng
	 * @param ilCtrl     $a_ilCtrl
	 */
	public function __construct(ilLanguage $a_lng, ilCtrl $a_ilCtrl)
	{
		$this->lng    = $a_lng;
		$this->ilCtrl = $a_ilCtrl;
	}

	/**
	 * @return ilPropertyFormGUI|null
	 */
	public function getForm()
	{
		if(!$this->form)
		{
			$this->createForm();
		}

		return $this->form;
	}

	/**
	 * @return string
	 */
	public function getHTML()
	{
		if(!$this->form)
		{
			$this->createForm();
		}

		return $this->form->getHTML();
	}

	/**
	 * 
	 */
	public function createForm()
	{
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ilCtrl->getFormActionByClass('ilMailTemplatesGUI', 'save_frame_settings'));
		$this->form->setTitle($this->lng->txt('mail_template_frame_settings'));

		$purifier = new ilMailTemplateHtmlPurifier();

		$input_plain = new ilTextAreaInputGUI($this->lng->txt('mail_template_frame_plain'), 'mail_template_frame_plain');
		$input_plain->setCols(86);
		$input_plain->setRows(20);
		$input_plain->usePurifier(true);
		$input_plain->setPurifier($purifier);
		$this->form->addItem($input_plain);

		$prop = new ilMailTemplatePlaceholdersPropertyGUI($input_plain);
		$prop->addPlaceHolder('CONTENT', $this->lng->txt('mail_template_placeholder_content'));
		$input_plain->addSubItem($prop);

		$input_html = new ilTextAreaInputGUI($this->lng->txt('mail_template_frame_html'), 'mail_message_html');
		$input_html->setDisabled(false);
		$input_html->setCols(100);
		$input_html->setRows(20);
		$input_html->setUseRte(true);
		$input_html->usePurifier(true);
		$input_html->setPurifier($purifier);
		$input_html->removePlugin('ilimgupload');
		$input_html->disableButtons(array('ilimgupload'));
		$input_html->setRteTagSet('extended');

		$prop = new ilMailTemplatePlaceholdersPropertyGUI($input_html);
		$prop->addPlaceHolder('CONTENT', $this->lng->txt('mail_template_placeholder_content'));
		$prop->addPlaceHolder('IMAGE', $this->lng->txt('mail_template_placeholder_image'));
		$input_html->addSubItem($prop);

		$this->form->addItem($input_html);

		$footer_image = new ilImageFileInputGUI($this->lng->txt('mail_template_footer_image'), 'mail_template_footer_image');
		$footer_image->setALlowDeletion(true);
		$this->form->addItem($footer_image);

		$footer_image_attributes = new ilTextInputGUI($this->lng->txt('mail_template_footer_image_attributes'), 'mail_template_footer_image_attributes');
		$footer_image_attributes->setInfo($this->lng->txt('mail_template_footer_image_attributes_info'));
		$this->form->addItem($footer_image_attributes);

		$this->form->addCommandButton('save_frame_settings', $this->lng->txt('save'));
	}

	/**
	 * @param ilMailTemplateFrameSettingsEntity $a_settings_entity
	 * @return ilPropertyFormGUI|null
	 */
	public function getPopulatedForm(ilMailTemplateFrameSettingsEntity $a_settings_entity)
	{
		$this->createForm();
		$this->form->setValuesByArray($this->getFormValuesByEntity($a_settings_entity));
		if($a_settings_entity->doesImageExist())
		{
			$this->form->getItemByPostVar('mail_template_footer_image')->setImage($a_settings_entity->getFileSystemBasePath() . '/' . $a_settings_entity->getImageName());
		}
		return $this->form;
	}

	/**
	 * @return array
	 */
	public function getEmptyFormValues()
	{
		$values = array(
			'mail_template_frame_plain'             => '',
			'mail_message_html'                     => '',
			'mail_template_footer_image'            => '',
			'mail_template_footer_image_attributes' => ''
		);

		return $values;
	}

	/**
	 * @param ilMailTemplateFrameSettingsEntity $a_settings_entity
	 * @return array
	 */
	public function getFormValuesByEntity(ilMailTemplateFrameSettingsEntity $a_settings_entity)
	{
		$values = array(
			'mail_template_frame_plain'             => $a_settings_entity->getPlainTextFrame(),
			'mail_message_html'                     => $a_settings_entity->getHtmlFrame(),
			'mail_template_footer_image'            => $a_settings_entity->getImageName(),
			'mail_template_footer_image_attributes' => $a_settings_entity->getImageStyles()
		);

		return $values;
	}
}