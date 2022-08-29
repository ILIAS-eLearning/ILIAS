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
 * Importer class for wikis
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiImporter extends ilXmlImporter
{
    protected ilWikiDataSet $ds;

    public function init(): void
    {
        $this->ds = new ilWikiDataSet();
        $this->ds->setDSPrefix("ds");
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
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
    ): void {
        $wpg_map = $a_mapping->getMappingsOfEntity("Modules/Wiki", "wpg");

        foreach ($wpg_map as $wpg_id) {
            $wiki_id = ilWikiPage::lookupWikiId($wpg_id);
            ilWikiPage::_writeParentId("wpg", $wpg_id, $wiki_id);
        }
    }
}
