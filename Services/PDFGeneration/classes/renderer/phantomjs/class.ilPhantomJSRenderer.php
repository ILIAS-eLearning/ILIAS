<?php

require_once './Services/PDFGeneration/classes/class.ilPDFGenerationConstants.php';
require_once './Services/PDFGeneration/interfaces/interface.ilRendererConfig.php';
require_once './Services/PDFGeneration/interfaces/interface.ilPDFRenderer.php';

class ilPhantomJSRenderer implements ilRendererConfig, ilPDFRenderer
{
    const PAGE = 0;
    const VIEWPORT = 1;

    /** @var ilLanguage $lng */
    protected $lng;

    /** @var string */
    protected $path_to_rasterize = './Services/PDFGeneration/js/rasterize.js';

    public function __construct($phpunit = false)
    {
        if (!$phpunit) {
            global $DIC;
            $this->setLanguage($DIC['lng']);
        }
    }

    /**
     * @param $lng
     */
    protected function setLanguage($lng)
    {
        $this->lng = $lng;
    }

    /**
     * @var bool
     */
    protected $use_default_config;

    /**
     * @var string
     */
    protected $page_size;

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
     * @var string
     */
    protected $viewport;

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
     * @var string
     */
    protected $path;

    /**
     * @var int
     */
    protected $page_type = self::PAGE;

    /**
     * @return string
     */
    public function getPluginName()
    {
        return $this->lng->txt('pdfgen_renderer_dummyrender_plugname');
    }

    /**
     * @var string
     */
    protected $do_not_validate_ssl = ' --ssl-protocol=any --ignore-ssl-errors=true ';

    /**
     * from ilRendererConfig
     *
     * @param \ilPropertyFormGUI $form
     * @param string             $service
     * @param string             $purpose
     *
     * @return \ilPropertyFormGUI|void
     */
    public function addConfigElementsToForm(\ilPropertyFormGUI $form, $service, $purpose)
    {
        $path = new ilTextInputGUI($this->lng->txt('path'), 'path');
        $path->setValue($this->path);
        $form->addItem($path);

        $item_group = new ilRadioGroupInputGUI($this->lng->txt('page_settings'), 'page_type');

        $op = new ilRadioOption($this->lng->txt('page'), self::PAGE);
        $op->addSubItem($this->buildMarginForm());
        $op->addSubItem($this->buildOrientationForm());
        $op->addSubItem($this->buildPageSizesForm());
        $header_select = new ilRadioGroupInputGUI($this->lng->txt('header_type'), 'header_select');
        $header_select->addOption(new ilRadioOption($this->lng->txt('none'), ilPDFGenerationConstants::HEADER_NONE, ''));
        $header_text = new ilRadioOption($this->lng->txt('text'), ilPDFGenerationConstants::HEADER_TEXT, '');
        $header_text->addSubItem($this->buildHeaderTextForm());
        $header_text->addSubItem($this->buildHeaderHeightForm());
        $header_text->addSubItem($this->buildHeaderPageNumbersForm());
        $header_select->addOption($header_text);
        $header_select->setValue($this->header_type);
        $op->addSubItem($header_select);
        $footer_select = new ilRadioGroupInputGUI($this->lng->txt('footer_type'), 'footer_select');
        $footer_select->addOption(new ilRadioOption($this->lng->txt('none'), ilPDFGenerationConstants::FOOTER_NONE, ''));
        $footer_text = new ilRadioOption($this->lng->txt('text'), ilPDFGenerationConstants::FOOTER_TEXT, '');
        $footer_text->addSubItem($this->buildFooterTextForm());
        $footer_text->addSubItem($this->buildFooterHeightForm());
        $footer_text->addSubItem($this->buildFooterPageNumbersForm());
        $footer_select->addOption($footer_text);
        $footer_select->setValue($this->footer_type);
        $op->addSubItem($footer_select);
        $item_group->addOption($op);

        $op = new ilRadioOption($this->lng->txt('viewport'), self::VIEWPORT);
        $op->addSubItem($this->buildViewPortForm());
        $item_group->addOption($op);
        $item_group->setValue($this->page_type);
        $form->addItem($item_group);

        $form->addItem($this->buildJavascriptDelayForm());
    }

    /**
     * from ilRendererConfig
     *
     * @param \ilPropertyFormGUI $form
     * @param string             $service
     * @param string             $purpose
     * @param array              $config
     *
     * @return void
     */
    public function populateConfigElementsInForm(\ilPropertyFormGUI $form, $service, $purpose, $config)
    {
        $form->getItemByPostVar('path')->setValue($config['path']);
        $form->getItemByPostVar('page_size')->setValue($config['page_size']);
        $form->getItemByPostVar('margin')->setValue($config['margin']);
        $form->getItemByPostVar('javascript_delay')->setValue($config['javascript_delay']);
        $form->getItemByPostVar('viewport')->setValue($config['viewport']);
        $form->getItemByPostVar('orientation')->setValue($config['orientation']);
        $form->getItemByPostVar('header_select')->setValue($config['header_type']);
        $form->getItemByPostVar('header_text')->setValue($config['header_text']);
        $form->getItemByPostVar('header_height')->setValue($config['header_height']);
        $form->getItemByPostVar('header_show_pages')->setChecked($config['header_show_pages']);
        $form->getItemByPostVar('footer_select')->setValue($config['footer_type']);
        $form->getItemByPostVar('footer_text')->setValue($config['footer_text']);
        $form->getItemByPostVar('footer_height')->setValue($config['footer_height']);
        $form->getItemByPostVar('footer_show_pages')->setChecked($config['footer_show_pages']);
        $form->getItemByPostVar('page_type')->setValue($config['page_type']);

        ilPDFGeneratorUtils::setCheckedIfTrue($form);
    }

    /**
     * from ilRendererConfig
     *
     * @param \ilPropertyFormGUI $form
     * @param string             $service
     * @param string             $purpose
     *
     * @return bool
     */
    public function validateConfigInForm(\ilPropertyFormGUI $form, $service, $purpose)
    {
        if (true) {
            return true;
        }
        return false;
    }

    /**
     * from ilRendererConfig
     *
     * @param \ilPropertyFormGUI $form
     * @param string             $service
     * @param string             $purpose
     *
     * @return array
     */
    public function getConfigFromForm(\ilPropertyFormGUI $form, $service, $purpose)
    {
        $config = array();
        $config['path'] = $form->getItemByPostVar('path')->getValue();
        $config['page_size'] = $form->getItemByPostVar('page_size')->getValue();
        $config['margin'] = $form->getItemByPostVar('margin')->getValue();
        $config['javascript_delay'] = $form->getItemByPostVar('javascript_delay')->getValue();
        $config['viewport'] = $form->getItemByPostVar('viewport')->getValue();
        $config['orientation'] = $form->getItemByPostVar('orientation')->getValue();
        $config['header_type'] = $form->getItemByPostVar('header_select')->getValue();
        $config['header_text'] = $form->getItemByPostVar('header_text')->getValue();
        $config['header_height'] = $form->getItemByPostVar('header_height')->getValue();
        $config['header_show_pages'] = $form->getItemByPostVar('header_show_pages')->getChecked();
        $config['footer_type'] = $form->getItemByPostVar('footer_select')->getValue();
        $config['footer_text'] = $form->getItemByPostVar('footer_text')->getValue();
        $config['footer_height'] = $form->getItemByPostVar('footer_height')->getValue();
        $config['footer_show_pages'] = $form->getItemByPostVar('footer_show_pages')->getChecked();
        $config['page_type'] = $form->getItemByPostVar('page_type')->getValue();

        return $config;
    }

    /**
     * from ilRendererConfig
     *
     * @param string $service
     * @param string $purpose
     *
     * @return array
     */
    public function getDefaultConfig($service, $purpose)
    {
        $config = array();
        if (PATH_TO_PHANTOMJS !== '') {
            $config['path'] = PATH_TO_PHANTOMJS;
        } else {
            $config['path'] = '/usr/local/bin/phantomjs';
        }

        $config['page_size'] = 'A4';
        $config['margin'] = '1cm';
        $config['javascript_delay'] = 200;
        $config['viewport'] = '';
        $config['orientation'] = 'Portrait';
        $config['header_type'] = 0;
        $config['header_text'] = '';
        $config['header_height'] = '0cm';
        $config['header_show_pages'] = 0;
        $config['footer_type'] = 0;
        $config['footer_text'] = '';
        $config['footer_height'] = '0cm';
        $config['footer_show_pages'] = 0;
        $config['page_type'] = self::PAGE;

        return $config;
    }


    /**
     * Prepare the content processing at the beginning of a PDF generation request
     * Should be used to initialize the processing of latex code
     * The PDF renderers require different image formats generated by the MathJax service
     *
     * @param string              $service
     * @param string              $purpose
     * @return void
     */
    public function prepareGenerationRequest($service, $purpose)
    {
        ilMathJax::getInstance()
            ->init(ilMathJax::PURPOSE_PDF)
             ->setRendering(ilMathJax::RENDER_SVG_AS_XML_EMBED);
    }


    /**
     * from ilPDFRenderer
     *
     * @param string              $service
     * @param string              $purpose
     * @param array               $config
     * @param \ilPDFGenerationJob $job
     *
     * @return string|void
     */
    public function generatePDF($service, $purpose, $config, $job)
    {
        $html_file = $this->getHtmlTempName();
        file_put_contents($html_file, implode('', $job->getPages()));
        $this->createPDFFileFromHTMLFile($html_file, $config, $job);
    }

    /**
     * @param $a_path_to_file
     * @param $config
     * @param ilPDFGenerationJob $job
     */
    public function createPDFFileFromHTMLFile($a_path_to_file, $config, $job)
    {
        /** @var ilLog $ilLog */
        global $ilLog;

        if (file_exists($a_path_to_file)) {
            $temp_file = $this->getPdfTempName();

            $args = ' ' . $a_path_to_file . ' ' . $temp_file . ' ' . $this->getCommandLineConfig($config);
            $return_value = ilUtil::execQuoted($config['path'], $this->do_not_validate_ssl . ' ' . $this->path_to_rasterize . ' ' . $args);

            $ilLog->write('ilPhantomJSRenderer command line config: ' . $args);
            foreach ($return_value as $key => $value) {
                $ilLog->write('ilPhantomJSRenderer return value line ' . $key . ' : ' . $value);
            }

            if (file_exists($temp_file)) {
                $ilLog->write('ilPhantomJSRenderer file exists: ' . $temp_file . ' file size is :' . filesize($temp_file) . ' bytes, will be renamed to ' . $job->getFilename());
                ilFileUtils::rename($temp_file, $job->getFilename());
            } else {
                $ilLog->write('ilPhantomJSRenderer error: ' . print_r($return_value, true));
            }
        }
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
    protected function buildViewPortForm()
    {
        $viewport = new ilTextInputGUI($this->lng->txt('viewport'), 'viewport');
        $viewport->setValue($this->viewport);
        $viewport->setInfo($this->lng->txt('viewport_info'));
        return $viewport;
    }

    /**
     * @return ilCheckboxInputGUI
     */
    protected function buildHeaderPageNumbersForm()
    {
        $header_show_pages = new ilCheckboxInputGUI($this->lng->txt('header_show_pages'), 'header_show_pages');
        if ($this->header_show_pages == true || $this->header_show_pages == 1) {
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
     * @return ilCheckboxInputGUI
     */
    protected function buildFooterPageNumbersForm()
    {
        $footer_show_pages = new ilCheckboxInputGUI($this->lng->txt('footer_show_pages'), 'footer_show_pages');
        if ($this->footer_show_pages == true || $this->footer_show_pages == 1) {
            $footer_show_pages->setChecked(true);
        }
        return $footer_show_pages;
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
    protected function buildJavascriptDelayForm()
    {
        $javascript_delay = new ilTextInputGUI($this->lng->txt('javascript_delay'), 'javascript_delay');
        $javascript_delay->setInfo($this->lng->txt('javascript_delay_info'));
        $javascript_delay->setValue($this->javascript_delay);
        return $javascript_delay;
    }

    /**
     * @return string
     */
    public function getPdfTempName()
    {
        return $this->getTempFileName('pdf');
    }

    /**
     * @return string
     */
    public function getHtmlTempName()
    {
        return $this->getTempFileName('html');
    }

    /**
     * @param $file_type
     * @return string
     */
    protected function getTempFileName($file_type)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && $file_type == 'html') {
            return 'file:///' . str_replace(':/', '://', ilUtil::ilTempnam()) . '.' . $file_type;
        } else {
            return ilUtil::ilTempnam() . '.' . $file_type;
        }
    }

    /**
     * @param $config
     *
     * @return string
     */
    protected function getCommandLineConfig($config)
    {
        $r_config = array();

        if ($config['header_type'] == ilPDFGenerationConstants::HEADER_TEXT) {
            $h_config = array(
                'text' => $config['header_text'],
                'height' => $config['header_height'],
                'show_pages' => $config['header_show_pages']);
        } else {
            $h_config = null;
        }

        if ($config['footer_type'] == ilPDFGenerationConstants::FOOTER_TEXT) {
            $f_config = array(
                'text' => $config['footer_text'],
                'height' => $config['footer_height'],
                'show_pages' => $config['footer_show_pages']);
        } else {
            $f_config = null;
        }

        $r_config['page_size'] = $config['page_size'];
        $r_config['orientation'] = $config['orientation'];
        $r_config['margin'] = $config['margin'];
        $r_config['delay'] = $config['javascript_delay'];
        $r_config['viewport'] = $config['viewport'];
        $r_config['header'] = $h_config;
        $r_config['footer'] = $f_config;
        $r_config['page_type'] = $config['page_type'];

        return json_encode(json_encode($r_config));
    }
}
