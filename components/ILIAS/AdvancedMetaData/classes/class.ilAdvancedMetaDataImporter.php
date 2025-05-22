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

declare(strict_types=1);
/**
 * Importer class for adv md
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMetaDataImporter extends ilXmlImporter
{
    /**
     *
     */
    public function __construct()
    {
        global $DIC;

        parent::__construct();
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $parser = new ilAdvancedMDParser($a_id, $a_mapping);
        $parser->setXMLContent($a_xml);
        $parser->startParsing();

        // records with imported values should be selected
        foreach ($parser->getRecordIds() as $obj_id => $sub_types) {
            ilContainer::_writeContainerSetting($obj_id, ilObjectServiceSettingsGUI::CUSTOM_METADATA, "1");

            foreach ((array) $sub_types as $sub_type => $rec_ids) {
                ilAdvancedMDRecord::saveObjRecSelection($obj_id, $sub_type, array_unique($rec_ids), false);
            }
        }
    }
}
