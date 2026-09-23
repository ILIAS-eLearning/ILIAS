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
 * Access checks of the repository object type lti.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilObjLTIConsumerAccess extends ilObjectAccess
{
    /**
     * @return array
     */
    public static function _getCommands(): array
    {
        return [
            [
                'permission' => 'read',
                'cmd' => ilObjLTIConsumerGUI::CMD_LAUNCH,
                'lang_var' => '',
                'default' => true,
            ],
            [
                'permission' => 'write',
                'cmd' => ilLTIObjectSettingsGUI::class . '::' . ilLTIObjectSettingsGUI::CMD_SHOW,
                'lang_var' => 'settings',
            ],
        ];
    }
}
