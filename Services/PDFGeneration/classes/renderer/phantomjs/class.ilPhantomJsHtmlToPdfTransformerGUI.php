<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/class.ilAbstractHtmlToPdfTransformerGUI.php';

/**
 * Class ilPhantomJsHtmlToPdfTransformerGUI
 */
class ilPhantomJsHtmlToPdfTransformerGUI extends ilAbstractHtmlToPdfTransformerGUI
{
	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	protected $page_size;

	/**
	 * @var float
	 */
	protected $zoom;

	/**
	 * @var int
	 */
	protected $is_active;

	/**
	 * @var string
	 */
	protected $orientation;

	/**
	 * @var string
	 */
	protected $margin;

	/**
	 * @var int
	 */
	protected $javascript_delay;

	/**
	 * @var int
	 */
	protected $print_media_type;

	/**
	 * @var int
	 */
	protected $header_type;

	/**
	 * @var int
	 */
	protected $footer_type;

	/**
	 * @var string
	 */
	protected $header_text;

	/**
	 * @var string
	 */
	protected $header_height;

	/**
	 * @var bool
	 */
	protected $header_show_pages;

	/**
	 * @var string
	 */
	protected $footer_text;

	/**
	 * @var string
	 */
	protected $footer_height;

	/**
	 * @var bool
	 */
	protected $footer_show_pages;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * ilPhantomJsHtmlToPdfTransformerGUI constructor.
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
		return new ilSetting('pdf_transformer_phantom');
	}

	/**
	 *
	 */
	public function populateForm()
	{
		$pdf_phantom_set		= $this->getSettingObject();
		$this->path				= $pdf_phantom_set->get('path', '/usr/local/bin/phantomjs');
		$this->page_size		= $pdf_phantom_set->get('page_size', 'A4');
		$this->zoom				= $pdf_phantom_set->get('zoom', 1);
		$this->is_active		= $pdf_phantom_set->get('is_active');
		$this->margin			= $pdf_phantom_set->get('margin', '1cm');
		$this->print_media_type	= $pdf_phantom_set->get('print_media_type');
		$this->javascript_delay	= $pdf_phantom_set->get('javascript_delay', 200);
		$this->orientation		= $pdf_phantom_set->get('orientation');
		$this->header_type		= $pdf_phantom_set->get('header_type');
		$this->header_text		= $pdf_phantom_set->get('header_text');
		$this->header_height	= $pdf_phantom_set->get('header_height');
		$this->header_show_pages= $pdf_phantom_set->get('header_show_pages');
		$this->footer_type		= $pdf_phantom_set->get('footer_type');
		$this->footer_text		= $pdf_phantom_set->get('footer_text');
		$this->footer_height	= $pdf_phantom_set->get('footer_height');
		$this->footer_show_pages= $pdf_phantom_set->get('footer_show_pages');
	}

	/**
	 *
	 */
	public function saveForm()
	{
		$pdf_phantom_set = $this->getSettingObject();
		$pdf_phantom_set->set('path', $this->path);
		$pdf_phantom_set->set('page_size', $this->page_size);
		$pdf_phantom_set->set('zoom', $this->zoom);
		$pdf_phantom_set->set('margin', $this->margin);
		$pdf_phantom_set->set('print_media_type', $this->print_media_type);
		$pdf_phantom_set->set('orientation', $this->orientation);
		$pdf_phantom_set->set('javascript_delay', $this->javascript_delay);
		$pdf_phantom_set->set('is_active', $this->is_active);
		$pdf_phantom_set->set('header_type', $this->header_type);
		$pdf_phantom_set->set('header_text', $this->header_text);
		$pdf_phantom_set->set('header_height', $this->header_height);
		$pdf_phantom_set->set('header_show_pages', $this->header_show_pages);
		$pdf_phantom_set->set('footer_type', $this->footer_type);
		$pdf_phantom_set->set('footer_text', $this->footer_text);
		$pdf_phantom_set->set('footer_height', $this->footer_height);
		$pdf_phantom_set->set('footer_show_pages', $this->footer_show_pages);
	}

	/**
	 * @return bool
	 */
	public function checkForm()
	{
		$everything_ok	= true;
		$this->setActiveState(false);
		$this->path 	= ilUtil::stripSlashes($_POST['path']);
		if(mb_stripos($this->path, 'phantomjs') === false)
		{
			ilUtil::sendFailure($this->lng->txt('file_not_found'),true);
			$everything_ok = false;
		}
		else
		{
			$this->page_size		= ilUtil::stripSlashes($_POST['page_size']);
			$this->zoom				= (float) $_POST['zoom'];
			$this->margin			= ilUtil::stripSlashes($_POST['margin']);
			$this->print_media_type	= (int) $_POST['print_media_type'];
			$this->orientation		= ilUtil::stripSlashes($_POST['orientation']);
			$this->javascript_delay	= (int) $_POST['javascript_delay'];
			$this->is_active		= (int) $_POST['is_active'];
			$this->header_type		= (int) $_POST['header_select'];
			$this->header_text		= ilUtil::stripSlashes($_POST['header_text']);
			$this->header_height	= ilUtil::stripSlashes($_POST['header_height']);
			$this->header_show_pages= (int) $_POST['header_show_pages'];
			$this->footer_type		= (int) $_POST['footer_select'];
			$this->footer_text		= ilUtil::stripSlashes($_POST['footer_text']);
			$this->footer_height	= ilUtil::stripSlashes($_POST['footer_height']);
			$this->footer_show_pages= (int) $_POST['footer_show_pages'];
		}

		return $everything_ok;
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function appendForm(ilPropertyFormGUI $form)
	{
		$form->setTitle($this->lng->txt('phantomjs_config'));

		$path = new ilTextInputGUI($this->lng->txt('path'), 'path');
		$path->setValue($this->path);
		$form->addItem($path);

		$active = new ilCheckboxInputGUI($this->lng->txt('is_active'), 'is_active');
		if($this->is_active == true || $this->is_active == 1)
		{
			$active->setChecked(true);
		}
		$form->addItem($active);

		$form->addItem($this->buildJavascriptDelayForm());
		$form->addItem($this->buildPageSettingsHeader());
		$form->addItem($this->buildZoomForm());
		$form->addItem($this->buildMarginForm());
		$form->addItem($this->buildPrintMediaTypeForm());
		$form->addItem($this->buildOrientationForm());
		$form->addItem($this->buildPageSizesForm());

		$header_select	= new ilRadioGroupInputGUI($this->lng->txt('header_type'), 'header_select');
		$header_select->addOption(new ilRadioOption($this->lng->txt('none'), ilPDFGenerationConstants::HEADER_NONE, ''));
		$header_text = new ilRadioOption($this->lng->txt('text'), ilPDFGenerationConstants::HEADER_TEXT, '');
		$header_text->addSubItem($this->buildHeaderTextForm());
		$header_text->addSubItem($this->buildHeaderHeightForm());
		$header_text->addSubItem($this->buildHeaderPageNumbersForm());
		$header_select->addOption($header_text);
		$header_select->setValue($this->header_type);
		$form->addItem($header_select);

		$footer_select	= new ilRadioGroupInputGUI($this->lng->txt('footer_type'), 'footer_select');
		$footer_select->addOption(new ilRadioOption($this->lng->txt('none'), ilPDFGenerationConstants::FOOTER_NONE, ''));
		$footer_text = new ilRadioOption($this->lng->txt('text'), ilPDFGenerationConstants::FOOTER_TEXT, '');
		$footer_text->addSubItem($this->buildFooterTextForm());
		$footer_text->addSubItem($this->buildFooterHeightForm());
		$footer_text->addSubItem($this->buildFooterPageNumbersForm());
		$footer_select->addOption($footer_text);
		$footer_select->setValue($this->footer_type);
		$form->addItem($footer_select);
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildHeaderTextForm()
	{
		$header_text = new ilTextInputGUI($this->lng->txt('head_text'), 'header_text');
		$header_text->setValue($this->header_text);
		return $header_text;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildHeaderHeightForm()
	{
		$header_height = new ilTextInputGUI($this->lng->txt('header_height'), 'header_height');
		$header_height->setValue($this->header_height);
		return $header_height;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildHeaderPageNumbersForm()
	{
		$header_show_pages = new ilCheckboxInputGUI($this->lng->txt('header_show_pages'), 'header_show_pages');
		if($this->header_show_pages == true || $this->header_show_pages == 1)
		{
			$header_show_pages->setChecked(true);
		}
		return $header_show_pages;
	}
	/**
	 * @return ilTextInputGUI
	 */
	protected function buildFooterTextForm()
	{
		$footer_text = new ilTextInputGUI($this->lng->txt('footer_text'), 'footer_text');
		$footer_text->setValue($this->footer_text);
		return $footer_text;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildFooterHeightForm()
	{
		$footer_height = new ilTextInputGUI($this->lng->txt('footer_height'), 'footer_height');
		$footer_height->setValue($this->footer_height);
		return $footer_height;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildFooterPageNumbersForm()
	{
		$footer_show_pages = new ilCheckboxInputGUI($this->lng->txt('footer_show_pages'), 'footer_show_pages');
		if($this->footer_show_pages == true || $this->footer_show_pages == 1)
		{
			$footer_show_pages->setChecked(true);
		}
		return $footer_show_pages;
	}

	/**
	 * @param $state
	 */
	protected function setActiveState($state)
	{
		$pdf_phantom_set = $this->getSettingObject();
		$pdf_phantom_set->set('is_active', $state);
	}


	/**
	 * @return ilSelectInputGUI
	 */
	protected function buildPageSizesForm()
	{
		$page_size = new ilSelectInputGUI($this->lng->txt('page_size'), 'page_size');
		$page_size->setOptions(ilPDFGenerationConstants::getPageSizesNames());
		$page_size->setValue($this->page_size);
		return $page_size;
	}

	/**
	 * @return ilSelectInputGUI
	 */
	protected function buildOrientationForm()
	{
		$orientation = new ilSelectInputGUI($this->lng->txt('orientation'), 'orientation');
		$orientation->setOptions(ilPDFGenerationConstants::getOrientations());
		$orientation->setValue($this->orientation);
		return $orientation;
	}

	/**
	 * @return ilCheckboxInputGUI
	 */
	protected function buildPrintMediaTypeForm()
	{
		$print_media = new ilCheckboxInputGUI($this->lng->txt('print_media_type'), 'print_media_type');
		if($this->print_media_type == 1)
		{
			$print_media->setChecked(true);
			return $print_media;
		}
		return $print_media;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildMarginForm()
	{
		$margin = new ilTextInputGUI($this->lng->txt('margin'), 'margin');
		$margin->setValue($this->margin);
		return $margin;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildZoomForm()
	{
		$zoom = new ilTextInputGUI($this->lng->txt('zoom'), 'zoom');
		$zoom->setValue($this->zoom);
		return $zoom;
	}

	/**
	 * @return ilFormSectionHeaderGUI
	 */
	protected function buildPageSettingsHeader()
	{
		$section_header = new ilFormSectionHeaderGUI();
		$section_header->setTitle($this->lng->txt('page_settings'));
		return $section_header;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildJavascriptDelayForm()
	{
		$javascript_delay = new ilTextInputGUI($this->lng->txt('javascript_delay'), 'javascript_delay');
		$javascript_delay->setValue($this->javascript_delay);
		return $javascript_delay;
	}


	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function appendHiddenTransformerNameToForm(ilPropertyFormGUI $form)
	{
		$class = new ilHiddenInputGUI('transformer');
		$class->setValue('ilPhantomJsHtmlToPdfTransformer');
		$form->addItem($class);
	}

}