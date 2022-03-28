<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Importer class for objects (currently focused on translation information)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectImporter extends ilXmlImporter
{
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
    ) : void
    {
        new ilDataSetImportParser(
            $entity,
            $this->getSchemaVersion(),
            $xml,
            $this->ds,
            $mapping
        );
    }
}
