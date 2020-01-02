<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for user action providers
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserActionProviderFactory
{
    protected static $providers = array(
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
        )

    );

    /**
     * Get all action providers
     *
     * @return ilUserActionProvider[] all providers
     */
    public static function getAllProviders()
    {
        $providers = array();

        foreach (self::$providers as $p) {
            $dir = (isset($p["dir"]))
                ? $p["dir"]
                : "classes";
            include_once("./" . $p["component"] . "/" . $dir . "/class." . $p["class"] . ".php");
            $providers[] = new $p["class"]();
        }

        return $providers;
    }
}
