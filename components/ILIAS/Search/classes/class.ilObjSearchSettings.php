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
* Class ilObjSearchSettings
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @extends ilObject
* @package ilias-core
*/
class ilObjSearchSettings extends ilObject
{
    public ?ilSearchSettings $settings_obj = null;


    /**
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "seas";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function initSettingsObject(): void
    {
        $this->settings_obj = new ilSearchSettings();
    }



    public function update(): bool
    {
        if (!parent::update()) {
            return false;
        }
        return true;
    }
} // END class.ilObjSearchSettings
