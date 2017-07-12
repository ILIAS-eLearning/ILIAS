<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/class.ilAbstractHtmlToPdfTransformerGUI.php';
require_once __DIR__ . '/class.ilPDFGenerationConstants.php';

/**
 * Class ilWebkitHtmlToPdfTransformerGUI
 */
class ilWebkitHtmlToPdfTransformerGUI extends ilAbstractHtmlToPdfTransformerGUI
{
	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var float
	 */
	protected $zoom;

	/**
	 * @var int
	 */
	protected $external_links;

	/**
	 * @var int
	 */
	protected $enable_forms;

	/**
	 * @var string
	 */
	protected $user_stylesheet;

	/**
	 * @var string
	 */
	protected $page_size;

	/**
	 * @var int
	 */
	protected $low_quality;

	/**
	 * @var int
	 */
	protected $is_active;

	/**
	 * @var int
	 */
	protected $grey_scale;

	/**
	 * @var string
	 */
	protected $orientation;

	/**
	 * @var string
	 */
	protected $margin_left;

	/**
	 * @var string
	 */
	protected $margin_right;

	/**
	 * @var string
	 */
	protected $margin_top;

	/**
	 * @var string
	 */
	protected $margin_bottom;

	/**
	 * @var int
	 */
	protected $print_media_type;

	/**
	 * @var int
	 */
	protected $javascript_delay;

	/**
	 * @var int
	 */
	protected $header_select;

	/**
	 * @var string
	 */
	protected $head_text_left;

	/**
	 * @var string
	 */
	protected $head_text_center;

	/**
	 * @var string
	 */
	protected $head_text_right;

	/**
	 * @var int
	 */
	protected $head_text_spacing;

	/**
	 * @var string
	 */
	protected $head_html;

	/**
	 * @var int
	 */
	protected $head_html_spacing;

	/**
	 * @var int
	 */
	protected $head_text_line;

	/**
	 * @var int
	 */
	protected $head_html_line;

	/**
	 * @var int
	 */
	protected $footer_select;

	/**
	 * @var string
	 */
	protected $footer_text_left;

	/**
	 * @var string
	 */
	protected $footer_text_center;

	/**
	 * @var string
	 */
	protected $footer_text_right;

	/**
	 * @var int
	 */
	protected $footer_text_spacing;

	/**
	 * @var int
	 */
	protected $footer_text_line;

	/**
	 * @var int
	 */
	protected $footer_html_line;

	/**
	 * @var string
	 */
	protected $footer_html;

	/**
	 * @var string
	 */
	protected $checkbox_svg;

	/**
	 * @var string
	 */
	protected $checkbox_checked_svg;

	/**
	 * @var string
	 */
	protected $radio_button_svg;

	/**
	 * @var string
	 */
	protected $radio_button_checked_svg;

	/**
	 * @var int
	 */
	protected $footer_html_spacing;

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
		return new ilSetting('pdf_transformer_webkit');
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function appendForm(ilPropertyFormGUI $form)
	{
		$form->setTitle($this->lng->txt('wkhtml_config'));

		$path = new ilTextInputGUI($this->lng->txt('path'), 'path');
		$path->setValue($this->path);
		$form->addItem($path);
		$form->addItem($this->buildIsActiveForm());

		$this->appendOutputOptionsForm($form);
		$this->appendPageSettingsForm($form);
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	protected function appendOutputOptionsForm(ilPropertyFormGUI $form)
	{
		$section_header = new ilFormSectionHeaderGUI();
		$section_header->setTitle($this->lng->txt('output_options'));
		$form->addItem($section_header);

		$form->addItem($this->buildExternalLinksForm());
		$form->addItem($this->buildEnableFormsForm());
		$form->addItem($this->buildUserStylesheetForm());
		$form->addItem($this->buildLowQualityForm());
		$form->addItem($this->buildGreyScaleForm());
		$form->addItem($this->buildPrintMediaTypeForm());
		$form->addItem($this->buildJavascriptDelayForm());
		$form->addItem($this->buildCheckboxSvgForm());
		$form->addItem($this->buildCheckedCheckboxSvgForm());
		$form->addItem($this->buildRadiobuttonSvgForm());
		$form->addItem($this->buildCheckedRadiobuttonSvgForm());
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	protected function appendPageSettingsForm(ilPropertyFormGUI $form)
	{
		$section_header = new ilFormSectionHeaderGUI();
		$section_header->setTitle($this->lng->txt('page_settings'));
		$form->addItem($section_header);

		$form->addItem($this->buildZoomForm());
		$form->addItem($this->buildOrientationsForm());
		$form->addItem($this->buildPageSizesForm());
		$form->addItem($this->buildMarginLeftForm());
		$form->addItem($this->buildMarginRightForm());
		$form->addItem($this->buildMarginTopForm());
		$form->addItem($this->buildMarginBottomForm());
		$form->addItem($this->buildHeaderForm());
		$form->addItem($this->buildFooterForm());
	}

	/**
	 * @return ilRadioGroupInputGUI
	 */
	protected function buildHeaderForm()
	{
		$header_select	= new ilRadioGroupInputGUI($this->lng->txt('header_type'), 'header_select');
		$header_select->addOption(new ilRadioOption($this->lng->txt('none'), ilPDFGenerationConstants::HEADER_NONE, ''));
		$header_select->addOption($this->buildHeaderTextForm());
		$header_select->addOption($this->buildHeaderHtmlForm());

		$header_select->setValue($this->header_select);

		return $header_select;
	}

	/**
	 * @return ilRadioOption
	 */
	protected function buildHeaderTextForm()
	{
		$header_text_option = new ilRadioOption($this->lng->txt('text'), ilPDFGenerationConstants::HEADER_TEXT, '');

		$header_text_left = new ilTextInputGUI($this->lng->txt('header_text_left'), 'head_text_left');
		$header_text_left->setValue($this->head_text_left);
		$header_text_option->addSubItem($header_text_left);

		$header_text_center = new ilTextInputGUI($this->lng->txt('header_text_center'), 'head_text_center');
		$header_text_center->setValue($this->head_text_center);
		$header_text_option->addSubItem($header_text_center);

		$header_text_right = new ilTextInputGUI($this->lng->txt('header_text_right'), 'head_text_right');
		$header_text_right->setValue($this->head_text_right);
		$header_text_option->addSubItem($header_text_right);

		$head_text_spacing = new ilTextInputGUI($this->lng->txt('spacing'), 'head_text_spacing');
		$head_text_spacing->setValue($this->head_text_spacing);
		$header_text_option->addSubItem($head_text_spacing);

		$head_text_line = new ilCheckboxInputGUI($this->lng->txt('header_line'), 'head_text_line');
		if($this->head_text_line == 1)
		{
			$head_text_line->setChecked(true);
		}
		$header_text_option->addSubItem($head_text_line);
		return $header_text_option;
	}

	/**
	 * @return ilRadioOption
	 */
	protected function buildHeaderHtmlForm()
	{
		$header_html_option = new ilRadioOption($this->lng->txt("html"), ilPDFGenerationConstants::HEADER_HTML, '');

		$header_html = new ilTextInputGUI($this->lng->txt('header_html'), 'head_html');
		$header_html->setValue($this->head_html);
		$header_html_option->addSubItem($header_html);

		$head_html_spacing = new ilTextInputGUI($this->lng->txt('spacing'), 'head_html_spacing');
		$head_html_spacing->setValue($this->head_html_spacing);
		$header_html_option->addSubItem($head_html_spacing);

		$head_html_line = new ilCheckboxInputGUI($this->lng->txt('header_line'), 'head_html_line');
		if($this->head_html_line == 1)
		{
			$head_html_line->setChecked(true);
		}
		$header_html_option->addSubItem($head_html_line);
		return $header_html_option;
	}

	/**
	 * @return ilCheckboxInputGUI
	 */
	protected function buildIsActiveForm()
	{
		$active = new ilCheckboxInputGUI($this->lng->txt('is_active'), 'is_active');
		if($this->is_active == true || $this->is_active == 1)
		{
			$active->setChecked(true);
			return $active;
		}
		return $active;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildMarginBottomForm()
	{
		$margin_bottom = new ilTextInputGUI($this->lng->txt('margin_bottom'), 'margin_bottom');
		$margin_bottom->setValue($this->margin_bottom);
		return $margin_bottom;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildMarginTopForm()
	{
		$margin_top = new ilTextInputGUI($this->lng->txt('margin_top'), 'margin_top');
		$margin_top->setValue($this->margin_top);
		return $margin_top;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildMarginRightForm()
	{
		$margin_right = new ilTextInputGUI($this->lng->txt('margin_right'), 'margin_right');
		$margin_right->setValue($this->margin_right);
		return $margin_right;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildMarginLeftForm()
	{
		$margin_left = new ilTextInputGUI($this->lng->txt('margin_left'), 'margin_left');
		$margin_left->setValue($this->margin_left);
		return $margin_left;
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
	protected function buildOrientationsForm()
	{
		$orientation = new ilSelectInputGUI($this->lng->txt('orientation'), 'orientation');
		$orientation->setOptions(ilPDFGenerationConstants::getOrientations());
		$orientation->setValue($this->orientation);
		return $orientation;
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
	 * @return ilTextInputGUI
	 */
	protected function buildCheckedRadiobuttonSvgForm()
	{
		$radio_button_checked_svg = new ilTextInputGUI($this->lng->txt('radio_button_checked_svg'), 'radio_button_checked_svg');
		$radio_button_checked_svg->setValue($this->radio_button_checked_svg);
		return $radio_button_checked_svg;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildRadiobuttonSvgForm()
	{
		$radio_button_svg = new ilTextInputGUI($this->lng->txt('radio_button_svg'), 'radio_button_svg');
		$radio_button_svg->setValue($this->radio_button_svg);
		return $radio_button_svg;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildCheckedCheckboxSvgForm()
	{
		$checkbox_checked_svg = new ilTextInputGUI($this->lng->txt('checkbox_checked_svg'), 'checkbox_checked_svg');
		$checkbox_checked_svg->setValue($this->checkbox_checked_svg);
		return $checkbox_checked_svg;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildCheckboxSvgForm()
	{
		$checkbox_svg = new ilTextInputGUI($this->lng->txt('checkbox_svg'), 'checkbox_svg');
		$checkbox_svg->setValue($this->checkbox_svg);
		return $checkbox_svg;
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
	 * @return ilCheckboxInputGUI
	 */
	protected function buildGreyScaleForm()
	{
		$grey_scale = new ilCheckboxInputGUI($this->lng->txt('greyscale'), 'greyscale');
		if($this->grey_scale == 1)
		{
			$grey_scale->setChecked(true);
			return $grey_scale;
		}
		return $grey_scale;
	}

	/**
	 * @return ilCheckboxInputGUI
	 */
	protected function buildLowQualityForm()
	{
		$low_quality = new ilCheckboxInputGUI($this->lng->txt('low_quality'), 'low_quality');
		if($this->low_quality == 1)
		{
			$low_quality->setChecked(true);
			return $low_quality;
		}
		return $low_quality;
	}

	/**
	 * @return ilTextInputGUI
	 */
	protected function buildUserStylesheetForm()
	{
		$user_stylesheet = new ilTextInputGUI($this->lng->txt('user_stylesheet'), 'user_stylesheet');
		$user_stylesheet->setValue($this->user_stylesheet);
		return $user_stylesheet;
	}

	/**
	 * @return ilCheckboxInputGUI
	 */
	protected function buildEnableFormsForm()
	{
		$enable_forms = new ilCheckboxInputGUI($this->lng->txt('enable_forms'), 'enable_forms');
		if($this->enable_forms == 1)
		{
			$enable_forms->setChecked(true);
			return $enable_forms;
		}
		return $enable_forms;
	}

	/**
	 * @return ilCheckboxInputGUI
	 */
	protected function buildExternalLinksForm()
	{
		$external_links = new ilCheckboxInputGUI($this->lng->txt('external_links'), 'external_links');
		if($this->external_links == 1)
		{
			$external_links->setChecked(true);
			return $external_links;
		}
		return $external_links;
	}

	/**
	 * @return ilRadioGroupInputGUI
	 */
	protected function buildFooterForm()
	{
		$footer_select	= new ilRadioGroupInputGUI($this->lng->txt('footer_type'), 'footer_select');
		$footer_select->addOption(new ilRadioOption($this->lng->txt("none"), ilPDFGenerationConstants::FOOTER_NONE, ''));
		$footer_select->addOption($this->buildFooterTextForm());
		$footer_select->addOption($this->buildFooterHtmlForm());

		$footer_select->setValue($this->footer_select);

		return $footer_select;
	}


	/**
	 * @return ilRadioOption
	 */
	protected function buildFooterHtmlForm()
	{
		$footer_html_option = new ilRadioOption($this->lng->txt('html'), ilPDFGenerationConstants::FOOTER_HTML, '');

		$footer_html = new ilTextInputGUI($this->lng->txt('footer_html'), 'footer_html');
		$footer_html->setValue($this->footer_html);
		$footer_html_option->addSubItem($footer_html);

		$footer_html_spacing = new ilTextInputGUI($this->lng->txt('spacing'), 'footer_html_spacing');
		$footer_html_spacing->setValue($this->footer_html_spacing);
		$footer_html_option->addSubItem($footer_html_spacing);

		$footer_html_line = new ilCheckboxInputGUI($this->lng->txt('footer_line'), 'footer_html_line');
		if($this->footer_html_line == 1)
		{
			$footer_html_line->setChecked(true);
		}
		$footer_html_option->addSubItem($footer_html_line);
		return $footer_html_option;
	}

	/**
	 * @return ilRadioOption
	 */
	protected function buildFooterTextForm()
	{
		$footer_text_option = new ilRadioOption($this->lng->txt('text'), ilPDFGenerationConstants::FOOTER_TEXT, '');

		$footer_text_left = new ilTextInputGUI($this->lng->txt('footer_text_left'), 'footer_text_left');
		$footer_text_left->setValue($this->footer_text_left);
		$footer_text_option->addSubItem($footer_text_left);

		$footer_text_center = new ilTextInputGUI($this->lng->txt('footer_text_center'), 'footer_text_center');
		$footer_text_center->setValue($this->footer_text_center);
		$footer_text_option->addSubItem($footer_text_center);

		$footer_text_right = new ilTextInputGUI($this->lng->txt('footer_text_right'), 'footer_text_right');
		$footer_text_right->setValue($this->footer_text_right);
		$footer_text_option->addSubItem($footer_text_right);

		$footer_text_spacing = new ilTextInputGUI($this->lng->txt('spacing'), 'footer_text_spacing');
		$footer_text_spacing->setValue($this->footer_text_spacing);
		$footer_text_option->addSubItem($footer_text_spacing);

		$footer_text_line = new ilCheckboxInputGUI($this->lng->txt('footer_line'), 'footer_text_line');
		if($this->footer_text_line == 1)
		{
			$footer_text_line->setChecked(true);
		}
		$footer_text_option->addSubItem($footer_text_line);
		return $footer_text_option;
	}

	public function populateForm()
	{
		$pdf_webkit_set					= $this->getSettingObject();
		$this->path						= $pdf_webkit_set->get('path',						'/usr/local/bin/wkhtmltopdf');
		$this->zoom						= $pdf_webkit_set->get('zoom',						1);
		$this->external_links			= $pdf_webkit_set->get('external_links');
		$this->enable_forms				= $pdf_webkit_set->get('enable_forms');
		$this->user_stylesheet			= $pdf_webkit_set->get('user_stylesheet');
		$this->low_quality				= $pdf_webkit_set->get('low_quality');
		$this->grey_scale				= $pdf_webkit_set->get('greyscale');
		$this->orientation				= $pdf_webkit_set->get('orientation');
		$this->page_size				= $pdf_webkit_set->get('page_size',					'A4');
		$this->margin_left				= $pdf_webkit_set->get('margin_left',				'0.5cm');
		$this->margin_right				= $pdf_webkit_set->get('margin_right',				'0.5cm');
		$this->margin_top				= $pdf_webkit_set->get('margin_top',				'0.5cm');
		$this->margin_bottom			= $pdf_webkit_set->get('margin_bottom',				'0.5cm');
		$this->print_media_type			= $pdf_webkit_set->get('print_media_type');
		$this->javascript_delay			= $pdf_webkit_set->get('javascript_delay',			200);
		$this->checkbox_svg				= $pdf_webkit_set->get('checkbox_svg',				'');
		$this->checkbox_checked_svg		= $pdf_webkit_set->get('checkbox_checked_svg',		'');
		$this->radio_button_svg			= $pdf_webkit_set->get('radio_button_svg',			'');
		$this->radio_button_checked_svg	= $pdf_webkit_set->get('radio_button_checked_svg',	'');
		$this->header_select			= $pdf_webkit_set->get('header_select',				ilPDFGenerationConstants::HEADER_NONE);
		$this->head_text_left			= $pdf_webkit_set->get('head_text_left',			'');
		$this->head_text_center			= $pdf_webkit_set->get('head_text_center',			'');
		$this->head_text_right			= $pdf_webkit_set->get('head_text_right',			'');
		$this->head_text_spacing		= $pdf_webkit_set->get('head_text_spacing',			1);
		$this->head_text_line			= $pdf_webkit_set->get('head_text_line',			0);
		$this->head_html_line			= $pdf_webkit_set->get('head_html_line',			0);
		$this->head_html_spacing		= $pdf_webkit_set->get('head_html_spacing',			1);
		$this->head_html				= $pdf_webkit_set->get('head_html',					'');
		$this->footer_select			= $pdf_webkit_set->get('footer_select',				ilPDFGenerationConstants::HEADER_NONE);
		$this->footer_text_left			= $pdf_webkit_set->get('footer_text_left',			'');
		$this->footer_text_center		= $pdf_webkit_set->get('footer_text_center',		'');
		$this->footer_text_right		= $pdf_webkit_set->get('footer_text_right',			'');
		$this->footer_text_spacing		= $pdf_webkit_set->get('footer_text_spacing',		1);
		$this->footer_text_line			= $pdf_webkit_set->get('footer_text_line',			0);
		$this->footer_html_line			= $pdf_webkit_set->get('footer_html_line',			0);
		$this->footer_html_spacing		= $pdf_webkit_set->get('footer_html_spacing',		1);
		$this->footer_html				= $pdf_webkit_set->get('footer_html',				'');
		$this->is_active				= $pdf_webkit_set->get('is_active');
	}

	public function saveForm()
	{
		$pdf_webkit_set = $this->getSettingObject();
		$pdf_webkit_set->set('path', 					$this->path);
		$pdf_webkit_set->set('zoom',					$this->zoom);
		$pdf_webkit_set->set('external_links',			$this->external_links);
		$pdf_webkit_set->set('enable_forms',			$this->enable_forms);
		$pdf_webkit_set->set('user_stylesheet',			$this->user_stylesheet);
		$pdf_webkit_set->set('low_quality',				$this->low_quality);
		$pdf_webkit_set->set('greyscale',				$this->grey_scale);
		$pdf_webkit_set->set('orientation',				$this->orientation);
		$pdf_webkit_set->set('page_size',				$this->page_size);
		$pdf_webkit_set->set('margin_left',				$this->margin_left);
		$pdf_webkit_set->set('margin_right',			$this->margin_right);
		$pdf_webkit_set->set('margin_top',				$this->margin_top);
		$pdf_webkit_set->set('margin_bottom',			$this->margin_bottom);
		$pdf_webkit_set->set('print_media_type',		$this->print_media_type);
		$pdf_webkit_set->set('javascript_delay',		$this->javascript_delay);
		$pdf_webkit_set->set('checkbox_svg',			$this->checkbox_svg);
		$pdf_webkit_set->set('checkbox_checked_svg',	$this->checkbox_checked_svg);
		$pdf_webkit_set->set('radio_button_svg',		$this->radio_button_svg);
		$pdf_webkit_set->set('radio_button_checked_svg',$this->radio_button_checked_svg);
		$pdf_webkit_set->set('header_select',			$this->header_select);
		$pdf_webkit_set->set('head_text_left',			$this->head_text_left);
		$pdf_webkit_set->set('head_text_center',		$this->head_text_center);
		$pdf_webkit_set->set('head_text_right',			$this->head_text_right);
		$pdf_webkit_set->set('head_text_spacing',		$this->head_text_spacing);
		$pdf_webkit_set->set('head_text_line',			$this->head_text_line);
		$pdf_webkit_set->set('head_html_line',			$this->head_html_line);
		$pdf_webkit_set->set('head_html_spacing',		$this->head_html_spacing);
		$pdf_webkit_set->set('head_html',				$this->head_html);
		$pdf_webkit_set->set('footer_select',			$this->footer_select);
		$pdf_webkit_set->set('footer_text_left',		$this->footer_text_left);
		$pdf_webkit_set->set('footer_text_center',		$this->footer_text_center);
		$pdf_webkit_set->set('footer_text_right',		$this->footer_text_right);
		$pdf_webkit_set->set('footer_text_spacing',		$this->footer_text_spacing);
		$pdf_webkit_set->set('footer_text_spacing',		$this->footer_text_spacing);
		$pdf_webkit_set->set('footer_text_line',		$this->footer_text_line);
		$pdf_webkit_set->set('footer_html_line',		$this->footer_html_line);
		$pdf_webkit_set->set('footer_html',				$this->footer_html);
		$pdf_webkit_set->set('is_active',				$this->is_active);
	}

	/**
	 * @return bool
	 */
	public function checkForm()
	{
		$everything_ok	= true;
		$this->setActiveState(false);
		$this->path 	= ilUtil::stripSlashes($_POST['path']);
		if(mb_stripos($this->path, 'wkhtmlto') === false)
		{
			ilUtil::sendFailure($this->lng->txt("file_not_found"),true);
			$everything_ok = false;
		}
		else
		{
			$this->zoom						= (float) $_POST['zoom'];
			$this->external_links			= (int) $_POST['external_links'];
			$this->enable_forms				= (int) $_POST['enable_forms'];
			$this->user_stylesheet			= ilUtil::stripSlashes($_POST['user_stylesheet']);
			$this->low_quality				= (int) $_POST['low_quality'];
			$this->grey_scale				= (int) $_POST['greyscale'];
			$this->orientation				= ilUtil::stripSlashes($_POST['orientation']);
			$this->page_size				= ilUtil::stripSlashes($_POST['page_size']);
			$this->margin_left				= ilUtil::stripSlashes($_POST['margin_left']);
			$this->margin_right				= ilUtil::stripSlashes($_POST['margin_right']);
			$this->margin_top				= ilUtil::stripSlashes($_POST['margin_top']);
			$this->margin_bottom			= ilUtil::stripSlashes($_POST['margin_bottom']);
			$this->print_media_type			= (int) $_POST['print_media_type'];
			$this->javascript_delay			= (int) $_POST['javascript_delay'];
			$this->checkbox_svg				= ilUtil::stripSlashes($_POST['checkbox_svg']);
			$this->checkbox_checked_svg		= ilUtil::stripSlashes($_POST['checkbox_checked_svg']);
			$this->radio_button_svg			= ilUtil::stripSlashes($_POST['radio_button_svg']);
			$this->radio_button_checked_svg	= ilUtil::stripSlashes($_POST['radio_button_checked_svg']);
			$this->header_select			= (int) $_POST['header_select'];
			$this->head_text_left			= ilUtil::stripSlashes($_POST['head_text_left']);
			$this->head_text_center			= ilUtil::stripSlashes($_POST['head_text_center']);
			$this->head_text_right			= ilUtil::stripSlashes($_POST['head_text_right']);
			$this->head_text_spacing		= (int) $_POST['head_text_spacing'];
			$this->head_text_line			= (int) $_POST['head_text_line'];
			$this->head_html_line			= (int) $_POST['head_html_line'];
			$this->head_html_spacing		= (int) $_POST['head_html_spacing'];
			$this->head_html				= ilUtil::stripSlashes($_POST['head_html']);
			$this->footer_select			= (int) $_POST['footer_select'];
			$this->footer_text_left			= ilUtil::stripSlashes($_POST['footer_text_left']);
			$this->footer_text_center		= ilUtil::stripSlashes($_POST['footer_text_center']);
			$this->footer_text_right		= ilUtil::stripSlashes($_POST['footer_text_right']);
			$this->footer_text_spacing		= (int) $_POST['footer_text_spacing'];
			$this->footer_text_line			= (int) $_POST['footer_text_line'];
			$this->footer_html_spacing		= (int) $_POST['footer_html_line'];
			$this->footer_html_line			= (int) $_POST['footer_html_spacing'];
			$this->footer_html				= ilUtil::stripSlashes($_POST['footer_html']);
			$this->is_active				= (int) $_POST['is_active'];
		}

		return $everything_ok;
	}

	/**
	 * @param $state
	 */
	protected function setActiveState($state)
	{
		$pdf_webkit_set = $this->getSettingObject();
		$pdf_webkit_set->set('is_active', $state);
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function appendHiddenTransformerNameToForm(ilPropertyFormGUI $form)
	{
		$class = new ilHiddenInputGUI('transformer');
		$class->setValue('ilWebkitHtmlToPdfTransformer');
		$form->addItem($class);
	}
}