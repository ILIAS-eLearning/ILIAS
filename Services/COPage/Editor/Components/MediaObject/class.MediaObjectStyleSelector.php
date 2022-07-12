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

namespace ILIAS\COPage\Editor\Components\MediaObject;

use ILIAS\COPage\Editor\Server\UIWrapper;
use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class MediaObjectStyleSelector
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
        string $type = "media-action",
        string $action = "media.class",
        string $attr = "class"
    ) : Dropdown {
        $a_chars = \ilPCMediaObjectGUI::_getCharacteristics($this->style_id);
        $ui_wrapper = $this->ui_wrapper;
        $ui = $this->ui;

        $buttons = [];
        foreach ($a_chars as $char => $char_lang) {
            $buttons[] = $ui_wrapper->getButton($char_lang, $type, $action, [$attr => $char]);
        }
        return $ui->factory()->dropdown()->standard($buttons)->withLabel($a_selected);
    }
}
