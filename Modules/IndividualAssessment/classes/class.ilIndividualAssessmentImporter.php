<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Manual Assessment importer class
 */
class ilIndividualAssessmentImporter extends ilXmlImporter
{
    protected ilIndividualAssessmentDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilIndividualAssessmentDataSet();
        $this->ds->setImportDirectory($this->getImportDirectory());
    }

    /**
     * Import xml representation
     */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        new ilDataSetImportParser($a_entity, $this->getSchemaVersion(), $a_xml, $this->ds, $a_mapping);
    }
}
