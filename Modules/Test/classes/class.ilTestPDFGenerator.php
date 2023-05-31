<?php
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

/**
 * Class ilTestPDFGenerator
 *
 * Class that handles PDF generation for test and assessment.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilTestPDFGenerator
{
    const PDF_OUTPUT_DOWNLOAD = 'D';
    const PDF_OUTPUT_INLINE = 'I';
    const PDF_OUTPUT_FILE = 'F';

    const service = "Test";

    private static function buildHtmlDocument($contentHtml, $styleHtml)
    {
        return "
			<html>
				<head>
					<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
 					$styleHtml
 				</head>
				<body>$contentHtml</body>
			</html>
		";
    }

    /**
     * @param $html
     * @return string
     */
    private static function makeHtmlDocument($contentHtml, $styleHtml)
    {
        if (!is_string($contentHtml) || !strlen(trim($contentHtml))) {
            return $contentHtml;
        }

        $html = self::buildHtmlDocument($contentHtml, $styleHtml);

        $dom = new DOMDocument("1.0", "utf-8");
        if (!@$dom->loadHTML($html)) {
            return $html;
        }

        $invalid_elements = array();

        $script_elements = $dom->getElementsByTagName('script');
        foreach ($script_elements as $elm) {
            $invalid_elements[] = $elm;
        }

        foreach ($invalid_elements as $elm) {
            $elm->parentNode->removeChild($elm);
        }

        // remove noprint elems as tcpdf will make empty pdf when hidden by css rules
        $domX = new DomXPath($dom);
        foreach ($domX->query("//*[contains(@class, 'noprint')]") as $node) {
            $node->parentNode->removeChild($node);
        }

        $dom->encoding = 'UTF-8';

        $img_src_map = array();
        foreach ($dom->getElementsByTagName('img') as $elm) {
            /** @var $elm DOMElement $uid */
            $uid = 'img_src_' . uniqid();
            $src = $elm->getAttribute('src');

            $elm->setAttribute('src', $uid);

            $img_src_map[$uid] = $src;
        }

        $cleaned_html = $dom->saveHTML();

        foreach ($img_src_map as $uid => $src) {
            $cleaned_html = str_replace($uid, $src, $cleaned_html);
        }

        if (!$cleaned_html) {
            return $html;
        }

        return $cleaned_html;
    }

    public static function generatePDF($pdf_output, $output_mode, $filename = null, $purpose = null)
    {
        $pdf_output = self::preprocessHTML($pdf_output);

        if (substr($filename, strlen($filename) - 4, 4) != '.pdf') {
            $filename .= '.pdf';
        }
        $pdf_factory = new ilHtmlToPdfTransformerFactory();

        if (!$pdf_factory->deliverPDFFromHTMLString(
            $pdf_output,
            $filename,
            $output_mode,
            self::service,
            $purpose
        )) {
            throw new \Exception('could not write PDF');
        }
        return true;
    }

    public static function preprocessHTML($html)
    {
        $html = self::makeHtmlDocument($html, '<style>' . self::getCssContent() . '</style>');

        return $html;
    }

    protected static function getTemplatePath($a_filename, $module_path = 'Modules/Test/')
    {
        // use ilStyleDefinition instead of account to get the current skin
        include_once "Services/Style/System/classes/class.ilStyleDefinition.php";
        if (ilStyleDefinition::getCurrentSkin() != "default") {
            $fname = "./Customizing/global/skin/" .
                    ilStyleDefinition::getCurrentSkin() . "/" . $module_path . basename($a_filename);
        }

        if ($fname == "" || !file_exists($fname)) {
            $fname = "./" . $module_path . "templates/default/" . basename($a_filename);
        }
        return $fname;
    }

    protected static function getCssContent()
    {
        $cssContent = file_get_contents(self::getTemplatePath('delos.css', ''));
        $cssContent .= file_get_contents(self::getTemplatePath('test_pdf.css'));

        return $cssContent;
    }
}
