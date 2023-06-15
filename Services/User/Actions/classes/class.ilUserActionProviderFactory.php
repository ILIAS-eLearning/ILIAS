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

class ilUserActionProviderFactory
{
    private const PROVIDERS = [
        [
            "component" => "Services/Contact/BuddySystem",
            "class" => "ilContactUserActionProvider"
        ],
        [
            "component" => "Services/User/Actions",
            "class" => "ilMailUserActionProvider"
        ],
        [
            "component" => "Services/User/Actions",
            "class" => "ilUserUserActionProvider"
        ],
        [
            "component" => "Services/User/Actions",
            "class" => "ilWorkspaceUserActionProvider"
        ],
        [
            "component" => "Services/User/Actions",
            "class" => "ilChatUserActionProvider"
        ],
        [
            "component" => "Modules/Group/UserActions",
            "class" => "ilGroupUserActionProvider"
        ],
        [
            "component" => "Modules/EmployeeTalk",
            "class" => "EmployeeTalkUserActionProvider"
        ]
    ];

    public function getProviders(): Generator
    {
        foreach (self::PROVIDERS as $p) {
            yield new $p["class"]();
        }
    }
}
