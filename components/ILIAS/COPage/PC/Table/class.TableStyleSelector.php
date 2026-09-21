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

namespace ILIAS\COPage\PC\Table;

use ILIAS\COPage\Editor\Server\UIWrapper;
use ILIAS\UI\Component\Dropdown\Dropdown;

class TableStyleSelector
{
    protected int $style_id = 0;
    protected UIWrapper $ui_wrapper;
    protected \ILIAS\DI\UIServices $ui;

    public function __construct(
        UIWrapper $ui_wrapper,
        int $style_id
    ) {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->ui_wrapper = $ui_wrapper;
        $this->style_id = $style_id;
    }

    public function getStyleSelector(
        string $a_selected,
        string $type = "table-action",
        string $action = "table.template",
        string $attr = "format"
    ): Dropdown {
        $buttons = [];
        if ($this->style_id > 0 && \ilObject::_lookupType($this->style_id) === "sty") {
            $style = new \ilObjStyleSheet($this->style_id);
            foreach ($style->getTemplates("table") as $template) {
                $key = "t:" . $template["id"] . ":" . $template["name"];
                $buttons[] = $this->ui_wrapper->getButton(
                    $template["name"],
                    $type,
                    $action,
                    [$attr => $key]
                );
            }
        }

        return $this->ui->factory()->dropdown()->standard($buttons)->withLabel($a_selected);
    }
}
