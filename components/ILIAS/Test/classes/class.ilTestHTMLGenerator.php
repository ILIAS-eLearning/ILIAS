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

declare(strict_types=1);

/**
 *
 * Class that handles PDF generation for test and assessment.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilTestHTMLGenerator
{
    private function buildHtmlDocument($content_html, $style_html): string
    {
        return "
			<html>
				<head>
					<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
 					$style_html
 				</head>
				<body>$content_html</body>
			</html>
		";
    }

    /**
     * @param $content_html
     * @param $style_html
     * @return string
     */
    private function makeHtmlDocument($content_html, $style_html): string
    {
        if (!is_string($content_html) || !strlen(trim($content_html))) {
            return $content_html;
        }

        $html = $this->buildHtmlDocument($content_html, $style_html);

        $dom = new DOMDocument("1.0", "utf-8");
        if (!@$dom->loadHTML($html)) {
            return $html;
        }

        $invalid_elements = [];

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

        $content_to_replace = $this->generateImageDataSrces($dom);

        $cleaned_html = $dom->saveHTML();

        if (!$cleaned_html) {
            return $html;
        }

        if ($content_to_replace === null) {
            return $cleaned_html;
        }

        return str_replace(array_keys($content_to_replace), array_values($content_to_replace), $cleaned_html);
    }

    private function generateImageDataSrces(DOMDocument $dom): ?array
    {
        $content_to_replace = null;
        $sources = [];
        $image_file_names = [];
        foreach ($dom->getElementsByTagName('img') as $elm) {
            /** @var $elm DOMElement $uid */
            $src = $elm->getAttribute('src');
            $original_content = $dom->saveHTML($elm);

            if (array_key_exists($src, $sources)) {
                $replacement_content = preg_replace('/src=[^\s]*/', "src='{$sources[$src]}'", $original_content);
                $content_to_replace[$original_content] = $replacement_content;
                continue;
            }

            if (stripos($src, ILIAS_HTTP_PATH) !== false
                && stripos($src, 'templates') === false) {
                $src = ILIAS_HTTP_PATH . substr(ilWACSignedPath::signFile($src), 1);
            }

            try {
                $image_raw_content = file_get_contents($src);
                $image_file_names[$src] = ilFileUtils::ilTempnam();
                file_put_contents($image_file_names[$src], $image_raw_content);
                $image_content = base64_encode($image_raw_content);
                $file_info = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($file_info, $image_file_names[$src]);
                $image_data = "data:{$mime_type};base64,{$image_content}";
                $sources[$src] = $image_data;
                $replacement_content = preg_replace('/src=[^\s]*/', "src='{$image_data}'", $original_content);
                $content_to_replace[$original_content] = $replacement_content;
            } catch (Exception $e) {

            }
        }

        foreach ($image_file_names as $image_file_name) {
            unlink($image_file_name);
        }

        return $content_to_replace;
    }

    public function generateHTML(string $content, string $filename)
    {
        file_put_contents($filename, $this->preprocessHTML($content));
        return true;
    }

    private function preprocessHTML(string $html): string
    {
        return $this->makeHtmlDocument($html, '<style>' . $this->getCssContent() . '</style>');
    }

    private function getTemplatePath($a_filename, $module_path = 'components/ILIAS/Test/'): string
    {
        $fname = '';
        if (ilStyleDefinition::getCurrentSkin() != "default") {
            $fname = "./Customizing/global/skin/" .
                    ilStyleDefinition::getCurrentSkin() . "/" . $module_path . basename($a_filename);
        }

        if ($fname == "" || !file_exists($fname)) {
            $fname = "./" . $module_path . "templates/default/" . basename($a_filename);
        }
        return $fname;
    }

    private function getCssContent(): string
    {
        $css_content = file_get_contents($this->getTemplatePath('delos.css', ''));
        $css_content .= ' html, body { overflow: auto; } body { padding: 1rem; }';

        return $css_content;
    }
}
