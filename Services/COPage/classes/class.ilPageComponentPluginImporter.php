<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Abstract parent class for all page component plugin importer classes.
 *
 * @author Fred Neumann <fred.neumann@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
abstract class ilPageComponentPluginImporter extends ilXmlImporter
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


    /**
     * Get the id of the mapped page content
     * The id structure should be irrelevant to child classes
     * The mapped ID shold be used both for getPCProperties() and setPCProperties()
     * when being called in their importXmlRepresentation()
     *
     * @param 	string				$a_id
     * @param	ilImportMapping		$a_mapping
     */
    public static function getPCMapping($a_id, $a_mapping)
    {
        $parts = explode(':', $a_id);
        $old_page_id = $parts[0] . ':' . $parts[1];
        $new_page_id = $a_mapping->getMapping("Services/COPage", 'pg', $old_page_id);

        return $new_page_id . ':' . $parts[2] . ':' . $parts[3];
    }
}
