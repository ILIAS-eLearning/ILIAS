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
 * GUI class that manages the editing of general test settings/properties
 * shown on "general" subtab
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/Test
 */
abstract class ilTestSettingsGUI
{
    protected ilObjTest $testOBJ;

    public function __construct(ilObjTest $testOBJ)
    {
        $this->testOBJ = $testOBJ;
    }

    protected function formPropertyExists(ilPropertyFormGUI $form, $propertyId): bool
    {
        return $form->getItemByPostVar($propertyId) instanceof ilFormPropertyGUI;
    }
}
