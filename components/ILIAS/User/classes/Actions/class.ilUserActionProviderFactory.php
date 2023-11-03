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
            "component" => "components/ILIAS/Contact/BuddySystem",
            "class" => "ilContactUserActionProvider"
        ],
        [
            "component" => "components/ILIAS/User/Actions",
            "class" => "ilMailUserActionProvider"
        ],
        [
            "component" => "components/ILIAS/User/Actions",
            "class" => "ilUserUserActionProvider"
        ],
        [
            "component" => "components/ILIAS/User/Actions",
            "class" => "ilWorkspaceUserActionProvider"
        ],
        [
            "component" => "components/ILIAS/User/Actions",
            "class" => "ilChatUserActionProvider"
        ],
        [
            "component" => "components/ILIAS/Group/UserActions",
            "class" => "ilGroupUserActionProvider"
        ],
        [
            "component" => "components/ILIAS/EmployeeTalk",
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
