<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Awareness providers are
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderFactory
{
    protected static $providers = array(
        array(
            "component" => "Services/Contact/BuddySystem",
            "class" => "ilAwarenessUserProviderContactRequests"
        ),
        array(
            "component" => "Services/Awareness",
            "class" => "ilAwarenessUserProviderSystemContacts"
        ),
        array(
            "component" => "Services/Awareness",
            "class" => "ilAwarenessUserProviderCourseContacts"
        ),
        array(
            "component" => "Services/Awareness",
            "class" => "ilAwarenessUserProviderCurrentCourse"
        ),
        array(
            "component" => "Services/Contact/BuddySystem",
            "class" => "ilAwarenessUserProviderApprovedContacts"
        ),
        array(
            "component" => "Services/Awareness",
            "class" => "ilAwarenessUserProviderMemberships"
        ),
        array(
            "component" => "Services/Awareness",
            "class" => "ilAwarenessUserProviderAllUsers"
        )
    );

    /*protected static $providers = array(
        array (
            "component" => "Services/Awareness",
            "class" => "ilAwarenessUserProviderCourseContacts"
        )
    );*/

    /**
     * Get all awareness providers
     *
     * @return \ilAwarenessUserProvider[] array of ilAwarenessProvider all providers
     */
    public static function getAllProviders() : array
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
