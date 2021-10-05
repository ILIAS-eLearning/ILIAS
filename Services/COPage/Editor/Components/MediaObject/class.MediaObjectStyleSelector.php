<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Components\MediaObject;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class MediaObjectStyleSelector
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
    public function getStyleSelector($a_selected, $type = "media-action", $action = "media.class", $attr = "class")
    {
        $a_chars = \ilPCMediaObjectGUI::_getCharacteristics($this->style_id);
        $ui_wrapper = $this->ui_wrapper;
        $ui = $this->ui;
        $lng = $this->lng;

        $buttons = [];
        foreach ($a_chars as $char => $char_lang) {
            $buttons[] = $ui_wrapper->getButton($char_lang, $type, $action, [$attr => $char]);
        }
        $dd = $ui->factory()->dropdown()->standard($buttons)->withLabel($a_selected);
        return $dd;
    }
}
