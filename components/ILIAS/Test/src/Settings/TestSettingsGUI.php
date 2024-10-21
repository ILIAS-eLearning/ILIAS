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

namespace ILIAS\Test\Settings;

use ilFormPropertyGUI;
use ilObjTest;
use ilPropertyFormGUI;

/**
 * GUI class that manages the editing of general test settings/properties
 * shown on "general" subtab
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/Test
 */
abstract class TestSettingsGUI
{
    public function __construct(protected ilObjTest $test_object)
    {
    }

    protected function formPropertyExists(ilPropertyFormGUI $form, string $propertyId): bool
    {
        return $form->getItemByPostVar($propertyId) instanceof ilFormPropertyGUI;
    }
}
