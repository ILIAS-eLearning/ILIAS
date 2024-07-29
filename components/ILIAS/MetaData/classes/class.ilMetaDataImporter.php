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

use ILIAS\MetaData\Services\InternalServices;
use ILIAS\MetaData\XML\Reader\ReaderInterface as StandardXMLReader;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\XML\Version;

class ilMetaDataImporter extends ilXmlImporter
{
    protected StandardXMLReader $reader;
    protected RepositoryInterface $repository;
    protected ilLogger $logger;

    public function init(): void
    {
        global $DIC;

        $services = new InternalServices($DIC);

        $this->reader = $services->xml()->standardReader();
        $this->repository = $services->repository()->repository();
        $this->logger = $DIC->logger()->meta();
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $new_id = $a_mapping->getMapping("components/ILIAS/MetaData", "md", $a_id);

        if (!is_string($new_id) || $new_id === "") {
            $this->logger->error(
                'Import of LOM aborted for ' . $new_id . ', ID mapping failed.'
            );
            return;
        }

        $id = explode(":", $new_id);

        $obj_id = (int) $id[0];
        $sub_id = (int) $id[1];
        $type = (string) $id[2];

        if ($sub_id === 0) {
            $sub_id = $obj_id;
        }

        $version = Version::tryFrom($this->getSchemaVersion());
        if (is_null($version)) {
            $this->logger->error(
                'Import of LOM aborted for ' . $new_id .
                ', invalid schema version: ' . $this->getSchemaVersion()
            );
            return;
        }

        $md = $this->reader->read(
            new SimpleXMLElement($a_xml),
            $version
        );
        $this->repository->transferMD($md, $obj_id, $sub_id, $type, false);
    }
}
