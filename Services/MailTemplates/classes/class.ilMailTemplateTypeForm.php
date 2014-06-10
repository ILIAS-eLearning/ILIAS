<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/MailTemplates/classes/Form/class.ilMailTemplateConsumerAdapterLocationInputField.php';

class ilMailTemplateTypeForm
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
			$this->createForm($this->getEmptyFormValues());
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
			$this->createForm($this->getEmptyFormValues());
		}

		return $this->form->getHTML();
	}

	/**
	 * @param array $form_values
	 */
	public function createForm(array $form_values)
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->form = new ilPropertyFormGUI();

		if(isset($_GET['template_id']) && (int)$_GET['template_id'])
		{
			$this->ilCtrl->setParameterByClass('ilMailTemplatesGUI', 'template_id', (int)$_GET['template_id']);
			$this->form->setTitle($this->lng->txt('mail_template_type_edit'));

			$input_id = new ilNonEditableValueGUI($this->lng->txt('mail_template_type_id'), 'template_id');
			$input_id->setValue((int)$_GET['template_id']);
			$this->form->addItem($input_id);
		}
		else
		{
			$this->form->setTitle($this->lng->txt('mail_template_type_create'));
		}

		$this->form->setFormAction($this->ilCtrl->getFormActionByClass('ilMailTemplatesGUI', 'cancel_template_type_settings'));

		$input_category = new ilTextInputGUI($this->lng->txt('mail_template_category_name'), 'mail_template_category_name');
		$input_category->setDisabled(false);
		$input_category->setRequired(true);
		$input_category->setValue($form_values['template_category_name']);
		$this->form->addItem($input_category);

		$input_type = new ilTextInputGUI($this->lng->txt('mail_template_type'), 'mail_template_type');
		$input_type->setDisabled(false);
		$input_type->setRequired(true);
		$input_type->setValue($form_values['template_type']);
		$this->form->addItem($input_type);

		$input_consumerloc = new ilMailTemplateConsumerAdapterLocationInputField($this->lng->txt('mail_template_consumer_location'), 'mail_template_consumer_location');
		$input_consumerloc->setInfo($this->lng->txt('mail_template_consumer_location_info'));
		$input_consumerloc->setDisabled(false);
		$input_consumerloc->setRequired(true);
		$input_consumerloc->setValue($form_values['template_consumer_location']);
		$this->form->addItem($input_consumerloc);

		$this->form->addCommandButton('save_template_type_settings', $this->lng->txt('save_template_type_settings'));
		$this->form->addCommandButton('cancel_template_type_settings', $this->lng->txt('cancel_template_type_settings'));
	}

	/**
	 * @param $a_settings_entity
	 * @return ilPropertyFormGUI|null
	 */
	public function getPopulatedForm($a_settings_entity)
	{
		$this->createForm($this->getFormValuesByEntity($a_settings_entity));
		return $this->form;
	}

	/**
	 * @return array
	 */
	public function getEmptyFormValues()
	{
		$values = array(
			'id'                         => '',
			'template_category_name'     => '',
			'template_type'              => '',
			'template_consumer_location' => ''
		);

		return $values;
	}

	/**
	 * @param ilMailTemplateSettingsEntity $a_settings_entity
	 * @return array
	 */
	public function getFormValuesByEntity(ilMailTemplateSettingsEntity $a_settings_entity)
	{
		$values = array(
			'id'                         => $a_settings_entity->getTemplateTypeId(),
			'template_category_name'     => $a_settings_entity->getTemplateCategoryName(),
			'template_type'              => $a_settings_entity->getTemplateTemplateType(),
			'template_consumer_location' => $a_settings_entity->getTemplateConsumerLocation()
		);

		return $values;
	}
}