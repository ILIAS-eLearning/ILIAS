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
 * Importer class for portfolio
 * Only for portfolio templates!
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioImporter extends ilXmlImporter
{
    protected ilPortfolioDataSet $ds;
    
    public function init() : void
    {
        $this->ds = new ilPortfolioDataSet();
        $this->ds->setDSPrefix("ds");
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        $this->ds->setImportDirectory($this->getImportDirectory());
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
    
    public function finalProcessing(
        ilImportMapping $a_mapping
    ) : void {
        $prttpg_map = $a_mapping->getMappingsOfEntity("Services/COPage", "pg");
        foreach ($prttpg_map as $prttpg_id) {
            $prttpg_id = substr($prttpg_id, 5);
            $prtt_id = ilPortfolioTemplatePage::findPortfolioForPage($prttpg_id);
            ilPortfolioTemplatePage::_writeParentId("prtt", $prttpg_id, $prtt_id);
        }
    }
}
