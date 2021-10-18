<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for notes data (xml)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesNotes
 */
class ilNotesExporter extends ilXmlExporter
{
    private ilNotesDataSet $ds;

    /**
     * Initialisation
     */
    public function init() : void
    {
        include_once("./Services/Notes/classes/class.ilNotesDataSet.php");
        $this->ds = new ilNotesDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    /**
     * Get xml representation
     * @param	string		entity
     * @param	string		target release
     * @param	string		id
     * @return	string		xml string
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     * @return array
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "4.3.0" => array(
                "namespace" => "http://www.ilias.de/Services/Notes/note/4_3",
                "xsd_file" => "ilias_usr_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => "")
        );
    }
}
