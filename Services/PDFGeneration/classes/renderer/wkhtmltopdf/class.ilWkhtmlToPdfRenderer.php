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
    protected static ?ilWkhtmlToPdfRenderer $instance = null;

    protected ilWkhtmlToPdfConfig $config;

    protected ilLanguage $lng;

    protected ilLogger $log;

    public function __construct(bool $phpunit = false)
    {
        if (!$phpunit) {
            global $DIC;
            $this->setLanguage($DIC->language());
            $this->log = $DIC->logger()->root();
        }
    }

    protected function setLanguage(ilLanguage $lng) : void
    {
        $this->lng = $lng;
    }

    public function addConfigElementsToForm(ilPropertyFormGUI $form, string $service, string $purpose) : void
    {
        $gui = new ilWkhtmlToPdfConfigFormGUI();
        $gui->addConfigForm($form);
    }

    public function populateConfigElementsInForm(ilPropertyFormGUI $form, string $service, string $purpose, array $config) : void
    {
        $this->config = new ilWkhtmlToPdfConfig($config);
        $gui = new ilWkhtmlToPdfConfigFormGUI();
        $gui->populateForm($form, $this->config);
    }

    public function validateConfigInForm(ilPropertyFormGUI $form, string $service, string $purpose) : bool
    {
        $gui = new ilWkhtmlToPdfConfigFormGUI();
        return $gui->validateForm();
    }

    public function getConfigFromForm(ilPropertyFormGUI $form, string $service, string $purpose) : array
    {
        $gui = new ilWkhtmlToPdfConfigFormGUI();
        return $gui->getConfigFromForm($form);
    }

    public function getDefaultConfig(string $service, string $purpose) : ilWkhtmlToPdfConfig
    {
        $config = new ilWkhtmlToPdfConfig();
        return $config;
    }

    public function generatePDF(string $service, string $purpose, array $config, ilPDFGenerationJob $job) : void
    {
        $html_file = $this->getHtmlTempName();
        file_put_contents($html_file, implode('', $job->getPages()));
        $this->createPDFFileFromHTMLFile($html_file, $config, $job);
    }

    public function getHtmlTempName() : string
    {
        return $this->getTempFileName('html');
    }

    protected function getTempFileName(string $file_type) : string
    {
        return ilFileUtils::ilTempnam() . '.' . $file_type;
    }

    public function createPDFFileFromHTMLFile(string $a_path_to_file, array $config, \ilPDFGenerationJob $job) : void
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

    protected function runCommandLine(string $a_path_to_file, string $a_target, array $config) : void
    {
        $config = new ilWkhtmlToPdfConfig($config);
        $temp_file = $this->getPdfTempName();
        $args = $config->getCommandLineConfig() . ' ' . $a_path_to_file . ' ' . $temp_file . $this->redirectLog();
        $this->appendDefaultFontStyle($a_path_to_file, $config);
        $return_value = ilShellUtil::execQuoted($config->getWKHTMLToPdfDefaultPath(), $args);
        $this->log->debug('ilWebkitHtmlToPdfTransformer command line config: ' . $args);
        $this->checkReturnValueFromCommandLine($return_value, $temp_file, $a_target);
        unlink($a_path_to_file);
    }

    public function getPdfTempName() : string
    {
        return $this->getTempFileName('pdf');
    }

    protected function redirectLog() : string
    {
        return ' 2>&1 ';
    }

    protected function appendDefaultFontStyle(string $a_path_to_file, ilWkhtmlToPdfConfig $config) : void
    {
        $backupStyle = $config->getOverwriteDefaultFont(true);
        $originalFile = file_get_contents($a_path_to_file) . $backupStyle;
        file_put_contents($a_path_to_file, $originalFile);
    }

    public function prepareGenerationRequest(string $service, string $purpose) : void
    {
        ilMathJax::getInstance()
                 ->init(ilMathJax::PURPOSE_PDF)
                 ->setRendering(ilMathJax::RENDER_SVG_AS_XML_EMBED);
    }

    protected function checkReturnValueFromCommandLine(array $return_value, string $temp_file, string $a_target) : void
    {
        foreach ($return_value as $key => $value) {
            $this->log->debug('ilWebkitHtmlToPdfTransformer return value line ' . $key . ' : ' . $value);
        }
        if (file_exists($temp_file)) {
            $this->log->debug('ilWebkitHtmlToPdfTransformer file exists: ' . $temp_file . ' file size is :' . filesize($temp_file) . ' bytes, will be renamed to ' . $a_target);
            rename($temp_file, $a_target);
        } else {
            $this->log->info('ilWebkitHtmlToPdfTransformer error: ' . print_r($return_value, true));
        }
    }
}
