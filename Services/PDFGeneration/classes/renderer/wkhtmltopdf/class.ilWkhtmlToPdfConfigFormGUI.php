<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

class ilWkhtmlToPdfConfigFormGUI
{
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $main_tpl;
    private ilPDFGenerationRequest $request;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->request = new ilPDFGenerationRequest($DIC->refinery(), $DIC->http());
    }

    public function addConfigForm(ilPropertyFormGUI $form): void
    {
        $path = new ilTextInputGUI($this->translate('path'), 'path');
        $form->addItem($path);

        $this->appendOutputOptionsForm($form);
        $this->appendPageSettingsForm($form);
    }

    protected function translate(string $txt): string
    {
        return $this->lng->txt($txt);
    }

    protected function appendOutputOptionsForm(ilPropertyFormGUI $form): void
    {
        $section_header = new ilFormSectionHeaderGUI();
        $section_header->setTitle($this->translate('output_options'));
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
        $form->addItem($this->buildOverwriteDefaultFont());
    }

    protected function buildExternalLinksForm(): ilCheckboxInputGUI
    {
        $external_links = new ilCheckboxInputGUI($this->translate('external_links'), 'external_links');
        $external_links->setInfo($this->translate('external_links_info'));
        return $external_links;
    }

    protected function buildEnableFormsForm(): ilCheckboxInputGUI
    {
        $enable_forms = new ilCheckboxInputGUI($this->translate('enable_forms'), 'enable_forms');
        $enable_forms->setInfo($this->translate('enable_forms_info'));
        return $enable_forms;
    }

    protected function buildUserStylesheetForm(): ilTextInputGUI
    {
        $user_stylesheet = new ilTextInputGUI($this->translate('user_stylesheet'), 'user_stylesheet');
        return $user_stylesheet;
    }

    protected function buildLowQualityForm(): ilCheckboxInputGUI
    {
        $low_quality = new ilCheckboxInputGUI($this->translate('low_quality'), 'low_quality');
        $low_quality->setInfo($this->translate('low_quality_info'));
        return $low_quality;
    }

    protected function buildGreyScaleForm(): ilCheckboxInputGUI
    {
        $grey_scale = new ilCheckboxInputGUI($this->translate('greyscale'), 'greyscale');
        $grey_scale->setInfo($this->translate('greyscale_info'));
        return $grey_scale;
    }

    protected function buildPrintMediaTypeForm(): ilCheckboxInputGUI
    {
        $print_media = new ilCheckboxInputGUI($this->translate('print_media_type'), 'print_media_type');
        $print_media->setInfo($this->translate('print_media_info'));
        return $print_media;
    }

    protected function buildJavascriptDelayForm(): ilTextInputGUI
    {
        $javascript_delay = new ilTextInputGUI($this->translate('javascript_delay'), 'javascript_delay');
        $javascript_delay->setInfo($this->translate('javascript_delay_info'));
        return $javascript_delay;
    }

    protected function buildCheckboxSvgForm(): ilTextInputGUI
    {
        return new ilTextInputGUI($this->translate('checkbox_svg'), 'checkbox_svg');
    }

    protected function buildCheckedCheckboxSvgForm(): ilTextInputGUI
    {
        return new ilTextInputGUI($this->translate('checkbox_checked_svg'), 'checkbox_checked_svg');
    }

    protected function buildRadiobuttonSvgForm(): ilTextInputGUI
    {
        return new ilTextInputGUI($this->translate('radio_button_svg'), 'radio_button_svg');
    }

    protected function buildCheckedRadiobuttonSvgForm(): ilTextInputGUI
    {
        return new ilTextInputGUI($this->translate('radio_button_checked_svg'), 'radio_button_checked_svg');
    }

    protected function buildOverwriteDefaultFont(): ilTextInputGUI
    {
        $overwrite_font = new ilTextInputGUI($this->translate('overwrite_font'), 'overwrite_font');
        $overwrite_font->setInfo($this->translate('overwrite_font_info'));
        return $overwrite_font;
    }

    protected function appendPageSettingsForm(ilPropertyFormGUI $form): void
    {
        $section_header = new ilFormSectionHeaderGUI();
        $section_header->setTitle($this->translate('page_settings'));
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

    protected function buildZoomForm(): ilTextInputGUI
    {
        return new ilTextInputGUI($this->translate('zoom'), 'zoom');
    }

    protected function buildOrientationsForm(): ilSelectInputGUI
    {
        $orientation = new ilSelectInputGUI($this->translate('orientation'), 'orientation');
        $orientation->setOptions(ilPDFGenerationConstants::getOrientations());
        return $orientation;
    }

    protected function buildPageSizesForm(): ilSelectInputGUI
    {
        $page_size = new ilSelectInputGUI($this->translate('page_size'), 'page_size');
        $page_size->setOptions(ilPDFGenerationConstants::getPageSizesNames());
        return $page_size;
    }

    protected function buildMarginLeftForm(): ilTextInputGUI
    {
        $margin_left = new ilTextInputGUI($this->translate('margin_left'), 'margin_left');
        $margin_left->setInfo($this->translate('margin_info'));
        return $margin_left;
    }

    protected function buildMarginRightForm(): ilTextInputGUI
    {
        $margin_right = new ilTextInputGUI($this->translate('margin_right'), 'margin_right');
        $margin_right->setInfo($this->translate('margin_info'));
        return $margin_right;
    }

    protected function buildMarginTopForm(): ilTextInputGUI
    {
        $margin_top = new ilTextInputGUI($this->translate('margin_top'), 'margin_top');
        $margin_top->setInfo($this->translate('margin_info'));
        return $margin_top;
    }

    protected function buildMarginBottomForm(): ilTextInputGUI
    {
        $margin_bottom = new ilTextInputGUI($this->translate('margin_bottom'), 'margin_bottom');
        $margin_bottom->setInfo($this->translate('margin_info'));
        return $margin_bottom;
    }

    protected function buildHeaderForm(): ilRadioGroupInputGUI
    {
        $header_select = new ilRadioGroupInputGUI($this->translate('header_type'), 'header_select');
        $header_select->addOption(new ilRadioOption(
            $this->translate('none'),
            (string) ilPDFGenerationConstants::HEADER_NONE,
            ''
        ));
        $header_select->addOption($this->buildHeaderTextForm());
        $header_select->addOption($this->buildHeaderHtmlForm());

        return $header_select;
    }

    protected function buildHeaderTextForm(): ilRadioOption
    {
        $header_text_option = new ilRadioOption(
            $this->translate('text'),
            (string) ilPDFGenerationConstants::HEADER_TEXT,
            ''
        );

        $header_text_left = new ilTextInputGUI($this->translate('header_text_left'), 'head_text_left');
        $header_text_option->addSubItem($header_text_left);

        $header_text_center = new ilTextInputGUI($this->translate('header_text_center'), 'head_text_center');
        $header_text_option->addSubItem($header_text_center);

        $header_text_right = new ilTextInputGUI($this->translate('header_text_right'), 'head_text_right');
        $header_text_option->addSubItem($header_text_right);

        $head_text_spacing = new ilTextInputGUI($this->translate('spacing'), 'head_text_spacing');
        $header_text_option->addSubItem($head_text_spacing);

        $head_text_line = new ilCheckboxInputGUI($this->translate('header_line'), 'head_text_line');

        $header_text_option->addSubItem($head_text_line);
        return $header_text_option;
    }

    protected function buildHeaderHtmlForm(): ilRadioOption
    {
        $header_html_option = new ilRadioOption(
            $this->translate("html"),
            (string) ilPDFGenerationConstants::HEADER_HTML,
            ''
        );

        $header_html = new ilTextInputGUI($this->translate('html'), 'head_html');
        $header_html_option->addSubItem($header_html);

        $head_html_spacing = new ilTextInputGUI($this->translate('spacing'), 'head_html_spacing');
        $header_html_option->addSubItem($head_html_spacing);

        $head_html_line = new ilCheckboxInputGUI($this->translate('header_line'), 'head_html_line');
        $header_html_option->addSubItem($head_html_line);
        return $header_html_option;
    }

    protected function buildFooterForm(): ilRadioGroupInputGUI
    {
        $footer_select = new ilRadioGroupInputGUI($this->translate('footer_type'), 'footer_select');
        $footer_select->addOption(new ilRadioOption(
            $this->translate("none"),
            (string) ilPDFGenerationConstants::FOOTER_NONE,
            ''
        ));
        $footer_select->addOption($this->buildFooterTextForm());
        $footer_select->addOption($this->buildFooterHtmlForm());

        return $footer_select;
    }

    protected function buildFooterTextForm(): ilRadioOption
    {
        $footer_text_option = new ilRadioOption(
            $this->translate('text'),
            (string) ilPDFGenerationConstants::FOOTER_TEXT,
            ''
        );

        $footer_text_left = new ilTextInputGUI($this->translate('footer_text_left'), 'footer_text_left');
        $footer_text_option->addSubItem($footer_text_left);

        $footer_text_center = new ilTextInputGUI($this->translate('footer_text_center'), 'footer_text_center');
        $footer_text_option->addSubItem($footer_text_center);

        $footer_text_right = new ilTextInputGUI($this->translate('footer_text_right'), 'footer_text_right');
        $footer_text_option->addSubItem($footer_text_right);

        $footer_text_spacing = new ilTextInputGUI($this->translate('spacing'), 'footer_text_spacing');
        $footer_text_option->addSubItem($footer_text_spacing);

        $footer_text_line = new ilCheckboxInputGUI($this->translate('footer_line'), 'footer_text_line');

        $footer_text_option->addSubItem($footer_text_line);
        return $footer_text_option;
    }

    protected function buildFooterHtmlForm(): ilRadioOption
    {
        $footer_html_option = new ilRadioOption(
            $this->translate('html'),
            (string) ilPDFGenerationConstants::FOOTER_HTML,
            ''
        );

        $footer_html = new ilTextInputGUI($this->translate('footer_html'), 'footer_html');
        $footer_html_option->addSubItem($footer_html);

        $footer_html_spacing = new ilTextInputGUI($this->translate('spacing'), 'footer_html_spacing');
        $footer_html_option->addSubItem($footer_html_spacing);

        $footer_html_line = new ilCheckboxInputGUI($this->translate('footer_line'), 'footer_html_line');
        $footer_html_option->addSubItem($footer_html_line);
        return $footer_html_option;
    }

    public function validateForm(): bool
    {
        $everything_ok = true;
        $config = new ilWkhtmlToPdfConfig();
        $config->setPath($this->request->securedString('path'));
        if (mb_stripos($config->getPath(), 'wkhtmlto') === false) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("file_not_found"), true);
            $everything_ok = false;
        } else {
            $config->setZoom($this->request->float('zoom'));
            $config->setExternalLinks($this->request->bool('external_links'));
            $config->setEnabledForms($this->request->bool('enable_forms'));
            $config->setUserStylesheet($this->request->securedString('user_stylesheet'));
            $config->setLowQuality($this->request->bool('low_quality'));
            $config->setGreyscale($this->request->bool('greyscale'));
            $config->setOrientation($this->request->securedString('orientation'));
            $config->setPageSize($this->request->securedString('page_size'));
            $config->setMarginLeft($this->request->securedString('margin_left'));
            $config->setMarginRight($this->request->securedString('margin_right'));
            $config->setMarginTop($this->request->securedString('margin_top'));
            $config->setMarginBottom($this->request->securedString('margin_bottom'));
            $config->setPrintMediaType($this->request->bool('print_media_type'));
            $config->setJavascriptDelay($this->request->int('javascript_delay'));
            $config->setCheckboxSvg($this->request->securedString('checkbox_svg'));
            $config->setCheckboxCheckedSvg($this->request->securedString('checkbox_checked_svg'));
            $config->setRadioButtonSvg($this->request->securedString('radio_button_svg'));
            $config->setRadioButtonCheckedSvg($this->request->securedString('radio_button_checked_svg'));
            $config->setHeaderType($this->request->int('header_select'));
            $config->setHeaderTextLeft($this->request->securedString('head_text_left'));
            $config->setHeaderTextCenter($this->request->securedString('head_text_center'));
            $config->setHeaderTextRight($this->request->securedString('head_text_right'));
            $config->setHeaderTextSpacing($this->request->int('head_text_spacing'));
            $config->setHeaderTextLine($this->request->bool('head_text_line'));
            $config->setHeaderHtmlLine($this->request->bool('head_html_line'));
            $config->setHeaderHtmlSpacing($this->request->int('head_html_spacing'));
            $config->setHeaderHtml($this->request->securedString('head_html'));
            $config->setFooterType($this->request->int('footer_select'));
            $config->setFooterTextLeft($this->request->securedString('footer_text_left'));
            $config->setFooterTextCenter($this->request->securedString('footer_text_center'));
            $config->setFooterTextRight($this->request->securedString('footer_text_right'));
            $config->setFooterTextSpacing($this->request->int('footer_text_spacing'));
            $config->setFooterTextLine($this->request->bool('footer_text_line'));
            $config->setFooterHtmlLine($this->request->bool('footer_html_line'));
            $config->setFooterHtmlSpacing($this->request->int('footer_html_spacing'));
            $config->setFooterHtml($this->request->securedString('footer_html'));
            $config->setOverwriteDefaultFont($this->request->securedString('overwrite_font'));
            $this->saveNewDefaultBinaryPath($config->getPath());
        }

        return $everything_ok;
    }

    protected function saveNewDefaultBinaryPath(string $path): void
    {
        $settings = new ilSetting('wkhtmltopdfrenderer');
        $settings->set('path', $path);
    }

    /**
     * @param ilPropertyFormGUI $form
     * @return array<string, mixed>
     */
    public function getConfigFromForm(ilPropertyFormGUI $form): array
    {
        return [
            'path' => $form->getItemByPostVar('path')->getValue(),
            'zoom' => $form->getItemByPostVar('zoom')->getValue(),
            'external_links' => $form->getItemByPostVar('external_links')->getChecked(),
            'enable_forms' => $form->getItemByPostVar('enable_forms')->getChecked(),
            'user_stylesheet' => $form->getItemByPostVar('user_stylesheet')->getValue(),
            'low_quality' => $form->getItemByPostVar('low_quality')->getChecked(),
            'greyscale' => $form->getItemByPostVar('greyscale')->getChecked(),
            'orientation' => $form->getItemByPostVar('orientation')->getValue(),
            'page_size' => $form->getItemByPostVar('page_size')->getValue(),
            'margin_left' => $form->getItemByPostVar('margin_left')->getValue(),
            'margin_right' => $form->getItemByPostVar('margin_right')->getValue(),
            'margin_top' => $form->getItemByPostVar('margin_top')->getValue(),
            'margin_bottom' => $form->getItemByPostVar('margin_bottom')->getValue(),
            'print_media_type' => $form->getItemByPostVar('print_media_type')->getChecked(),
            'javascript_delay' => $form->getItemByPostVar('javascript_delay')->getValue(),
            'checkbox_svg' => $form->getItemByPostVar('checkbox_svg')->getValue(),
            'checkbox_checked_svg' => $form->getItemByPostVar('checkbox_checked_svg')->getValue(),
            'radio_button_svg' => $form->getItemByPostVar('radio_button_svg')->getValue(),
            'radio_button_checked_svg' => $form->getItemByPostVar('radio_button_checked_svg')->getValue(),
            'header_select' => $form->getItemByPostVar('header_select')->getValue(),
            'head_text_left' => $form->getItemByPostVar('head_text_left')->getValue(),
            'head_text_center' => $form->getItemByPostVar('head_text_center')->getValue(),
            'head_text_right' => $form->getItemByPostVar('head_text_right')->getValue(),
            'head_text_spacing' => $form->getItemByPostVar('head_text_spacing')->getValue(),
            'head_text_line' => $form->getItemByPostVar('head_text_line')->getValue(),
            'head_html_line' => $form->getItemByPostVar('head_html_line')->getValue(),
            'head_html_spacing' => $form->getItemByPostVar('head_html_spacing')->getValue(),
            'head_html' => $form->getItemByPostVar('head_html')->getValue(),
            'footer_select' => $form->getItemByPostVar('footer_select')->getValue(),
            'footer_text_left' => $form->getItemByPostVar('footer_text_left')->getValue(),
            'footer_text_right' => $form->getItemByPostVar('footer_text_right')->getValue(),
            'footer_text_spacing' => $form->getItemByPostVar('footer_text_spacing')->getValue(),
            'footer_text_center' => $form->getItemByPostVar('footer_text_center')->getValue(),
            'footer_text_line' => $form->getItemByPostVar('footer_text_line')->getValue(),
            'footer_html' => $form->getItemByPostVar('footer_html')->getValue(),
            'footer_html_spacing' => $form->getItemByPostVar('footer_html_spacing')->getValue(),
            'overwrite_font' => $form->getItemByPostVar('overwrite_font')->getValue()
        ];
    }

    public function populateForm(ilPropertyFormGUI $form, ilWkhtmlToPdfConfig $config): void
    {
        $form->getItemByPostVar('path')->setValue($config->getWKHTMLToPdfDefaultPath());
        $form->getItemByPostVar('zoom')->setValue($config->getZoom());
        $form->getItemByPostVar('external_links')->setValue("1");
        $form->getItemByPostVar('external_links')->setChecked($config->getExternalLinks());
        $form->getItemByPostVar('enable_forms')->setValue("1");
        $form->getItemByPostVar('enable_forms')->setChecked($config->getEnabledForms());
        $form->getItemByPostVar('user_stylesheet')->setValue($config->getUserStylesheet());
        $form->getItemByPostVar('low_quality')->setValue("1");
        $form->getItemByPostVar('low_quality')->setChecked($config->getLowQuality());
        $form->getItemByPostVar('greyscale')->setValue("1");
        $form->getItemByPostVar('greyscale')->setChecked($config->getGreyscale());
        $form->getItemByPostVar('orientation')->setValue($config->getOrientation());
        $form->getItemByPostVar('page_size')->setValue($config->getPageSize());
        $form->getItemByPostVar('margin_left')->setValue($config->getMarginLeft());
        $form->getItemByPostVar('margin_right')->setValue($config->getMarginRight());
        $form->getItemByPostVar('margin_top')->setValue($config->getMarginTop());
        $form->getItemByPostVar('margin_bottom')->setValue($config->getMarginBottom());
        $form->getItemByPostVar('print_media_type')->setValue("1");
        $form->getItemByPostVar('print_media_type')->setChecked($config->getPrintMediaType());
        $form->getItemByPostVar('javascript_delay')->setValue($config->getJavascriptDelay());
        $form->getItemByPostVar('checkbox_svg')->setValue($config->getCheckboxSvg());
        $form->getItemByPostVar('checkbox_checked_svg')->setValue($config->getCheckboxCheckedSvg());
        $form->getItemByPostVar('radio_button_svg')->setValue($config->getRadioButtonSvg());
        $form->getItemByPostVar('radio_button_checked_svg')->setValue($config->getRadioButtonCheckedSvg());
        $form->getItemByPostVar('header_select')->setValue((string) $config->getHeaderType());
        $form->getItemByPostVar('head_text_left')->setValue($config->getHeaderTextLeft());
        $form->getItemByPostVar('head_text_center')->setValue($config->getHeaderTextCenter());
        $form->getItemByPostVar('head_text_right')->setValue($config->getHeaderTextRight());
        $form->getItemByPostVar('head_text_spacing')->setValue($config->getHeaderTextSpacing());
        $form->getItemByPostVar('head_text_line')->setValue("1");
        $form->getItemByPostVar('head_text_line')->setChecked($config->isHeaderTextLine());
        $form->getItemByPostVar('head_html_line')->setValue("1");
        $form->getItemByPostVar('head_html_line')->setChecked($config->isHeaderHtmlLine());
        $form->getItemByPostVar('head_html_spacing')->setValue($config->getHeaderHtmlSpacing());
        $form->getItemByPostVar('head_html')->setValue($config->getHeaderHtml());
        $form->getItemByPostVar('footer_select')->setValue((string) $config->getFooterType());
        $form->getItemByPostVar('footer_text_left')->setValue($config->getFooterTextLeft());
        $form->getItemByPostVar('footer_text_center')->setValue($config->getFooterTextCenter());
        $form->getItemByPostVar('footer_text_right')->setValue($config->getFooterTextRight());
        $form->getItemByPostVar('footer_text_spacing')->setValue($config->getFooterTextSpacing());
        $form->getItemByPostVar('footer_text_line')->setValue("1");
        $form->getItemByPostVar('footer_text_line')->setChecked($config->isFooterTextLine());
        $form->getItemByPostVar('footer_html_line')->setValue("1");
        $form->getItemByPostVar('footer_html_line')->setChecked($config->isFooterHtmlLine());
        $form->getItemByPostVar('footer_html')->setValue($config->getFooterHtml());
        $form->getItemByPostVar('footer_html_spacing')->setValue($config->getFooterHtmlSpacing());
        $form->getItemByPostVar('overwrite_font')->setValue($config->getOverwriteDefaultFont(false));

        ilPDFGeneratorUtils::setCheckedIfTrue($form);
    }
}
