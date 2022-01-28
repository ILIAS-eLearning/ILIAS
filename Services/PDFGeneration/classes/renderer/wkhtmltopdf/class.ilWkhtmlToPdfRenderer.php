<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    protected function setLanguage(\ilLanguage $lng) : void
    {
        $this->lng = $lng;
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param string            $service
     * @param string            $purpose
     */
    public function addConfigElementsToForm(ilPropertyFormGUI $form, $service, $purpose) : void
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
    public function populateConfigElementsInForm(ilPropertyFormGUI $form, $service, $purpose, $config) : void
    {
        $this->config = new ilWkhtmlToPdfConfig($config);
        $gui = new ilWkhtmlToPdfConfigFormGUI();
        $gui->populateForm($form, $this->config);
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param string            $service
     * @param string            $purpose
     */
    public function validateConfigInForm(ilPropertyFormGUI $form, $service, $purpose) : bool
    {
        $gui = new ilWkhtmlToPdfConfigFormGUI();
        return $gui->validateForm();
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param string            $service
     * @param string            $purpose
     * @return array<string, mixed>
     */
    public function getConfigFromForm(ilPropertyFormGUI $form, $service, $purpose) : array
    {
        $gui = new ilWkhtmlToPdfConfigFormGUI();
        return $gui->getConfigFromForm($form);
    }

    /**
     * @param string $service
     * @param string $purpose
     */
    public function getDefaultConfig($service, $purpose) : \ilWkhtmlToPdfConfig
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
    public function generatePDF($service, $purpose, $config, $job) : void
    {
        $html_file = $this->getHtmlTempName();
        file_put_contents($html_file, implode('', $job->getPages()));
        $this->createPDFFileFromHTMLFile($html_file, $config, $job);
    }

    public function getHtmlTempName() : string
    {
        return $this->getTempFileName('html');
    }

    /**
     * @param $file_type
     */
    protected function getTempFileName($file_type) : string
    {
        return ilFileUtils::ilTempnam() . '.' . $file_type;
    }

    /**
     * @param                    $a_path_to_file
     * @param                    $config
     */
    public function createPDFFileFromHTMLFile($a_path_to_file, $config, \ilPDFGenerationJob $job) : void
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
    protected function runCommandLine($a_path_to_file, $a_target, \ilWkhtmlToPdfConfig $config) : void
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

    public function getPdfTempName() : string
    {
        return $this->getTempFileName('pdf');
    }

    protected function redirectLog() : string
    {
        return $redirect_log = ' 2>&1 ';
    }

    /**
     * @param                     $a_path_to_file
     */
    protected function appendDefaultFontStyle($a_path_to_file, \ilWkhtmlToPdfConfig $config) : void
    {
        $backupStyle = $config->getOverwriteDefaultFont(true);
        $originalFile = file_get_contents($a_path_to_file) . $backupStyle;
        file_put_contents($a_path_to_file, $originalFile);
    }

    /**
     * @param string $service
     * @param string $purpose
     */
    public function prepareGenerationRequest($service, $purpose) : void
    {
        ilMathJax::getInstance()
                 ->init(ilMathJax::PURPOSE_PDF)
                 ->setRendering(ilMathJax::RENDER_SVG_AS_XML_EMBED);
    }
}
