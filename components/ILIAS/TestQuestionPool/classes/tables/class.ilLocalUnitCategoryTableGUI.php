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

/**
 * Class ilLocalUnitCategoryTableGUI
 */
class ilLocalUnitCategoryTableGUI extends ilUnitCategoryTableGUI
{
    protected function populateTitle(): void
    {
        if ($this->getParentObject()->isCRUDContext()) {
            $this->setTitle($this->lng->txt('un_local_units') . ': ' . $this->lng->txt('categories'));
        } else {
            $this->setTitle($this->lng->txt('un_global_units') . ': ' . $this->lng->txt('categories'));
        }
    }
}
