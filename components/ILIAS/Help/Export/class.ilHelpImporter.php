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
 * Importer class for help
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHelpImporter extends ilXmlImporter
{
    protected ilHelpDataSet $ds;
    protected ?ilHelpImportConfig $config = null;

    public function init(): void
    {
        $this->ds = new ilHelpDataSet();
        $this->ds->setDSPrefix("ds");

        /** @var ilHelpImportConfig $config */
        $config = $this->getImport()->getConfig("Services/Help");
        $this->config = $config;
        $module_id = $this->config->getModuleId();
        if ($module_id > 0) {
            $this->getImport()->getMapping()->addMapping('Services/Help', 'help_module', 0, $module_id);
        }
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
}
