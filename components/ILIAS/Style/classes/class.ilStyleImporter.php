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
 * Importer class for style
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilStyleImporter extends ilXmlImporter
{
    protected ilStyleDataSet $ds;
    /**
     * @var ilLogger
     */
    protected $log;

    public function init(): void
    {
        $this->log = ilLoggerFactory::getLogger('styl');

        $this->ds = new ilStyleDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImportDirectory($this->getImportDirectory());

        $this->log->debug("initialized");
    }

    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping): void
    {
        $this->log->debug("import xml " . $a_entity);
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
}
