<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Abstract parent class for all page component plugin exporter classes.
 *
 * @author Fred Neumann <fred.neumann@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
abstract class ilPageComponentPluginExporter extends ilXmlExporter
{
    /**
     * Properties of exportable plugged page contents
     * The id has the following format:
     * 		<parent_type>:<page_id>:<lang>:<pc_id>
     * This format, however, should be irrelevant to child classes
     *
     * @var array $pc_properties  id => [ name => value, ... ]
     */
    protected static $pc_properties = array();

    /**
     * Plugin versions of exportable plugged page contents
     *
     * @var array $pc_version	id => version
     */
    protected static $pc_version = array();


    /**
     * Set the properties of a plugged page content
     * This method is used by ilCOPageExporter to provide the properties
     *
     * @param string $a_id
     * @param array $a_properties
     */
    public static function setPCProperties($a_id, $a_properties)
    {
        self::$pc_properties[$a_id] = $a_properties;
    }

    /**
     * Get the properties of a plugged page content
     *
     * @param string $a_id
     * @return mixed|null
     */
    public static function getPCProperties($a_id)
    {
        if (isset(self::$pc_properties[$a_id])) {
            return self::$pc_properties[$a_id];
        } else {
            return null;
        }
    }

    /**
     * Set the version of a plugged page content
     * This method is used by ilCOPageExporter to provide the version
     *
     * @param string $a_id
     * @param string $a_version
     */
    public static function setPCVersion($a_id, $a_version)
    {
        self::$pc_version[$a_id] = $a_version;
    }

    /**
     * Get the version of a plugged page content
     *
     * @param string $a_id
     * @return string|null
     */
    public static function getPCVersion($a_id)
    {
        if (isset(self::$pc_version[$a_id])) {
            return self::$pc_version[$a_id];
        } else {
            return null;
        }
    }
}
