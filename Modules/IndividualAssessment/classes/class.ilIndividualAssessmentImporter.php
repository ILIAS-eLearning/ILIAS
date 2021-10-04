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
    public function init() : void
    {
        $this->ds = new ilIndividualAssessmentDataSet();
        $this->ds->setImportDirectory($this->getImportDirectory());
    }

    /**
     * Import xml representation
     * @param	string		entity
     * @param	string		target release
     * @param	string		id
     * @return	void		xml string
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        $parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(), $a_xml, $this->ds, $a_mapping);
    }
}
