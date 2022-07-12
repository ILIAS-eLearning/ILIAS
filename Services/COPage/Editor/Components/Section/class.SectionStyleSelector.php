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

namespace ILIAS\COPage\Editor\Components\Section;

use ILIAS\COPage\Editor\Server\UIWrapper;
use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class SectionStyleSelector
{
    public static string $style_selector_reset = "margin-top:2px; margin-bottom:2px; text-indent:0px; position:static; float:none; width: auto;";
    protected int $style_id = 0;

    protected UIWrapper $ui_wrapper;
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;

    public function __construct(
        UIWrapper $ui_wrapper,
        int $style_id
    ) {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->ui_wrapper = $ui_wrapper;
        $this->style_id = $style_id;
        $this->lng = $DIC->language();
    }

    public function getStyleSelector(
        string $a_selected,
        string $type = "sec-action",
        string $action = "sec.class",
        string $attr = "class",
        bool $include_none = false
    ) : Dropdown {
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
