<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for user action contexts
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserActionContextFactory
{
    protected static $contexts = array(
        array(
            "component" => "Services/Awareness",
            "class" => "ilAwarenessUserActionContext"
        ),
        array(
            "component" => "Services/User/Gallery",
            "class" => "ilGalleryUserActionContext"
        )
    );

    /**
     * Get all action contexts
     *
     * @return array[ilUserActionContext] all providers
     */
    public static function getAllActionContexts()
    {
        $contexts = array();

        foreach (self::$contexts as $p) {
            $dir = (isset($p["dir"]))
                ? $p["dir"]
                : "classes";
            include_once("./" . $p["component"] . "/" . $dir . "/class." . $p["class"] . ".php");
            $contexts[] = new $p["class"]();
        }

        return $contexts;
    }
}
