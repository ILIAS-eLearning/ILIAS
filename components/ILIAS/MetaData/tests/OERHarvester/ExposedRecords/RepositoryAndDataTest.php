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

namespace ILIAS\MetaData\OERHarvester\ExposedRecords;

use PHPUnit\Framework\TestCase;

class RepositoryAndDataTest extends TestCase
{
    protected function getRepository(
        int $current_datestamp,
        array $returns_on_query
    ): DatabaseRepository {
        return new class ($current_datestamp, $returns_on_query) extends DatabaseRepository {
            public array $exposed_queries = [];

            public function __construct(
                protected int $current_datestamp,
                protected array $returns_on_query
            ) {
            }

            protected function query(string $query): \Generator
            {
                $this->exposed_queries[] = $query;
                yield from $this->returns_on_query;
            }

            protected function manipulate(string $query): void
            {
                $this->exposed_queries[] = $query;
            }

            protected function quoteInteger(int $integer): string
            {
                return '~int:' . $integer . '~';
            }

            protected function quoteString(string $string): string
            {
                return '~string:' . $string . '~';
            }

            protected function quoteClob(string $string): string
            {
                return '~clob:' . $string . '~';
            }

            protected function getCurrentDatestamp(): int
            {
                return $this->current_datestamp;
            }
        };
    }

    protected function assertRecordMatchesArray(RecordInterface $record, array $data): void
    {
        $this->assertRecordInfosMatchesArray($record->infos(), $data);
        $this->assertXmlStringEqualsXmlString(
            $data['metadata'],
            $record->metadata()->saveXML()
        );
    }

    protected function assertRecordInfosMatchesArray(RecordInfosInterface $infos, array $data): void
    {
        $this->assertSame((int) $data['obj_id'], $infos->objID());
        $this->assertSame($data['identifier'], $infos->identfifier());
        $this->assertSame((int) $data['datestamp'], $infos->datestamp()->getTimestamp());
    }

    public function testGetRecords(): void
    {
        $record_1 = [
            'obj_id' => '32',
            'identifier' => 'id32',
            'datestamp' => '123456',
            'metadata' => '<content>something</content>'
        ];
        $record_2 = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653',
            'metadata' => '<content><sub1>hello</sub1><sub2>world</sub2></content>'
        ];
        $record_3 = [
            'obj_id' => '1',
            'identifier' => 'id1',
            'datestamp' => '65656565',
            'metadata' => '<content>something else</content>'
        ];
        $repo = $this->getRepository(0, [$record_1, $record_2, $record_3]);

        $records = iterator_to_array($repo->getRecords());

        $this->assertSame(
            ['SELECT * FROM il_meta_oer_exposed ORDER BY obj_id'],
            $repo->exposed_queries
        );
        $this->assertCount(3, $records);
        $this->assertRecordMatchesArray($records[0], $record_1);
        $this->assertRecordMatchesArray($records[1], $record_2);
        $this->assertRecordMatchesArray($records[2], $record_3);
    }

    public function testGetRecordsWithFromDate(): void
    {
        $record_1 = [
            'obj_id' => '32',
            'identifier' => 'id32',
            'datestamp' => '123456',
            'metadata' => '<content>something</content>'
        ];
        $record_2 = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653',
            'metadata' => '<content><sub1>hello</sub1><sub2>world</sub2></content>'
        ];
        $repo = $this->getRepository(0, [$record_1, $record_2]);

        $records = iterator_to_array($repo->getRecords(
            new \DateTimeImmutable('@1723994')
        ));

        $this->assertSame(
            ['SELECT *' . ' FROM il_meta_oer_exposed WHERE datestamp >= ~int:1723994~ ORDER BY obj_id'],
            $repo->exposed_queries
        );
        $this->assertCount(2, $records);
        $this->assertRecordMatchesArray($records[0], $record_1);
        $this->assertRecordMatchesArray($records[1], $record_2);
    }

    public function testGetRecordsWithUntilDate(): void
    {
        $record_2 = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653',
            'metadata' => '<content><sub1>hello</sub1><sub2>world</sub2></content>'
        ];
        $record_3 = [
            'obj_id' => '1',
            'identifier' => 'id1',
            'datestamp' => '65656565',
            'metadata' => '<content>something else</content>'
        ];
        $repo = $this->getRepository(0, [$record_2, $record_3]);

        $records = iterator_to_array($repo->getRecords(
            null,
            new \DateTimeImmutable('@1763994')
        ));

        $this->assertSame(
            ['SELECT *' . ' FROM il_meta_oer_exposed WHERE datestamp <= ~int:1763994~ ORDER BY obj_id'],
            $repo->exposed_queries
        );
        $this->assertCount(2, $records);
        $this->assertRecordMatchesArray($records[0], $record_2);
        $this->assertRecordMatchesArray($records[1], $record_3);
    }

    public function testGetRecordsWithFromAndUntilDates(): void
    {
        $record_2 = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653',
            'metadata' => '<content><sub1>hello</sub1><sub2>world</sub2></content>'
        ];
        $repo = $this->getRepository(0, [$record_2]);

        $records = iterator_to_array($repo->getRecords(
            new \DateTimeImmutable('@1723994'),
            new \DateTimeImmutable('@1763994')
        ));

        $this->assertSame(
            ['SELECT *' . ' FROM il_meta_oer_exposed WHERE datestamp >= ~int:1723994~ AND datestamp <= ~int:1763994~ ORDER BY obj_id'],
            $repo->exposed_queries
        );
        $this->assertCount(1, $records);
        $this->assertRecordMatchesArray($records[0], $record_2);
    }

    public function testGetRecordsWithLimit(): void
    {
        $record = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653',
            'metadata' => '<content><sub1>hello</sub1><sub2>world</sub2></content>'
        ];
        $repo = $this->getRepository(0, [$record]);

        $records = iterator_to_array($repo->getRecords(
            null,
            null,
            5
        ));

        $this->assertSame(
            ['SELECT *' . ' FROM il_meta_oer_exposed ORDER BY obj_id LIMIT ~int:5~'],
            $repo->exposed_queries
        );
        $this->assertCount(1, $records);
        $this->assertRecordMatchesArray($records[0], $record);
    }

    public function testGetRecordsWithOffset(): void
    {
        $record = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653',
            'metadata' => '<content><sub1>hello</sub1><sub2>world</sub2></content>'
        ];
        $repo = $this->getRepository(0, [$record]);

        $records = iterator_to_array($repo->getRecords(
            null,
            null,
            null,
            5
        ));

        $this->assertSame(
            ['SELECT *' . ' FROM il_meta_oer_exposed ORDER BY obj_id LIMIT ~int:' . PHP_INT_MAX . '~ OFFSET ~int:5~'],
            $repo->exposed_queries
        );
        $this->assertCount(1, $records);
        $this->assertRecordMatchesArray($records[0], $record);
    }

    public function testGetRecordsWithLimitAndOffset(): void
    {
        $record = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653',
            'metadata' => '<content><sub1>hello</sub1><sub2>world</sub2></content>'
        ];
        $repo = $this->getRepository(0, [$record]);

        $records = iterator_to_array($repo->getRecords(
            null,
            null,
            5,
            10
        ));

        $this->assertSame(
            ['SELECT *' . ' FROM il_meta_oer_exposed ORDER BY obj_id LIMIT ~int:5~ OFFSET ~int:10~'],
            $repo->exposed_queries
        );
        $this->assertCount(1, $records);
        $this->assertRecordMatchesArray($records[0], $record);
    }

    public function testGetRecordInfos(): void
    {
        $record_1 = [
            'obj_id' => '32',
            'identifier' => 'id32',
            'datestamp' => '123456'
        ];
        $record_2 = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653'
        ];
        $record_3 = [
            'obj_id' => '1',
            'identifier' => 'id1',
            'datestamp' => '65656565'
        ];
        $repo = $this->getRepository(0, [$record_1, $record_2, $record_3]);

        $records = iterator_to_array($repo->getRecordInfos());

        $this->assertSame(
            ['SELECT obj_id, identifier, datestamp FROM il_meta_oer_exposed ORDER BY obj_id'],
            $repo->exposed_queries
        );
        $this->assertCount(3, $records);
        $this->assertRecordInfosMatchesArray($records[0], $record_1);
        $this->assertRecordInfosMatchesArray($records[1], $record_2);
        $this->assertRecordInfosMatchesArray($records[2], $record_3);
    }

    public function testGetRecordInfosWithFromDate(): void
    {
        $record_1 = [
            'obj_id' => '32',
            'identifier' => 'id32',
            'datestamp' => '123456'
        ];
        $record_2 = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653'
        ];
        $repo = $this->getRepository(0, [$record_1, $record_2]);

        $records = iterator_to_array($repo->getRecordInfos(
            new \DateTimeImmutable('@1723994')
        ));

        $this->assertSame(
            ['SELECT obj_id, identifier, datestamp' . ' FROM il_meta_oer_exposed WHERE datestamp >= ~int:1723994~ ORDER BY obj_id'],
            $repo->exposed_queries
        );
        $this->assertCount(2, $records);
        $this->assertRecordInfosMatchesArray($records[0], $record_1);
        $this->assertRecordInfosMatchesArray($records[1], $record_2);
    }

    public function testGetRecordInfosWithUntilDate(): void
    {
        $record_2 = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653'
        ];
        $record_3 = [
            'obj_id' => '1',
            'identifier' => 'id1',
            'datestamp' => '65656565'
        ];
        $repo = $this->getRepository(0, [$record_2, $record_3]);

        $records = iterator_to_array($repo->getRecordInfos(
            null,
            new \DateTimeImmutable('@1763994')
        ));

        $this->assertSame(
            ['SELECT obj_id, identifier, datestamp' . ' FROM il_meta_oer_exposed WHERE datestamp <= ~int:1763994~ ORDER BY obj_id'],
            $repo->exposed_queries
        );
        $this->assertCount(2, $records);
        $this->assertRecordInfosMatchesArray($records[0], $record_2);
        $this->assertRecordInfosMatchesArray($records[1], $record_3);
    }

    public function testGetRecordInfosWithFromAndUntilDates(): void
    {
        $record_2 = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653'
        ];
        $repo = $this->getRepository(0, [$record_2]);

        $records = iterator_to_array($repo->getRecordInfos(
            new \DateTimeImmutable('@1723994'),
            new \DateTimeImmutable('@1763994')
        ));

        $this->assertSame(
            ['SELECT obj_id, identifier, datestamp' . ' FROM il_meta_oer_exposed WHERE datestamp >= ~int:1723994~ AND datestamp <= ~int:1763994~ ORDER BY obj_id'],
            $repo->exposed_queries
        );
        $this->assertCount(1, $records);
        $this->assertRecordInfosMatchesArray($records[0], $record_2);
    }

    public function testGetRecordInfosWithLimit(): void
    {
        $record = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653'
        ];
        $repo = $this->getRepository(0, [$record]);

        $records = iterator_to_array($repo->getRecordInfos(
            null,
            null,
            5
        ));

        $this->assertSame(
            ['SELECT obj_id, identifier, datestamp' . ' FROM il_meta_oer_exposed ORDER BY obj_id LIMIT ~int:5~'],
            $repo->exposed_queries
        );
        $this->assertCount(1, $records);
        $this->assertRecordInfosMatchesArray($records[0], $record);
    }

    public function testGetRecordInfosWithOffset(): void
    {
        $record = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653'
        ];
        $repo = $this->getRepository(0, [$record]);

        $records = iterator_to_array($repo->getRecordInfos(
            null,
            null,
            null,
            5
        ));

        $this->assertSame(
            ['SELECT obj_id, identifier, datestamp' . ' FROM il_meta_oer_exposed ORDER BY obj_id LIMIT ~int:' . PHP_INT_MAX . '~ OFFSET ~int:5~'],
            $repo->exposed_queries
        );
        $this->assertCount(1, $records);
        $this->assertRecordInfosMatchesArray($records[0], $record);
    }

    public function testGetRecordInfosWithLimitAndOffset(): void
    {
        $record = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653'
        ];
        $repo = $this->getRepository(0, [$record]);

        $records = iterator_to_array($repo->getRecordInfos(
            null,
            null,
            5,
            10
        ));

        $this->assertSame(
            ['SELECT obj_id, identifier, datestamp' . ' FROM il_meta_oer_exposed ORDER BY obj_id LIMIT ~int:5~ OFFSET ~int:10~'],
            $repo->exposed_queries
        );
        $this->assertCount(1, $records);
        $this->assertRecordInfosMatchesArray($records[0], $record);
    }

    public function testGetRecordCount(): void
    {
        $repo = $this->getRepository(0, [['num' => 4]]);

        $count = $repo->getRecordCount();

        $this->assertSame(
            ['SELECT COUNT(*) AS num FROM il_meta_oer_exposed'],
            $repo->exposed_queries
        );
        $this->assertSame(4, $count);
    }

    public function testGetRecordCountWithFromDate(): void
    {
        $repo = $this->getRepository(0, [['num' => 3]]);

        $count = $repo->getRecordCount(
            new \DateTimeImmutable('@1723994')
        );

        $this->assertSame(
            ['SELECT' . ' COUNT(*) AS num FROM il_meta_oer_exposed WHERE datestamp >= ~int:1723994~'],
            $repo->exposed_queries
        );
        $this->assertSame(3, $count);
    }

    public function testGetRecordCountWithUntilDate(): void
    {
        $repo = $this->getRepository(0, [['num' => 3]]);

        $count = $repo->getRecordCount(
            null,
            new \DateTimeImmutable('@1763994')
        );

        $this->assertSame(
            ['SELECT' . ' COUNT(*) AS num FROM il_meta_oer_exposed WHERE datestamp <= ~int:1763994~'],
            $repo->exposed_queries
        );
        $this->assertSame(3, $count);
    }

    public function testGetRecordCountWithFromAndUntilDates(): void
    {
        $repo = $this->getRepository(0, [['num' => 2]]);

        $count = $repo->getRecordCount(
            new \DateTimeImmutable('@1723994'),
            new \DateTimeImmutable('@1763994')
        );

        $this->assertSame(
            ['SELECT' . ' COUNT(*) AS num FROM il_meta_oer_exposed WHERE datestamp >= ~int:1723994~ AND datestamp <= ~int:1763994~'],
            $repo->exposed_queries
        );
        $this->assertSame(2, $count);
    }

    public function testGetEarliestDatestamp(): void
    {
        $repo = $this->getRepository(0, [['earliest' => '1795857']]);

        $earliest = $repo->getEarliestDatestamp();

        $this->assertSame(
            ['SELECT MIN(datestamp) AS earliest FROM il_meta_oer_exposed'],
            $repo->exposed_queries
        );
        $this->assertSame(1795857, $earliest->getTimestamp());
    }

    public function testGetRecordByIdentifier(): void
    {
        $record = [
            'obj_id' => '456',
            'identifier' => 'id456',
            'datestamp' => '9345653',
            'metadata' => '<content><sub1>hello</sub1><sub2>world</sub2></content>'
        ];
        $repo = $this->getRepository(0, [$record]);

        $res = $repo->getRecordByIdentifier('id456');

        $this->assertSame(
            ['SELECT * FROM il_meta_oer_exposed WHERE identifier = ~string:id456~'],
            $repo->exposed_queries
        );
        $this->assertNotNull($res);
        $this->assertRecordMatchesArray($res, $record);
    }

    public function testGetRecordByIdentifierNotFound(): void
    {
        $repo = $this->getRepository(0, []);

        $res = $repo->getRecordByIdentifier('id456');

        $this->assertSame(
            ['SELECT * FROM il_meta_oer_exposed WHERE identifier = ~string:id456~'],
            $repo->exposed_queries
        );
        $this->assertNull($res);
    }

    public function testDoesRecordWithIdentifierExistTrue(): void
    {
        $repo = $this->getRepository(0, [['num' => 1]]);

        $exists = $repo->doesRecordWithIdentifierExist('some id');

        $this->assertSame(
            ['SELECT COUNT(*) AS num FROM il_meta_oer_exposed WHERE identifier = ~string:some id~'],
            $repo->exposed_queries
        );
        $this->assertTrue($exists);
    }

    public function testDoesRecordWithIdentifierExistFalse(): void
    {
        $repo = $this->getRepository(0, [['num' => 0]]);

        $exists = $repo->doesRecordWithIdentifierExist('some id');

        $this->assertSame(
            ['SELECT COUNT(*) AS num FROM il_meta_oer_exposed WHERE identifier = ~string:some id~'],
            $repo->exposed_queries
        );
        $this->assertFalse($exists);
    }

    public function testDoesRecordExistForObjIDTrue(): void
    {
        $repo = $this->getRepository(0, [['num' => 1]]);

        $exists = $repo->doesRecordExistForObjID(43);

        $this->assertSame(
            ['SELECT ' . 'COUNT(*) AS num FROM il_meta_oer_exposed WHERE obj_id = ~int:43~'],
            $repo->exposed_queries
        );
        $this->assertTrue($exists);
    }

    public function testDoesRecordExistForObjIDFalse(): void
    {
        $repo = $this->getRepository(0, [['num' => 0]]);

        $exists = $repo->doesRecordExistForObjID(43);

        $this->assertSame(
            ['SELECT ' . 'COUNT(*) AS num FROM il_meta_oer_exposed WHERE obj_id = ~int:43~'],
            $repo->exposed_queries
        );
        $this->assertFalse($exists);
    }

    public function testCreateRecord(): void
    {
        $xml = new \DOMDocument();
        $xml->loadXML('<content><sub1>hello</sub1><sub2>world</sub2></content>');

        $repo = $this->getRepository(17646362, []);

        $repo->createRecord(
            32,
            'id32',
            $xml
        );

        $this->assertSame(
            [
                'INSERT INTO il_meta_oer_exposed (obj_id, identifier, datestamp, metadata) VALUES (' .
                '~int:32~, ~string:id32~, ~int:17646362~, ~clob:' . $xml->saveXML() . '~)'
            ],
            $repo->exposed_queries
        );
    }

    public function testUpdateRecord(): void
    {
        $xml = new \DOMDocument();
        $xml->loadXML('<content><sub1>hello</sub1><sub2>world</sub2></content>');

        $repo = $this->getRepository(17646362, []);

        $repo->updateRecord(
            32,
            $xml
        );

        $this->assertSame(
            [
                'UPDATE il_meta_oer_exposed SET metadata = ~clob:' . $xml->saveXML() . '~, ' .
                'datestamp = ~int:17646362~ WHERE obj_id = ~int:32~'
            ],
            $repo->exposed_queries
        );
    }

    public function testDeleteRecord(): void
    {
        $repo = $this->getRepository(0, []);

        $repo->deleteRecord(32);

        $this->assertSame(
            ['DELETE ' . 'FROM il_meta_oer_exposed WHERE obj_id = ~int:32~'],
            $repo->exposed_queries
        );
    }
}
