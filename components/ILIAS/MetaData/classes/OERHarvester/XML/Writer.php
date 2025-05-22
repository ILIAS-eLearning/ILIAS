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

namespace ILIAS\MetaData\OERHarvester\XML;

use ILIAS\MetaData\Repository\RepositoryInterface as LOMRepository;
use ILIAS\MetaData\XML\Writer\SimpleDC\SimpleDCInterface as SimpleDCXMLWriter;

class Writer implements WriterInterface
{
    protected LOMRepository $lom_repository;
    protected SimpleDCXMLWriter $xml_writer;

    public function __construct(
        LOMRepository $lom_repository,
        SimpleDCXMLWriter $xml_writer
    ) {
        $this->lom_repository = $lom_repository;
        $this->xml_writer = $xml_writer;
    }

    public function writeSimpleDCMetaData(int $obj_id, int $ref_id, string $type): \DOMDocument
    {
        $simple_dc_xml = new \DOMDocument();
        $simple_dc_xml->loadXML($this->xml_writer->write(
            $this->lom_repository->getMD($obj_id, $obj_id, $type),
            $ref_id
        )->asXML());
        return $simple_dc_xml;
    }
}
