<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Importer class for taxonomies
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaxonomyImporter extends ilXmlImporter
{
    protected ilTaxonomyDataSet $ds;

    /**
     * Initialisation
     */
    public function init() : void
    {
        $this->ds = new ilTaxonomyDataSet();
        $this->ds->setDSPrefix("ds");
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
}
