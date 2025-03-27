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
 * Abstract parent class for all page component plugin importer classes.
 *
 * @author Fred Neumann <fred.neumann@gmx.de>
 */
abstract class ilPageComponentPluginImporter extends ilXmlImporter
{
    /**
     * @deprecated
     */
    protected static array $pc_properties = array();

    /**
     * @deprecated
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
        ilPageComponentPluginExportImportStore::getInstance()->setPCProperties($a_id, $a_properties);
    }

    /**
     * Get the properties of a plugged page content
     */
    public static function getPCProperties(string $a_id): ?array
    {
        return ilPageComponentPluginExportImportStore::getInstance()->getPCProperties($a_id);
    }

    /**
     * Set the version of a plugged page content
     * This method is used by ilCOPageExporter to provide the version
     */
    public static function setPCVersion(
        string $a_id,
        string $a_version
    ): void {
        ilPageComponentPluginExportImportStore::getInstance()->setPCVersion($a_id, $a_version);
    }

    /**
     * Get the version of a plugged page content
     */
    public static function getPCVersion(string $a_id): ?string
    {
        return ilPageComponentPluginExportImportStore::getInstance()->getPCVersion($a_id);
    }

    /**
     * Get the id of the mapped page content
     * The id structure should be irrelevant to child classes
     * The mapped ID should be used both for getPCProperties() and setPCProperties()
     * when being called in their importXmlRepresentation()
     */
    public static function getPCMapping(string $a_id, ilImportMapping $a_mapping): string
    {
        return ilPageComponentPluginExportImportStore::getInstance()->getMappedContentId($a_id, $a_mapping);
    }
}
