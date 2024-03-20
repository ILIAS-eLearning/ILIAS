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

namespace ILIAS\ContainerReference;

use ILIAS\Repository\StandardGUIRequest;
use ilObjectDefinition;
use ilObject;
use ilAccessHandler;
use ilTreeExplorerGUI;
use ilContainerSorting;
use ilRepositorySelectorExplorerGUI;

class ilContainerReferenceRepositorySelectorExplorerGUI extends ilRepositorySelectorExplorerGUI
{
    public function isNodeClickable($a_node): bool
    {
        $ilAccess = $this->access;

        if (!$ilAccess->hasUserRBACorAnyPositionAccess('visible', $a_node['child'])) {
            return false;
        }

        if (is_array($this->getClickableTypes()) && count($this->getClickableTypes()) > 0) {
            return in_array($a_node['type'], $this->getClickableTypes(), true);
        }

        return true;
    }
}

