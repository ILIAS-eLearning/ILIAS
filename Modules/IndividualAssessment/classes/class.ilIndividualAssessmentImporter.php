<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Export/classes/class.ilXmlImporter.php");
require_once("Modules/IndividualAssessment/classes/class.ilIndividualAssessmentDataSet.php");

/**
 * Manual Assessment importer class
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilIndividualAssessmentImporter extends ilXmlImporter
{
    /**
     * Init
     */
    public function init()
    {
        $this->ds = new ilIndividualAssessmentDataSet();
        $this->ds->setImportDirectory($this->getImportDirectory());
    }

    /**
     * Import xml representation
     *
     * @param	string		entity
     * @param	string		target release
     * @param	string		id
     * @return	string		xml string
     */
    public function importXmlRepresentation($entity, $id, $xml, $mapping)
    {
        $parser = new ilDataSetImportParser($entity, $this->getSchemaVersion(), $xml, $this->ds, $mapping);
    }
}
