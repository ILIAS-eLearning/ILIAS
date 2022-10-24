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

class ilHtmlToPdfTransformerFactory
{
    private const PDF_OUTPUT_DOWNLOAD = 'D';
    private const PDF_OUTPUT_INLINE = 'I';
    private const PDF_OUTPUT_FILE = 'F';

    protected ilLanguage $lng;

    public function __construct(string $component = '')
    {
        global $lng;
        $this->lng = $lng;
    }

    protected function generateTempPath(string $output): string
    {
        $dir = ilFileUtils::ilTempnam();
        if (!is_dir($dir)) {
            ilFileUtils::makeDirParents($dir);
        }

        $output = preg_replace('#[\\\\/:*?"<>|]#', '-', $output);
        $output = $dir . '/' . $output;
        return $output;
    }

    /**
     * @throws Exception
     */
    public function deliverPDFFromHTMLString(string $src, string $output, string $delivery_type, string $service, string $purpose)
    {
        $map = ilPDFGeneratorUtils::getRendererMapForPurpose($service, $purpose);
        $renderer = ilPDFGeneratorUtils::getRendererInstance($map['selected']);
        $config = ilPDFGeneratorUtils::getRendererConfig($service, $purpose, $map['selected']);

        if (basename($output) === $output) {
            $output = $this->generateTempPath($output);
        }

        $job = new ilPDFGenerationJob();
        $job->setFilename($output);
        $job->addPage($src);
        $job->setOutputMode($delivery_type);

        $renderer->generatePDF($service, $purpose, $config, $job);
        return $this->deliverPDF($output, $delivery_type);
    }

    protected function deliverPDF(string $file, string $delivery_type)
    {
        if (file_exists($file)) {
            if (strtoupper($delivery_type) === self::PDF_OUTPUT_DOWNLOAD) {
                ilFileDelivery::deliverFileLegacy($file, basename($file), '', false, true);
            } elseif (strtoupper($delivery_type) === self::PDF_OUTPUT_INLINE) {
                ilFileDelivery::deliverFileLegacy($file, basename($file), '', true, true);
            } elseif (strtoupper($delivery_type) === self::PDF_OUTPUT_FILE) {
                return $file;
            }
            return $file;
        }

        return false;
    }

    protected function createOneFileFromArray(array $src): string
    {
        $tmp_file = dirname(reset($src)) . '/complete_pages_overview.html';
        $html_content = '';
        foreach ($src as $filename) {
            if (file_exists($filename)) {
                $html_content .= file_get_contents($filename);
            }
        }
        file_put_contents($tmp_file, $html_content);
        return $tmp_file;
    }
}
