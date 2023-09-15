<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Dashboard objects table renderer
 */
class ilDashObjectsTableRenderer
{
    protected object $parent_gui;

    public function __construct(object $parent_gui)
    {
        $this->parent_gui = $parent_gui;
    }

    public function render(array $groupedItems): string
    {
        $cnt = 0;
        $html = "";
        foreach ($groupedItems as $group) {
            $items = $group->getItems();
            if (count($items) > 0) {
                $table = new ilDashObjectsTableGUI($this->parent_gui, "render", $cnt++);
                $table->setTitle($group->getLabel());
                $table->setData($group->getItems());
                $html .= $table->getHTML();
            }
        }
        return $html;
    }
}
