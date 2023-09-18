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

class ilBibtechParserTest extends TestCase
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

    public function testParseBibtechAsArray(): void
    {
        $ilBiblEntryFactory = $this->createMock(ilBiblEntryFactoryInterface::class);
        $ilBiblFieldFactory = $this->createMock(ilBiblFieldFactoryInterface::class);
        $ilBiblAttributeFactory = $this->createMock(ilBiblAttributeFactoryInterface::class);
        $reader = new ilBiblTexFileReader(
            $ilBiblEntryFactory,
            $ilBiblFieldFactory,
            $ilBiblAttributeFactory
        );
        $reader->setFileContent($this->getBibtechContent());
        $content = $reader->parseContent();
        $this->assertIsArray($content);
        $this->assertEquals(2, count($content));

        // First item
        $first_item = $content[0];
        $this->assertEquals('book', $first_item['entryType']);
        $this->assertEquals('Süß, Henrik http://www.testlink.ch', $first_item['author']);
        $this->assertEquals('Last Minute Histologie: [Fit fürs Examen in 2 Tagen!]', $first_item['title']);
        $this->assertEquals('München', $first_item['address']);
        $this->assertEquals('2012', $first_item['year']);
        $this->assertEquals('Elsevier, Urban & Fischer', $first_item['publisher']);
        $this->assertEquals('Histologie. Lehrbuch', $first_item['keywords']);
        $this->assertEquals('XIII, 103 S.', $first_item['pages']);
        $this->assertEquals('1. Aufl.', $first_item['edition']);
        $this->assertEquals('3-437-43015-7', $first_item['isbn']);

        // Second item
        $second_item = $content[1];
        $this->assertEquals('journal', $second_item['entryType']);
        $this->assertEquals('Voll, Markus http://de.wikipedia.org/wiki/a-z', $second_item['author']);
        $this->assertEquals(
            'Atlas of neuroanatomy for communication science and disorders: based on the work of Michael Schuenke, Erik Schulte, Udo Schumacher',
            $second_item['title']
        );
        $this->assertEquals('New York', $second_item['address']);
        $this->assertEquals('2012', $second_item['year']);
        $this->assertEquals('Thieme', $second_item['publisher']);
        $this->assertEquals(
            'Sprachstörung. Zentralnervensystem. Neuroanatomie. Neuropathologie. Atlas',
            $second_item['keywords']
        );
        $this->assertEquals('IX, 176 S.', $second_item['pages']);
        $this->assertEquals('978-1-60406-649-4', $second_item['isbn']);
    }

    public function testParseBibtechAsItems(): void
    {
        $ilBiblEntryFactory = $this->createMock(ilBiblEntryFactoryInterface::class);
        $ilBiblFieldFactory = $this->createMock(ilBiblFieldFactoryInterface::class);
        $ilBiblAttributeFactory = $this->createMock(ilBiblAttributeFactoryInterface::class);
        $ilObjBibliographic = $this->createMock(ilObjBibliographic::class);

        $reader = new ilBiblTexFileReader(
            $ilBiblEntryFactory,
            $ilBiblFieldFactory,
            $ilBiblAttributeFactory
        );
        $reader->setFileContent($this->getBibtechContent());

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
        $this->assertEquals('book', $first_item->getType());
        // Second Item
        /** @var ilBiblEntry $second_item */
        $second_item = $content[1];
        $this->assertEquals('journal', $second_item->getType());
    }

    protected function getBibtechContent(): string
    {
        return '@book {
author = {Süß, Henrik http://www.testlink.ch},
title = {Last Minute Histologie: [Fit fürs Examen in 2 Tagen!]},
address = {München},
year = {2012},
publisher = {Elsevier, Urban & Fischer},
keywords = {Histologie. Lehrbuch},
pages = {XIII, 103 S.},
edition = {1. Aufl.},
ISBN = {978-3-437-43015-2},
ISBN = {3-437-43015-7},
}
@journal {
author = {Voll, Markus http://de.wikipedia.org/wiki/a-z},
editor = {Voll, Markus},
title = {Atlas of neuroanatomy for communication science and disorders: based on the work of Michael Schuenke, Erik Schulte, Udo Schumacher},
address = {New York},
year = {2012},
publisher = {Thieme},
keywords = {Sprachstörung. Zentralnervensystem. Neuroanatomie. Neuropathologie. Atlas},
pages = {IX, 176 S.},
ISBN = {978-1-60406-649-4},
}';
    }
}
