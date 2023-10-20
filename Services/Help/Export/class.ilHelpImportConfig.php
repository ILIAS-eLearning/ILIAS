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
 * Import configuration for help modules
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHelpImportConfig extends ilImportConfig
{
    protected int $module_id = 0;

    public function setModuleId(int $a_val): void
    {
        $this->module_id = $a_val;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }
}
