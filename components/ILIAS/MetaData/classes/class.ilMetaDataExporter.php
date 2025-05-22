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
use ILIAS\MetaData\XML\Writer\WriterInterface as StandardXMLWriter;
use ILIAS\MetaData\Repository\RepositoryInterface;

class ilMetaDataExporter extends ilXmlExporter
{
    protected StandardXMLWriter $writer;
    protected RepositoryInterface $repository;

    public function init(): void
    {
        global $DIC;

        $services = new InternalServices($DIC);

        $this->writer = $services->xml()->standardWriter();
        $this->repository = $services->repository()->repository();
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        $id = explode(":", $a_id);

        $obj_id = (int) $id[0];
        $sub_id = (int) $id[1];
        $type = (string) $id[2];

        if ($sub_id === 0) {
            $sub_id = $obj_id;
        }

        $md = $this->repository->getMD($obj_id, $sub_id, $type);
        $xml = $this->writer->write($md);

        return trim(str_replace('<?xml version="1.0"?>', '', $xml->asXML()));
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     * @return array<string, array<string, string>>
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return [
            "10.0" => [
                "namespace" => "http://www.ilias.de/Services/MetaData/md/10_0",
                "xsd_file" => "ilias_md_10_0.xsd",
                "min" => "10.0",
                "max" => ""
            ],
            "4.1.0" => [
                "namespace" => "http://www.ilias.de/Services/MetaData/md/4_1",
                "xsd_file" => "ilias_md_4_1.xsd",
                "min" => "4.1.0",
                "max" => "9.99"
            ]
        ];
    }
}
