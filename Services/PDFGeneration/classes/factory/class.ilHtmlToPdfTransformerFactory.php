<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../class.ilAbstractHtmlToPdfTransformer.php';
require_once './Services/PDFGeneration/classes/class.ilPDFGenerationJob.php';

/**
 * Class ilHtmlToPdfTransformerFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlToPdfTransformerFactory
{
    const PDF_OUTPUT_DOWNLOAD	= 'D';
    const PDF_OUTPUT_INLINE		= 'I';
    const PDF_OUTPUT_FILE		= 'F';


    /**
     * @var ilLanguage $lng
     */
    protected $lng;

    /**
     * ilHtmlToPdfTransformerFactory constructor.
     * @param $component
     */
    public function __construct($component = '')
    {
        global $lng;
        $this->lng	= $lng;
    }

    /**
     * @param $output
     * @return string
     */
    protected function generateTempPath($output)
    {
        $dir = ilUtil::ilTempnam();
        if (!is_dir($dir)) {
            ilUtil::makeDirParents($dir);
        }

        $output = preg_replace('#[\\\\/:*?"<>|]#', '-', $output);
        $output = $dir . '/' . $output;
        return $output;
    }

    /**
     * @param $src
     * @param $output
     * @param $delivery_type
     * @param $service
     * @param $purpose
     * @throws Exception
     */
    public function deliverPDFFromHTMLString($src, $output, $delivery_type, $service, $purpose)
    {
        $map = ilPDFGeneratorUtils::getRendererMapForPurpose($service, $purpose);
        $renderer = ilPDFGeneratorUtils::getRendererInstance($map['selected']);
        $config = ilPDFGeneratorUtils::getRendererConfig($service, $purpose, $map['selected']);

        if (basename($output) == $output) {
            $output = $this->generateTempPath($output);
        }
        
        $job = new ilPDFGenerationJob();
        $job->setFilename($output);
        $job->addPage($src);
        $job->setOutputMode($delivery_type);

        /** @var ilPDFRenderer $renderer */
        $renderer->generatePDF($service, $purpose, $config, $job);
        return $this->deliverPDF($output, $delivery_type);
    }


    /**
     * @param $file
     * @param $delivery_type
     * @return mixed
     */
    protected function deliverPDF($file, $delivery_type)
    {
        if (file_exists($file)) {
            if (strtoupper($delivery_type) === self::PDF_OUTPUT_DOWNLOAD) {
                ilUtil::deliverFile($file, basename($file), '', false, true);
            } elseif (strtoupper($delivery_type) === self::PDF_OUTPUT_INLINE) {
                ilUtil::deliverFile($file, basename($file), '', true, true);
            } elseif (strtoupper($delivery_type) === self::PDF_OUTPUT_FILE) {
                return $file;
            }
            return $file;
        }
        return false;
    }
    /**
     * @param array $src
     * @return string
     */
    protected function createOneFileFromArray(array $src)
    {
        $tmp_file = dirname(reset($src)) . '/complete_pages_overview.html';
        $html_content	= '';
        foreach ($src as $filename) {
            if (file_exists($filename)) {
                $html_content .= file_get_contents($filename);
            }
        }
        file_put_contents($tmp_file, $html_content);
        return $tmp_file;
    }
}
