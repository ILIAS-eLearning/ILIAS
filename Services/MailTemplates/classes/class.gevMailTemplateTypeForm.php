<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/MailTemplates/classes/class.ilMailTemplateTypeForm.php");

class gevMailTemplateTypeForm extends ilMailTemplateTypeForm {
	public function createForm(array $form_values) {
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
		
		require_once("Services/Form/classes/class.ilSelectInputGUI.php");
		$inputtype = new ilSelectInputGUI( $this->lng->txt("mail_template_type")
										 , "mail_template_type");
		$inputtype->setOptions($this->getAvailableTypeOptions());
		$inputtype->setRequired(true);
		$inputtype->setValue($form_values["template_type"]);
		$this->form->addItem($inputtype);
		
		$input_category = new ilTextInputGUI($this->lng->txt('mail_template_category_name')
											, 'mail_template_category_name');
		$input_category->setDisabled(false);
		$input_category->setRequired(true);
		$input_category->setValue($form_values['template_category_name']);
		$this->form->addItem($input_category);
		
		$this->form->addCommandButton('save_template_type_settings', $this->lng->txt('save_template_type_settings'));
		$this->form->addCommandButton('cancel_template_type_settings', $this->lng->txt('cancel_template_type_settings'));
	}
	
	public function getEmptyFormValues() {
		return array( "id"						=> ""
					, "template_type"			=> "crs_invitation"
					, "template_category_name"	=> ""
					);
	}
	
	public function getFormValuesByEntity(ilMailTemplateSettingsEntity $a_settings_entity)
	{
		$values = array(
			'id'                         => $a_settings_entity->getTemplateTypeId(),
			'template_category_name'     => $a_settings_entity->getTemplateCategoryName(),
			'template_type'              => $this->mapTemplateTypeNameToTypeInput(
												$a_settings_entity->getTemplateTemplateType()
											)
		);

		return $values;
	}
	
	protected function getAvailableTypeOptions() {
		return array( "crs_invitation" => "Einladungsmail für Training"
					, "crs_auto" => "automatische Mail für Trainings"
					, "registration" => "Mails während der Makler-Registrierung"
					, "na_registration" => "Mails während der NA-Registrierung"
					, "orgu_superior_mail" => "Mails für Führungskräfte"
					, "decentral_training_creation" => "Mails bei der Anlage von dezentralen Trainings"
					, "webinar" => "Mails für Webinare"
					);
	}
	
	protected function mapTemplateTypeNameToTypeInput($a_name) {
		switch ($a_name) {
			case "CrsInv":
				return "crs_invitation";
			case "CrsMail":
				return "crs_auto";
			case "Agentregistration":
				return "registration";
			case "NA-Registration":
				return "na_registration";
			case "OrguSuperiorMail":
				return "orgu_superior_mail";
			case "DecentralTrainingCreation":
				return "decentral_training_creation";
			case "WebinarMail":
				return "webinar";
			default:
				throw new Exception("gevMailTemplateTypeForm::mapTemplateTypeNameToTypeInput: unknown type: '".$a_name."'");
		}
	}
	
	protected function mapTemplateTypeInputToTypeName($a_name) {
		switch ($a_name) {
			case "crs_invitation":
				return "CrsInv";
			case "crs_auto":
				return "CrsMail";
			case "registration":
				return "Agentregistration";
			case "na_registration":
				return "NA-Registration";
			case "orgu_superior_mail":
				return "OrguSuperiorMail";
			case "decentral_training_creation":
				return "DecentralTrainingCreation";
			case "webinar":
				return "WebinarMail";
			default:
				throw new Exception("gevMailTemplateTypeForm::mapTemplateTypeInputToTypeName: unknown input: '".$a_name."'");
		}
	}
	
	protected function mapTemplateTypeInputToConsumerLocation($a_name) {
		switch ($a_name) {
			case "crs_invitation":
				return "Services/GEV/Mailing/classes/class.gevCrsMailTypeAdapter.php";
			case "crs_auto":
				return "Services/GEV/Mailing/classes/class.gevCrsMailTypeAdapter.php";
			case "registration":
				return "Services/GEV/Mailing/classes/class.gevRegistrationMailTypeAdapter.php";
			case "na_registration":
				return "Services/GEV/Mailing/classes/class.gevNARegistrationMailTypeAdapter.php";
			case "orgu_superior_mail":
				return "Services/GEV/Mailing/classes/class.gevOrguSuperiorMailTypeAdapter.php";
			case "decentral_training_creation":
				return "Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingMailTypeAdapter.php";
			case "webinar":
				return "Services/GEV/Mailing/classes/class.gevWebinarTypeAdapter.php";
			default:
				throw new Exception("gevMailTemplateTypeForm::mapTemplateTypeInputToConsumerLocation: unknown input: '".$a_name."'");
		}
	}
	
	public function getCategoryName() {
		return $this->getForm()->getInput("mail_template_category_name");
	}

	public function getConsumerLocation() {
		$val = $this->getForm()->getInput("mail_template_type");
		return $this->mapTemplateTypeInputToConsumerLocation($val);
	}
	
	public function getTemplateType() {
		$val = $this->getForm()->getInput("mail_template_type");
		return $this->mapTemplateTypeInputToTypeName($val);
	}
}