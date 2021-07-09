<?php

class ilWkhtmlToPdfRenderer implements ilRendererConfig, ilPDFRenderer
{
    /** @var self */
    protected static $instance = null;
    /** @var ilWkhtmlToPdfConfig */
    protected $config;
    /** @var ilLanguage */
    protected $lng;

    /**
     * ilWkhtmlToPdfRenderer constructor.
     * @param bool $phpunit
     */
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
     * @param ilPropertyFormGUI $form
     * @param string            $service
     * @param string            $purpose
     */
    public function addConfigElementsToForm(ilPropertyFormGUI $form, $service, $purpose)
    {
        $gui = new ilWkhtmlToPdfConfigFormGUI();
        $gui->addConfigForm($form);
    }

    /**
     * @param ilPropertyFormGUI   $form
     * @param string              $service
     * @param string              $purpose
     * @param ilWkhtmlToPdfConfig $config
     */
    public function populateConfigElementsInForm(ilPropertyFormGUI $form, $service, $purpose, $config)
    {
        $this->config = new ilWkhtmlToPdfConfig($config);
        $gui = new ilWkhtmlToPdfConfigFormGUI();
        $gui->populateForm($form, $this->config);
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param string            $service
     * @param string            $purpose
     * @return bool
     */
    public function validateConfigInForm(ilPropertyFormGUI $form, $service, $purpose)
    {
        $gui = new ilWkhtmlToPdfConfigFormGUI();
        return $gui->validateForm();
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param string            $service
     * @param string            $purpose
     * @return array
     */
    public function getConfigFromForm(ilPropertyFormGUI $form, $service, $purpose)
    {
        $gui = new ilWkhtmlToPdfConfigFormGUI();
        return $gui->getConfigFromForm($form);
    }

    /**
     * @param string $service
     * @param string $purpose
     * @return ilWkhtmlToPdfConfig
     */
    public function getDefaultConfig($service, $purpose)
    {
        $config = new ilWkhtmlToPdfConfig();
        return $config;
    }

    /**
     * @param string             $service
     * @param string             $purpose
     * @param array              $config
     * @param ilPDFGenerationJob $job
     */
    public function generatePDF($service, $purpose, $config, $job)
    {
        $html_file = $this->getHtmlTempName();
        file_put_contents($html_file, implode('', $job->getPages()));
        $this->createPDFFileFromHTMLFile($html_file, $config, $job);
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
        return ilUtil::ilTempnam() . '.' . $file_type;
    }

    /**
     * @param                    $a_path_to_file
     * @param                    $config
     * @param ilPDFGenerationJob $job
     */
    public function createPDFFileFromHTMLFile($a_path_to_file, $config, $job)
    {
        if (is_array($a_path_to_file)) {
            $files_list_as_string = ' ';
            foreach ($a_path_to_file as $file) {
                if (file_exists($file)) {
                    $files_list_as_string .= ' ' . $files_list_as_string;
                }
            }
            $this->runCommandLine($files_list_as_string, $job->getFilename(), $config);
        } else {
            if (file_exists($a_path_to_file)) {
                $this->runCommandLine($a_path_to_file, $job->getFilename(), $config);
            }
        }
    }

    /**
     * @param $a_path_to_file
     * @param $a_target
     * @param $config
     */
    protected function runCommandLine($a_path_to_file, $a_target, $config)
    {
        global $DIC;
        $log = $DIC['ilLog'];
        $config = new ilWkhtmlToPdfConfig($config);
        $temp_file = $this->getPdfTempName();
        $args = $config->getCommandLineConfig() . ' ' . $a_path_to_file . ' ' . $temp_file . $this->redirectLog();
        $this->appendDefaultFontStyle($a_path_to_file, $config);
        $return_value = ilUtil::execQuoted($config->getWKHTMLToPdfDefaultPath(), $args);
        $log->debug('ilWebkitHtmlToPdfTransformer command line config: ' . $args);
        foreach ($return_value as $key => $value) {
            $log->debug('ilWebkitHtmlToPdfTransformer return value line ' . $key . ' : ' . $value);
        }
        if (file_exists($temp_file)) {
            $log->debug('ilWebkitHtmlToPdfTransformer file exists: ' . $temp_file . ' file size is :' . filesize($temp_file) . ' bytes, will be renamed to ' . $a_target);
            rename($temp_file, $a_target);
        } else {
            $log->info('ilWebkitHtmlToPdfTransformer error: ' . print_r($return_value, true));
        }
        unlink($a_path_to_file);
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
    protected function redirectLog()
    {
        return $redirect_log = ' 2>&1 ';
    }

    /**
     * @param                     $a_path_to_file
     * @param ilWkhtmlToPdfConfig $config
     */
    protected function appendDefaultFontStyle($a_path_to_file, $config)
    {
        $backupStyle = $config->getOverwriteDefaultFont(true);
        $originalFile = file_get_contents($a_path_to_file) . $backupStyle;
        file_put_contents($a_path_to_file, $originalFile);
    }

    /**
     * @param string $service
     * @param string $purpose
     */
    public function prepareGenerationRequest($service, $purpose)
    {
        ilMathJax::getInstance()
                 ->init(ilMathJax::PURPOSE_PDF)
                 ->setRendering(ilMathJax::RENDER_SVG_AS_XML_EMBED);
    }
}
