<?php declare(strict_types=1);

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
 * Importer class for objects (currently focused on translation information)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectImporter extends ilXmlImporter
{
    protected ?ilObjectDataSet $ds = null;
    
    public function __construct()
    {
        parent::__construct();
    }

    public function init() : void
    {
        $this->ds = new ilObjectDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImportDirectory($this->getImportDirectory());
    }

    public function importXmlRepresentation(
        string $entity,
        string $id,
        string $xml,
        ilImportMapping $mapping
    ) : void {
        new ilDataSetImportParser(
            $entity,
            $this->getSchemaVersion(),
            $xml,
            $this->ds,
            $mapping
        );
    }
}
