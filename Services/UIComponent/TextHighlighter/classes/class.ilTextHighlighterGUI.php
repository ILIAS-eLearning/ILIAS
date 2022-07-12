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
 * Text highlighter.
 * @author Alexander Killing <killing@leifos.de>
 *
 * @deprecated 11
 */
class ilTextHighlighterGUI
{
    /**
     * Searches for all occurences of a text (case-insensitive) and highlights it
     */
    public static function highlight(
        string $a_dom_node_id,
        string $a_text,
        ilGlobalTemplateInterface $a_tpl = null
    ) : void {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $tpl = $DIC["tpl"];

        if (!trim($a_text)) {
            return;
        }
        
        if ($a_tpl === null) {
            $a_tpl = $tpl;
        }
        $a_tpl->addJavaScript("./Services/UIComponent/TextHighlighter/js/ilTextHighlighter.js");
        $a_tpl->addOnLoadCode("il.TextHighlighter.highlight('" . $a_dom_node_id . "','" . $a_text . "');");
    }
}
