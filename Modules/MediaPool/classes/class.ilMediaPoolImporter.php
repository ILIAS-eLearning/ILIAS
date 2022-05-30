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
 * Importer class for media pools
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPoolImporter extends ilXmlImporter
{
    protected ilImportConfig $config;
    protected ilMediaPoolDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilMediaPoolDataSet();
        $this->ds->setDSPrefix("ds");

        $this->config = $this->getImport()->getConfig("Modules/MediaPool");
        if ($this->config->getTranslationImportMode()) {
            $this->ds->setTranslationImportMode(
                $this->config->getTranslationMep(),
                $this->config->getTranslationLang()
            );
            $cop_config = $this->getImport()->getConfig("Services/COPage");
            $cop_config->setUpdateIfExists(true);
            $cop_config->setForceLanguage($this->config->getTranslationLang());
            $cop_config->setReuseOriginallyExportedMedia(true);
            $cop_config->setSkipInternalLinkResolve(true);

            $mob_config = $this->getImport()->getConfig("Services/MediaObjects");
            $mob_config->setUsePreviousImportIds(true);
        }
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

    public function finalProcessing(
        ilImportMapping $a_mapping
    ) : void {
        $pg_map = $a_mapping->getMappingsOfEntity("Modules/MediaPool", "pg");

        foreach ($pg_map as $pg_id) {
            $mep_id = ilMediaPoolItem::getPoolForItemId($pg_id);
            $mep_id = current($mep_id);
            ilMediaPoolPage::_writeParentId("mep", $pg_id, $mep_id);
        }
    }
}
