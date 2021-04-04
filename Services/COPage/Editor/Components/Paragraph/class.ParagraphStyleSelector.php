<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Components\Paragraph;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ParagraphStyleSelector
{
    public static $style_selector_reset = "margin-top:2px; margin-bottom:2px; text-indent:0px; position:static; float:none; width: auto;";

    /**
     * @var
     */
    protected $ui_wrapper;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * Constructor
     */
    public function __construct($ui_wrapper, $style_id)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->ui_wrapper = $ui_wrapper;
        $this->style_id = $style_id;
    }

    /**
     * Get style selector
     */
    public function getStyleSelector($a_selected, $type = "par-action", $action = "par.class", $attr = "class")
    {
        $a_chars = \ilPCParagraphGUI::_getCharacteristics($this->style_id);
        $ui_wrapper = $this->ui_wrapper;
        $ui = $this->ui;
        $buttons = [];
        foreach ($a_chars as $char => $char_lang) {
            $t = "text_block";
            $tag = "div";
            switch ($char) {
                case "Headline1": $t = "heading1"; $tag = "h1"; break;
                case "Headline2": $t = "heading2"; $tag = "h2"; break;
                case "Headline3": $t = "heading3"; $tag = "h3"; break;
            }
            $html = '<div class="ilCOPgEditStyleSelectionItem"><' . $tag . ' class="ilc_' . $t . '_' . $char . '" style="' . self::$style_selector_reset . '">' . $char_lang . "</" . $tag . "></div>";
            $buttons[] = $ui_wrapper->getButton($html, $type, $action, [$attr => $char]);
        }
        $dd = $ui->factory()->dropdown()->standard($buttons)->withLabel($a_selected);
        return $dd;
    }
}
