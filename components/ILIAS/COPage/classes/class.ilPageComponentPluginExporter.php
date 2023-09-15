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
 * Abstract parent class for all page component plugin exporter classes.
 * @author Fred Neumann <fred.neumann@gmx.de>
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
    protected static array $pc_properties = array();

    /**
     * Plugin versions of exportable plugged page contents
     *
     * @var array $pc_version	id => version
     */
    protected static array $pc_version = array();


    /**
     * Set the properties of a plugged page content
     * This method is used by ilCOPageExporter to provide the properties
     */
    public static function setPCProperties(
        string $a_id,
        array $a_properties
    ): void {
        self::$pc_properties[$a_id] = $a_properties;
    }

    /**
     * Get the properties of a plugged page content
     */
    public static function getPCProperties(string $a_id): ?array
    {
        return self::$pc_properties[$a_id] ?? null;
    }

    /**
     * Set the version of a plugged page content
     * This method is used by ilCOPageExporter to provide the version
     */
    public static function setPCVersion(
        string $a_id,
        string $a_version
    ): void {
        self::$pc_version[$a_id] = $a_version;
    }

    /**
     * Get the version of a plugged page content
     */
    public static function getPCVersion(string $a_id): ?string
    {
        return self::$pc_version[$a_id] ?? null;
    }
}
