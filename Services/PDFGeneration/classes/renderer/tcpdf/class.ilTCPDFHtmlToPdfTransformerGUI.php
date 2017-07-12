<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '//class.ilAbstractHtmlToPdfTransformerGUI.php';

/**
 * Class ilTCPDFHtmlToPdfTransformerGUI
 */
class ilTCPDFHtmlToPdfTransformerGUI extends ilAbstractHtmlToPdfTransformerGUI
{
	protected $page_size;

	protected $is_active;

	/**
	 * ilTCPDFHtmlToPdfTransformerGUI constructor.
	 * @param $lng
	 */
	public function __construct($lng)
	{
		$this->lng = $lng;
	}

	/**
	 * @return ilSetting
	 */
	protected function getSettingObject()
	{
		return new ilSetting('pdf_transformer_tcpdf');
	}

	/**
	 *
	 */
	public function populateForm()
	{
		$pdf_tcpdf_set		= $this->getSettingObject();
		$this->page_size	= $pdf_tcpdf_set->get('page_size', 'A4');
		$this->is_active	= $pdf_tcpdf_set->get('is_active');
	}

	/**
	 *
	 */
	public function saveForm()
	{
		$pdf_tcpdf_set = $this->getSettingObject();
		$pdf_tcpdf_set->set('page_size',		$this->page_size);
		$pdf_tcpdf_set->set('is_active',		$this->is_active);
	}

	/**
	 * @return bool
	 */
	public function checkForm()
	{
		$everything_ok	= true;
		$this->setActiveState(false);
		$this->page_size		= ilUtil::stripSlashes($_POST['page_size']);
		$this->is_active		= (int) $_POST['is_active'];
		return $everything_ok;
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function appendForm(ilPropertyFormGUI $form)
	{
		$form->setTitle($this->lng->txt('tcpdf_config'));
		$active = new ilCheckboxInputGUI($this->lng->txt('is_active'), 'is_active');
		if($this->is_active == true || $this->is_active == 1)
		{
			$active->setChecked(true);
		}
		$form->addItem($active);

		$section_header = new ilFormSectionHeaderGUI();
		$section_header->setTitle($this->lng->txt('page_settings'), 'page_settings');
		$form->addItem($section_header);
		$page_size = new ilSelectInputGUI($this->lng->txt('page_size'), 'page_size');
		$page_size->setOptions(ilPDFGenerationConstants::getPageSizesNames());
		$page_size->setValue($this->page_size);
		$form->addItem($page_size);
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function appendHiddenTransformerNameToForm(ilPropertyFormGUI $form)
	{
		$class = new ilHiddenInputGUI('transformer');
		$class->setValue('ilTCPDFHtmlToPdfTransformer');
		$form->addItem($class);
	}

	/**
	 * @param $state
	 */
	protected function setActiveState($state)
	{
		$pdf_tcpdf_set = $this->getSettingObject();
		$pdf_tcpdf_set->set('is_active', $state);
	}


}