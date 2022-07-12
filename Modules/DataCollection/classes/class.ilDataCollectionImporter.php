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
 ********************************************************************
 */

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
