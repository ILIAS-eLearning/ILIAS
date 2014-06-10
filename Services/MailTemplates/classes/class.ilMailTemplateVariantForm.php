<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/MailTemplates/classes/class.ilMailTemplateHtmlPurifier.php';

class ilMailTemplateVariantForm
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form = null;

	/**
	 * @var ilCtrl
	 */
	protected $ilCtrl;

	/**
	 * @var ilDB
	 */
	protected $ilDB;

	/**
	 * @param $a_lng
	 * @param $a_ilCtrl
	 * @param $a_ilDB
	 */
	public function __construct(ilLanguage $a_lng, ilCtrl $a_ilCtrl, ilDB $a_ilDB)
	{
		$this->lng    = $a_lng;
		$this->ilCtrl = $a_ilCtrl;
		$this->ilDB   = $a_ilDB;
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

		$this->form->setFormAction($this->ilCtrl->getLinkTargetByClass('ilMailTemplatesGUI', 'edit_template_variant'));

		$input_id = new ilTextInputGUI($this->lng->txt('mail_template_variant_id'), 'mail_template_variant_id');
		$input_id->setDisabled(true);
		$input_id->setValue($form_values['id']);
		$this->form->addItem($input_id);

		// This element has to be enabled for the second level of implementation (mulit-lang)
		$input_fi = new ilTextInputGUI($this->lng->txt('mail_types_fi'), 'mail_types_fi');
		$input_fi->setDisabled(true);
		$input_fi->setValue($form_values['mail_types_fi']);
		$this->form->addItem($input_fi);

		// This element has to be enabled for the second level of implementation (mulit-lang)
		$input_lng = new ilTextInputGUI($this->lng->txt('mail_types_language'), 'mail_types_language');
		$input_lng->setDisabled(true);
		$input_lng->setValue($form_values['language']);
		$this->form->addItem($input_lng);

		$input_subject = new ilTextInputGUI($this->lng->txt('mail_message_subject'), 'mail_message_subject');
		$input_subject->setDisabled(false);
		$input_subject->setValue($form_values['message_subject']);
		$this->form->addItem($input_subject);

		$purifier = new ilMailTemplateHtmlPurifier();

		$input_plain = new ilTextAreaInputGUI($this->lng->txt('mail_message_plain'), 'mail_message_plain');
		$input_plain->setDisabled(false);
		$input_plain->setCols(86);
		$input_plain->setRows(20);
		$input_plain->setUseRte(false);
		$input_plain->usePurifier(true);
		$input_plain->setPurifier($purifier);
		$input_plain->setValue($form_values['message_plain']);
		$this->form->addItem($input_plain);
		
		require_once './Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php';
		$template_settings = new ilMailTemplateSettingsEntity();
		$template_settings->setIlDB($this->ilDB);
		$template_settings->loadById($form_values['mail_types_fi']);
		$template_adapter =	$template_settings->getAdapterClassInstance();
		
		require_once 'Services/MailTemplates/classes/Form/class.ilMailTemplatePlaceholdersPropertyGUI.php';
		$prop = new ilMailTemplatePlaceholdersPropertyGUI($input_plain, $template_adapter);
		$prop->addPlaceHolder('SALUTATION', $this->lng->txt('mail_nacc_salutation'));
		$prop->addPlaceHolder('FIRST_NAME', $this->lng->txt('firstname'));
		$prop->addPlaceHolder('LAST_NAME', $this->lng->txt('lastname'));
		$prop->addPlaceHolder('LOGIN', $this->lng->txt('mail_nacc_login'));
		$prop->addPlaceHolder('ILIAS_URL', $this->lng->txt('mail_nacc_ilias_url'));
		$prop->addPlaceHolder('CLIENT_NAME', $this->lng->txt('mail_nacc_client_name'));
		$this->form->addItem($prop);

		$input_html = new ilTextAreaInputGUI($this->lng->txt('mail_message_html'), 'mail_message_html');
		$input_html->setDisabled(false);
		$input_html->setCols(100);
		$input_html->setRows(20);
		$input_html->setUseRte(true);
		$input_html->usePurifier(true);
		$input_html->setPurifier($purifier);
		$input_html->setValue($form_values['message_html']);
		$this->form->addItem($input_html);

		$prop2 = new ilMailTemplatePlaceholdersPropertyGUI($input_html, $template_adapter);
		$prop2->addPlaceHolder('SALUTATION', $this->lng->txt('mail_nacc_salutation'));
		$prop2->addPlaceHolder('FIRST_NAME', $this->lng->txt('firstname'));
		$prop2->addPlaceHolder('LAST_NAME', $this->lng->txt('lastname'));
		$prop2->addPlaceHolder('LOGIN', $this->lng->txt('mail_nacc_login'));
		$prop2->addPlaceHolder('ILIAS_URL', $this->lng->txt('mail_nacc_ilias_url'));
		$prop2->addPlaceHolder('CLIENT_NAME', $this->lng->txt('mail_nacc_client_name'));
		$this->form->addItem($prop2);

		$input_created = new ilNonEditableValueGUI($this->lng->txt('mail_message_created'), 'mail_message_created');
		$input_created->setValue(
			(int)$form_values['created_date'] ? ilDatePresentation::formatDate(new ilDateTime($form_values['created_date'], IL_CAL_UNIX)) : '-'
		);
		$this->form->addItem($input_created);

		$input_updated = new ilNonEditableValueGUI($this->lng->txt('mail_message_updated'), 'mail_message_updated');
		$input_updated->setValue(
			(int)$form_values['update_date'] ? ilDatePresentation::formatDate(new ilDateTime($form_values['update_date'], IL_CAL_UNIX)) : '-'
		);
		$this->form->addItem($input_updated);

		$input_update_usr = new ilNonEditableValueGUI($this->lng->txt('mail_message_updated_usr'), 'mail_message_updated_usr');
		$input_update_usr->setValue(
			(int)$form_values['updated_usr_fi'] ? ilObjUser::_lookupLogin($form_values['updated_usr_fi']).' ('.$form_values['updated_usr_fi'].')' : '-'
		);
		$this->form->addItem($input_update_usr);

		$input_active = new ilCheckboxInputGUI($this->lng->txt('mail_message_active'), 'mail_message_active');
		$input_active->setDisabled(false);
		$input_active->setChecked($form_values['template_active']);
		$this->form->addItem($input_active);

		$this->form->addCommandButton('save_template_variant', $this->lng->txt('save_template_variant'));
		$this->form->addCommandButton('save_and_sample_variant', $this->lng->txt('save_and_sample_variant'));
		$this->form->addCommandButton('cancel_template_variant', $this->lng->txt('cancel_template_variant'));
	}

	/**
	 * @param $a_variant_entity
	 * @return ilPropertyFormGUI|null
	 */
	public function getPopulatedForm($a_variant_entity)
	{
		$this->createForm($this->getFormValuesByEntity($a_variant_entity));
		return $this->form;
	}

	/**
	 * @return array
	 */
	public function getEmptyFormValues()
	{
		$values = array(
			'id'              => '',
			'mail_types_fi'   => '',
			'language'        => '',
			'message_subject' => '',
			'message_plain'   => '',
			'message_html'    => '',
			'created_date'    => '',
			'updated_date'    => '',
			'updated_usr_fi'  => '',
			'template_active' => ''
		);

		return $values;
	}

	/**
	 * @param ilMailTemplateVariantEntity $a_variant_entity
	 * @return array
	 */
	public function getFormValuesByEntity(ilMailTemplateVariantEntity $a_variant_entity)
	{
		$values = array(
			'id'              => $a_variant_entity->getId(),
			'mail_types_fi'   => $a_variant_entity->getMailTypesFi(),
			'language'        => $a_variant_entity->getLanguage(),
			'message_subject' => $a_variant_entity->getMessageSubject(),
			'message_plain'   => $a_variant_entity->getMessagePlain(),
			'message_html'    => $a_variant_entity->getMessageHtml(),
			'created_date'    => $a_variant_entity->getCreatedDate(),
			'updated_date'    => $a_variant_entity->getUpdatedDate(),
			'updated_usr_fi'  => $a_variant_entity->getUpdatedUsrFi(),
			'template_active' => $a_variant_entity->getTemplateActive()
		);

		return $values;
	}

	/**
	 * @param ilCtrl $ilCtrl
	 */
	public function setIlCtrl($ilCtrl)
	{
		$this->ilCtrl = $ilCtrl;
	}

	/**
	 * @return ilCtrl
	 */
	public function getIlCtrl()
	{
		return $this->ilCtrl;
	}

	/**
	 * @param ilDB $ilDB
	 */
	public function setIlDB($ilDB)
	{
		$this->ilDB = $ilDB;
	}

	/**
	 * @return ilDB
	 */
	public function getIlDB()
	{
		return $this->ilDB;
	}

	/**
	 * @param ilLanguage $lng
	 */
	public function setLng($lng)
	{
		$this->lng = $lng;
	}

	/**
	 * @return ilLanguage
	 */
	public function getLng()
	{
		return $this->lng;
	}
}
