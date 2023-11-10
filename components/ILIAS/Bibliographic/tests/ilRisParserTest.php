<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;
use ILIAS\ResourceStorage\Services;

class ilRisParserTest extends TestCase
{
    private ?\ILIAS\DI\Container $dic_backup = null;

    protected function setUp(): void
    {
        global $DIC;
        $this->dic_backup = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();
        $DIC['resource_storage'] = $this->createMock(Services::class);
        $DIC['ilDB'] = $this->createMock(ilDBInterface::class);
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }

    public function testParseRisAsArray(): void
    {
        $ilBiblEntryFactory = $this->createMock(ilBiblEntryFactoryInterface::class);
        $ilBiblFieldFactory = $this->createMock(ilBiblFieldFactoryInterface::class);
        $ilBiblAttributeFactory = $this->createMock(ilBiblAttributeFactoryInterface::class);
        $reader = new ilBiblRisFileReader(
            $ilBiblEntryFactory,
            $ilBiblFieldFactory,
            $ilBiblAttributeFactory
        );
        $reader->setFileContent($this->getRisContent());
        $content = $reader->parseContent();

        $this->assertIsArray($content);
        $this->assertEquals(2, count($content));

        // First item
        $first_item = $content[0];

        $this->assertEquals('BOOK', $first_item['TY']);
        $this->assertEquals('Schrode, Paula, Simon, Udo Gerald', $first_item['A2']);
        $this->assertEquals('Die Sunna leben', $first_item['T1']);
        $this->assertEquals('zur Dynamik islamischer Religionspraxis in Deutschland', $first_item['T2']);
        $this->assertEquals('Würzburg', $first_item['CY']);
        $this->assertEquals('2012', $first_item['Y1']);
        $this->assertEquals('Egon', $first_item['PB']);
        $this->assertEquals('Deutschland, Islam, Religionsausübung, Kongress', $first_item['KW']);
        $this->assertEquals('250 S.', $first_item['EP']);
        $this->assertEquals('978-3-89913-722-4', $first_item['SN']);

        // Second item
        $second_item = $content[1];

        $this->assertEquals('JOURNAL', $second_item['TY']);
        $this->assertEquals('Gienow-Hecht, Jessica C. E.', $second_item['A2']);
        $this->assertEquals('Searching for a cultural diplomacy', $second_item['T1']);
        $this->assertEquals('New York [u.a.]', $second_item['CY']);
        $this->assertEquals('2010', $second_item['Y1']);
        $this->assertEquals('Berghahn', $second_item['PB']);
        $this->assertEquals('Sowjetunion, Mitteleuropa, Naher Osten, Japan, Kulturbeziehungen, Diplomatie, Geschichte 1900-2000, Aufsatzsammlung', $second_item['KW']);
        $this->assertEquals('XII, 265 S.', $second_item['EP']);
        $this->assertEquals('978-1-84545-746-4', $second_item['SN']);
    }

    public function testParseRisAsItems(): void
    {
        $ilBiblEntryFactory = $this->createMock(ilBiblEntryFactoryInterface::class);
        $ilBiblFieldFactory = $this->createMock(ilBiblFieldFactoryInterface::class);
        $ilBiblAttributeFactory = $this->createMock(ilBiblAttributeFactoryInterface::class);
        $ilObjBibliographic = $this->createMock(ilObjBibliographic::class);

        $reader = new ilBiblRisFileReader(
            $ilBiblEntryFactory,
            $ilBiblFieldFactory,
            $ilBiblAttributeFactory
        );
        $reader->setFileContent($this->getRisContent());

        $ilBiblEntryFactory->expects($this->atLeast(2))
                           ->method('getEmptyInstance')
                           ->willReturnOnConsecutiveCalls(new ilBiblEntry(), new ilBiblEntry());


        $content = $reader->parseContentToEntries($ilObjBibliographic);

        $this->assertIsArray($content);
        $this->assertEquals(2, count($content));
        $this->assertContainsOnlyInstancesOf(ilBiblEntry::class, $content);

        // First item
        /** @var ilBiblEntry $first_item */
        $first_item = $content[0];
        $this->assertEquals('BOOK', $first_item->getType());
        // Second Item
        /** @var ilBiblEntry $second_item */
        $second_item = $content[1];
        $this->assertEquals('JOURNAL', $second_item->getType());
    }

    protected function getRisContent(): string
    {
        return 'TY  - BOOK
A2  - Schrode, Paula
A2  - Simon, Udo Gerald
T1  - Die Sunna leben
T2  - zur Dynamik islamischer Religionspraxis in Deutschland
CY  - Würzburg
Y1  - 2012
PB  - Egon
KW  - Deutschland
KW  - Islam
KW  - Religionsausübung
KW  - Kongress
EP  - 250 S.
SN  - 978-3-89913-722-4
U1  - WilhelmstraÃŸe
ER  -
TY  - JOURNAL
A2  - Gienow-Hecht, Jessica C. E.
T1  - Searching for a cultural diplomacy
CY  - New York [u.a.]
Y1  - 2010
PB  - Berghahn
KW  - Sowjetunion
KW  - Mitteleuropa
KW  - Naher Osten
KW  - Japan
KW  - Kulturbeziehungen
KW  - Diplomatie
KW  - Geschichte 1900-2000
KW  - Aufsatzsammlung
EP  - XII, 265 S.
SN  - 978-1-84545-746-4
U1  - Wilhelmstraße
ER  - ';
    }
}
