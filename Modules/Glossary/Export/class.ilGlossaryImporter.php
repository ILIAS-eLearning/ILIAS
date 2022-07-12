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
 * Importer class for files
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilGlossaryImporter extends ilXmlImporter
{
    protected ilImportConfig $config;
    protected ilGlossaryDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilGlossaryDataSet();
        $this->ds->setDSPrefix("ds");
        $this->config = $this->getImport()->getConfig("Modules/Glossary");
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        if ($a_entity == "glo") {
            // case i container
            if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
                $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
            }

            // in the new version (5.1)  we are also here, but the following file should not exist
            // if being exported with 5.1 or higher
            $xml_file = $this->getImportDirectory() . '/' . basename($this->getImportDirectory()) . '.xml';
            $GLOBALS['ilLog']->write(__METHOD__ . ': Using XML file ' . $xml_file);

            // old school import
            if (file_exists($xml_file)) {
                throw new ilGlossaryOldImportException("This glossary seems to be from ILIAS version 5.0.x or lower. Import is not supported anymore.");
            } else {
                // necessary?
                // ilObject::_writeImportId($newObj->getId(), $newObj->getImportId());
                $parser = new ilDataSetImportParser(
                    $a_entity,
                    $this->getSchemaVersion(),
                    $a_xml,
                    $this->ds,
                    $a_mapping
                );

                // in the new version the mapping above is done in the dataset
            }
        }
    }
    
    public function finalProcessing(
        ilImportMapping $a_mapping
    ) : void {

        // get all glossaries of the import
        $maps = $a_mapping->getMappingsOfEntity("Modules/Glossary", "glo");
        foreach ($maps as $old => $new) {
            if ($old != "new_id" && (int) $old > 0) {
                // get all new taxonomys of this object
                $new_tax_ids = $a_mapping->getMapping("Services/Taxonomy", "tax_usage_of_obj", $old);
                if ($new_tax_ids !== false) {
                    $tax_ids = explode(":", $new_tax_ids);
                    foreach ($tax_ids as $tid) {
                        ilObjTaxonomy::saveUsage((int) $tid, (int) $new);
                    }
                }

                // advmd column order
                if ($this->exportedFromSameInstallation()) {
                    $advmdco = $a_mapping->getMappingsOfEntity("Modules/Glossary", "advmd_col_order");
                    foreach ($advmdco as $id => $order) {
                        $id = explode(":", $id);
                        $field_glo_id = $id[0];
                        $field_id = $id[1];
                        if ($field_glo_id == $old) {
                            // #17454
                            $new_local_id = $a_mapping->getMapping("Services/AdvancedMetaData", "lfld", $field_id);
                            if ($new_local_id) {
                                $field_id = $new_local_id;
                            }
                            ilGlossaryAdvMetaDataAdapter::writeColumnOrder((int) $new, (int) $field_id, (int) $order);
                        }
                    }
                }
            }
        }
    }
}
