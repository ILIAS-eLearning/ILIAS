<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Importer class for exercises
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseImporter extends ilXmlImporter
{
    protected ilExerciseDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilExerciseDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImportDirectory($this->getImportDirectory());
    }

    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
}
