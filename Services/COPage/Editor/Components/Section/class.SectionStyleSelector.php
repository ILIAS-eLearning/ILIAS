<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Components\Section;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class SectionStyleSelector
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
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct($ui_wrapper, $style_id)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->ui_wrapper = $ui_wrapper;
        $this->style_id = $style_id;
        $this->lng = $DIC->language();
    }

    /**
     * Get style selector
     */
    public function getStyleSelector($a_selected, $type = "sec-action", $action = "sec.class", $attr = "class", $include_none = false)
    {
        $a_chars = \ilPCSectionGUI::_getCharacteristics($this->style_id);
        $ui_wrapper = $this->ui_wrapper;
        $ui = $this->ui;
        $lng = $this->lng;

        $buttons = [];
        if ($include_none) {
            $t = "section";
            $tag = "div";
            $html = '<div class="ilCOPgEditStyleSelectionItem"><' . $tag . ' class="" style="' . self::$style_selector_reset . '">' . $lng->txt("cont_none") . "</" . $tag . "></div>";
            $buttons[] = $ui_wrapper->getButton($html, $type, $action, [$attr => ""]);
        }
        foreach ($a_chars as $char => $char_lang) {
            $t = "section";
            $tag = "div";
            $html = '<div class="ilCOPgEditStyleSelectionItem"><' . $tag . ' class="ilc_' . $t . '_' . $char . '" style="' . self::$style_selector_reset . '">' . $char_lang . "</" . $tag . "></div>";
            $buttons[] = $ui_wrapper->getButton($html, $type, $action, [$attr => $char]);
        }
        $dd = $ui->factory()->dropdown()->standard($buttons)->withLabel($a_selected);
        return $dd;
    }
}
