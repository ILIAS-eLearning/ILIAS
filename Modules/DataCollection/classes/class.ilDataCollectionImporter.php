<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDataCollectionImporter
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDataCollectionImporter extends ilXmlImporter
{
    protected ilDataCollectionDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilDataCollectionDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImportDirectory($this->getImportDirectory());
    }

    /**
     * Executes the Import
     * @param string          $a_entity
     * @param string          $a_id
     * @param string          $a_xml
     * @param ilImportMapping $a_mapping
     * @return void
     */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        $parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(), $a_xml, $this->ds, $a_mapping);
    }

    /**
     * Called before finishing the import
     * @param ilImportMapping $a_mapping
     */
    public function finalProcessing(ilImportMapping $a_mapping) : void
    {
        $this->ds->beforeFinishImport($a_mapping);
    }

    public static function getExcelCharForInteger(int $int) : string
    {
        $char = "";
        $rng = range("A", "Z");
        while ($int > 0) {
            $diff = ($int - 1) % 26;
            $char = $rng[$diff] . $char;
            $int -= $diff;
            $int = (int) ($int / 26);
        }

        return $char;
    }
}
