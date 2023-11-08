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

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilStandardGlossarySelectorGUI extends ilRepositorySelectorExplorerGUI
{
    public function __construct(
        ilObjGlossaryGUI $a_parent_obj,
        string $a_parent_cmd,
        ilObjGlossaryGUI $a_selection_gui = null,
        string $a_selection_cmd = "selectObject",
        string $a_selection_par = "sel_ref_id",
    ) {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_selection_gui, $a_selection_cmd, $a_selection_par);
        $this->setTypeWhiteList(["root", "cat", "grp", "crs", "glo", "fold"]);
        $this->setClickableTypes(["glo"]);
    }

    public function isNodeVisible($a_node): bool
    {
        if ($a_node['type'] === "glo") {
            $glossary = new ilObjGlossary($a_node["child"]);
            if ($glossary->isVirtual()) {
                return false;
            } else {
                return parent::isNodeVisible($a_node);
            }
        }

        return parent::isNodeVisible($a_node);
    }
}
