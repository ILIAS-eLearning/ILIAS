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

namespace ILIAS\Test\Settings;

/**
 * @depracated This is only a temporary exception to identify missing migrations and will be removed in the future.
 */
class SettingsNotFoundException extends \ilObjectNotFoundException
{
    public function __construct($a_message)
    {
        $a_message .= "\nThis error occurs because the test settings migrations have not been completed yet.
            Without these migrations, the tests are unusable.";

        parent::__construct($a_message);
    }
}
