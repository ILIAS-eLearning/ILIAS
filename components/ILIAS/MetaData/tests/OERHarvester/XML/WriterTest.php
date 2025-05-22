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

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\XML\Writer\SimpleDC\SimpleDCInterface;
use ILIAS\MetaData\Repository\NullRepository;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Elements\NullSet;
use ILIAS\MetaData\XML\Writer\SimpleDC\NullSimpleDC;

class WriterTest extends TestCase
{
    protected function getLOMRepository(): RepositoryInterface
    {
        return new class () extends NullRepository {
            public function getMD(int $obj_id, int $sub_id, string $type): SetInterface
            {
                $xml_elements = '<obj_id>' . $obj_id . '</obj_id>' .
                    '<sub_id>' . $sub_id . '</sub_id>' .
                    '<type>' . $type . '</type>';
                return new class ($xml_elements) extends NullSet {
                    public function __construct(public string $xml_elements)
                    {
                    }
                };
            }
        };
    }

    protected function getSimpleDCWriter(): SimpleDCInterface
    {
        return new class () extends NullSimpleDC {
            public function write(SetInterface $set, int $object_ref_id): \SimpleXMLElement
            {
                return new \SimpleXMLElement(
                    '<xml>' . $set->xml_elements . '<ref_id>' . $object_ref_id . '</ref_id>' . '</xml>'
                );
            }
        };
    }

    public function testWriteSimpleDCMetaData(): void
    {
        $writer = new Writer(
            $this->getLOMRepository(),
            $this->getSimpleDCWriter()
        );

        $expected_xml = '<xml><obj_id>' . 21 . '</obj_id>' .
            '<sub_id>' . 21 . '</sub_id>' .
            '<type>' . 'some type' . '</type>' .
            '<ref_id>' . 67 . '</ref_id></xml>';

        $xml = $writer->writeSimpleDCMetaData(21, 67, 'some type');

        $this->assertXmlStringEqualsXmlString(
            $expected_xml,
            $xml->saveXML()
        );
    }
}
