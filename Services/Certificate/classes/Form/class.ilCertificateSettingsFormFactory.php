<?php


class ilCertificateSettingsFormFactory
{
	/**
	 * @var ilLanguage
	 */
	private $language;

	/**
	 * @var ilTemplate
	 */
	private $template;

	/**
	 * @var ilCtrl
	 */
	private $controller;

	/**
	 * @var ilAccess
	 */
	private $access;

	/**
	 * @var ilToolbarGUI
	 */
	private $toolbar;

	/**
	 * @param ilLanguage $language
	 * @param ilTemplate $template
	 * @param ilCtrl $controller
	 * @param ilAccess $access
	 * @param ilToolbarGUI $toolbar
	 */
	public function __construct(
		ilLanguage $language,
		ilTemplate $template,
		ilCtrl $controller,
		ilAccess $access,
		ilToolbarGUI $toolbar
	) {
		$this->language   = $language;
		$this->template   = $template;
		$this->controller = $controller;
		$this->access     = $access;
		$this->toolbar    = $toolbar;
	}

	/**
	 * @param $objId
	 * @param array $form_fields
	 * @return ilPropertyFormGUI
	 * @throws ilWACException
	 */
	public function create($objId, $certificateGUI, $certificateObject, $form_fields)
	{
		$command = $this->controller->getCmd();

		$form = new ilPropertyFormGUI();
		$form->setPreventDoubleSubmission(false);
		$form->setFormAction($this->controller->getFormAction($certificateGUI));
		$form->setTitle($this->language->txt("cert_form_sec_availability"));
		$form->setMultipart(TRUE);
		$form->setTableWidth("100%");
		$form->setId("certificate");

		$active = new ilCheckboxInputGUI($this->language->txt("active"), "active");
		$active->setChecked($form_fields["active"]);
		$form->addItem($active);

		$import = new ilFileInputGUI($this->language->txt("import"), "certificate_import");
		$import->setRequired(FALSE);
		$import->setSuffixes(array("zip"));
		// handle the certificate import
		if (strlen($_FILES["certificate_import"]["name"]))
		{
			if ($import->checkInput())
			{
				$result = $this->object->importCertificate($_FILES["certificate_import"]["tmp_name"], $_FILES["certificate_import"]["name"]);
				if ($result == FALSE)
				{
					$import->setAlert($this->language->txt("certificate_error_import"));
				}
				else
				{
					$this->controller->redirect($certificateGUI, "certificateEditor");
				}
			}
		}
		$form->addItem($import);

		$formSection = new \ilFormSectionHeaderGUI();
		$formSection->setTitle($this->language->txt("cert_form_sec_layout"));
		$form->addItem($formSection);

		$pageformat  = new ilRadioGroupInputGUI($this->language->txt("certificate_page_format"), "pageformat");
		$pageformats = $certificateObject->getPageFormats();
		$pageformat->setValue($form_fields["pageformat"]);

		foreach($pageformats as $format) {
			$option = new ilRadioOption($format["name"], $format["value"]);

			if(strcmp($format["value"], "custom") == 0) {
				$pageheight = new ilTextInputGUI($this->language->txt("certificate_pageheight"), "pageheight");
				$pageheight->setValue($form_fields["pageheight"]);
				$pageheight->setSize(6);
				$pageheight->setValidationRegexp('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*)))(cm|mm|in|pt|pc|px|em)$/is');
				$pageheight->setInfo($this->language->txt("certificate_unit_description"));
				$pageheight->setRequired(true);
				$option->addSubitem($pageheight);

				$pagewidth = new ilTextInputGUI($this->language->txt("certificate_pagewidth"), "pagewidth");
				$pagewidth->setValue($form_fields["pagewidth"]);
				$pagewidth->setSize(6);
				$pagewidth->setValidationRegexp('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*)))(cm|mm|in|pt|pc|px|em)$/is');
				$pagewidth->setInfo($this->language->txt("certificate_unit_description"));
				$pagewidth->setRequired(true);
				$option->addSubitem($pagewidth);
			}

			$pageformat->addOption($option);
		}

		$pageformat->setRequired(true);

		if (strcmp($command, "certificateSave") == 0) {
			$pageformat->checkInput();
		}

		$form->addItem($pageformat);

		$bgimage = new ilImageFileInputGUI($this->language->txt("certificate_background_image"), "background");
		$bgimage->setRequired(FALSE);
		$bgimage->setUseCache(false);

		if (!$certificateObject->hasBackgroundImage()) {
			if (ilObjCertificateSettingsAccess::hasBackgroundImage()) {
				ilWACSignedPath::setTokenMaxLifetimeInSeconds(15);
				$bgimage->setImage(ilWACSignedPath::signFile(ilObjCertificateSettingsAccess::getBackgroundImageThumbPathWeb()));
			}
		}
		else {
			ilWACSignedPath::setTokenMaxLifetimeInSeconds(15);
			$bgimage->setImage(ilWACSignedPath::signFile($certificateObject->getBackgroundImageThumbPathWeb()));
		}

		$form->addItem($bgimage);


		$rect = new ilCSSRectInputGUI($this->language->txt("certificate_margin_body"), "margin_body");
		$rect->setRequired(TRUE);
		$rect->setUseUnits(TRUE);
		$rect->setTop($form_fields["margin_body_top"]);
		$rect->setBottom($form_fields["margin_body_bottom"]);
		$rect->setLeft($form_fields["margin_body_left"]);
		$rect->setRight($form_fields["margin_body_right"]);
		$rect->setInfo($this->language->txt("certificate_unit_description"));

		if (strcmp($command, "certificateSave") == 0) {
			$rect->checkInput();
		}

		$form->addItem($rect);

		$certificate = new ilTextAreaInputGUI($this->language->txt("certificate_text"), "certificate_text");
		$certificate->removePlugin('ilimgupload');
		$certificate->setValue($form_fields["certificate_text"]);
		$certificate->setRequired(TRUE);
		$certificate->setRows(20);
		$certificate->setCols(80);

		// fraunhpatch start
		$common_desc_tpl = new ilTemplate(
			"tpl.common_desc.html",
			true,
			true,
			"Services/Certificate"
		);

		foreach (ilCertificate::getCustomCertificateFields() as $field) {
			$common_desc_tpl->setCurrentBlock("cert_field");
			$common_desc_tpl->setVariable("PH", $field["ph"]);
			$common_desc_tpl->setVariable("PH_TXT", $field["name"]);
			$common_desc_tpl->parseCurrentBlock();
		}
		$common_desc = $common_desc_tpl->get();
		// fraunhpatch start

		$certificate->setInfo($certificateObject->getAdapter()->getCertificateVariablesDescription() . $common_desc);
		$certificate->setUseRte(TRUE, '3.4.7');

		$tags = array(
			"br",
			"em",
			"font",
			"li",
			"ol",
			"p",
			"span",
			"strong",
			"u",
			"ul"
		);

		$certificate->setRteTags($tags);

		if (strcmp($command, "certificateSave") == 0) {
			$certificate->checkInput();
		}

		$form->addItem($certificate);

		if ($certificateObject->getAdapter()->hasAdditionalFormElements()) {
			$formSection = new \ilFormSectionHeaderGUI();
			$formSection->setTitle($this->language->txt("cert_form_sec_add_features"));
			$form->addItem($formSection);
		}

		$certificateObject->getAdapter()->addAdditionalFormElements($form, $form_fields);

		if($this->access->checkAccess("writewrite", "", $_GET["ref_id"])) {
			if ($certificateObject->isComplete() || $certificateObject->hasBackgroundImage()) {
				$this->toolbar->setFormAction($this->controller->getFormAction($certificateGUI));

				$preview = ilSubmitButton::getInstance();
				$preview->setCaption('certificate_preview');
				$preview->setCommand('certificatePreview');
				$this->toolbar->addStickyItem($preview);

				$export = ilSubmitButton::getInstance();
				$export->setCaption('certificate_export');
				$export->setCommand('certificateExportFO');
				$this->toolbar->addButtonInstance($export);

				$delete = ilSubmitButton::getInstance();
				$delete->setCaption('delete');
				$delete->setCommand('certificateDelete');
				$this->toolbar->addButtonInstance($delete);
			}
			$form->addCommandButton("certificateSave", $this->language->txt("save"));
		}

		return $form;
	}
}
