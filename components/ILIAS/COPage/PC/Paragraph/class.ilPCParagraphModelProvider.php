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

use ILIAS\COPage\Dom\DomUtil;
use ILIAS\COPage\Editor\Components\PageComponentModelProvider;

class ilPCParagraphModelProvider implements PageComponentModelProvider
{
    public function getModels(
        DomUtil $dom_util,
        \ilPageObject $page
    ): array {
        $models = [];

        foreach ($dom_util->path($page->getDomDoc(), "//Paragraph") as $node) {
            $par = $node->parentNode;
            $pc_id = $par->getAttribute("PCID");

            $model = new \stdClass();
            $s_text = "";
            foreach ($node->childNodes as $c) {
                $s_text .= $dom_util->dump($c);
            }
            $s_text = \ilPCParagraph::xml2output($s_text, true, false);
            $s_text = \ilPCParagraphGUI::xml2outputJS($s_text);

            $char = (string) $node->getAttribute("Characteristic");
            if ($char == "") {
                $char = "Standard";
            }
            $model->characteristic = $char;
            $model->text = $s_text;
            $models[$pc_id] = $model;
        }

        return $models;
    }
}
