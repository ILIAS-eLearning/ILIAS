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
 * Factory for user action providers
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserActionProviderFactory
{
    protected static array $providers = array(
        array(
            "component" => "Services/Contact/BuddySystem",
            "class" => "ilContactUserActionProvider"
        ),
        array(
            "component" => "Services/User/Actions",
            "class" => "ilMailUserActionProvider"
        ),
        array(
            "component" => "Services/User/Actions",
            "class" => "ilUserUserActionProvider"
        ),
        array(
            "component" => "Services/User/Actions",
            "class" => "ilWorkspaceUserActionProvider"
        ),
        array(
            "component" => "Services/User/Actions",
            "class" => "ilChatUserActionProvider"
        ),
        array(
            "component" => "Modules/Group/UserActions",
            "class" => "ilGroupUserActionProvider"
        ),
        array(
            "component" => "Modules/EmployeeTalk",
            "class" => "EmployeeTalkUserActionProvider"
        )
    );

    /**
     * Get all action providers
     *
     * @return ilUserActionProvider[] all providers
     */
    public static function getAllProviders(): array
    {
        $providers = array();

        foreach (self::$providers as $p) {
            $dir = $p["dir"] ?? "classes";
            $providers[] = new $p["class"]();
        }

        return $providers;
    }
}
