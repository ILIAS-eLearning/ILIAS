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

    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping) : void
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
