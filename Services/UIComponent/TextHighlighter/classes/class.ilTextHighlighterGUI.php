<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Text highlighter.
*/
class ilTextHighlighterGUI
{
    /**
     * Searches for all occurences of a text (case-insensitive) and highlights it
     */
    public static function highlight($a_dom_node_id, $a_text, $a_tpl = null)
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        if (!trim($a_text)) {
            return;
        }
        
        if ($a_tpl == null) {
            $a_tpl = $tpl;
        }
        $a_tpl->addJavascript("./Services/UIComponent/TextHighlighter/js/ilTextHighlighter.js");
        $a_tpl->addOnLoadCode("il.TextHighlighter.highlight('" . $a_dom_node_id . "','" . $a_text . "');");
    }
}
