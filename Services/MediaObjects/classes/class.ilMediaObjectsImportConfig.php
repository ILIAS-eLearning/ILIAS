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
 * Import configuration for media objects
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaObjectsImportConfig extends ilImportConfig
{
    protected bool $use_previous_import_ids = false;

    /**
     * Set use previous import ids
     *
     * @param bool $a_val use previous import ids
     */
    public function setUsePreviousImportIds(
        bool $a_val
    ) : void {
        $this->use_previous_import_ids = $a_val;
    }

    public function getUsePreviousImportIds() : bool
    {
        return $this->use_previous_import_ids;
    }
}
