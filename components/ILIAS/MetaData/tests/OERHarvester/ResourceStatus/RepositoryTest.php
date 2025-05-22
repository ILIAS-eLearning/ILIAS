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

namespace ILIAS\MetaData\OERHarvester\ResourceStatus;

use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    protected function getRepository(
        array $returns_on_query
    ): DatabaseRepository {
        return new class ($returns_on_query) extends DatabaseRepository {
            public array $exposed_queries = [];

            public function __construct(protected array $returns_on_query)
            {
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

            protected function inWithIntegers(string $field, int ...$integers): string
            {
                return '~' . $field . '~in~(' . implode(',', $integers) . ')~';
            }
        };
    }

    public function testIsHarvestingBlockedTrue(): void
    {
        $repo = $this->getRepository([['blocked' => '1']]);

        $blocked = $repo->isHarvestingBlocked(32);

        $this->assertSame(
            ['SELECT' . ' blocked FROM il_meta_oer_stat WHERE obj_id = ~int:32~'],
            $repo->exposed_queries
        );
        $this->assertTrue($blocked);
    }

    public function testIsHarvestingBlockedFalse(): void
    {
        $repo = $this->getRepository([['blocked' => '0']]);

        $blocked = $repo->isHarvestingBlocked(32);

        $this->assertSame(
            ['SELECT' . ' blocked FROM il_meta_oer_stat WHERE obj_id = ~int:32~'],
            $repo->exposed_queries
        );
        $this->assertFalse($blocked);
    }

    public function testIsHarvestingBlockedFalseNotFound(): void
    {
        $repo = $this->getRepository([]);

        $blocked = $repo->isHarvestingBlocked(32);

        $this->assertSame(
            ['SELECT' . ' blocked FROM il_meta_oer_stat WHERE obj_id = ~int:32~'],
            $repo->exposed_queries
        );
        $this->assertFalse($blocked);
    }

    public function testSetHarvestingBlockedTrue(): void
    {
        $repo = $this->getRepository([]);

        $repo->setHarvestingBlocked(32, true);

        $this->assertSame(
            [
                'INSERT' . ' INTO il_meta_oer_stat (obj_id, href_id, blocked) VALUES (' .
                '~int:32~, ~int:0~, ~int:1~) ON DUPLICATE KEY UPDATE blocked = ~int:1~'
            ],
            $repo->exposed_queries
        );
    }

    public function testSetHarvestingBlockedFalse(): void
    {
        $repo = $this->getRepository([]);

        $repo->setHarvestingBlocked(32, false);

        $this->assertSame(
            [
                'INSERT' . ' INTO il_meta_oer_stat (obj_id, href_id, blocked) VALUES (' .
                '~int:32~, ~int:0~, ~int:0~) ON DUPLICATE KEY UPDATE blocked = ~int:0~'
            ],
            $repo->exposed_queries
        );
    }

    public function testIsAlreadyHarvestedTrue(): void
    {
        $repo = $this->getRepository([['href_id' => '678']]);

        $harvested = $repo->isAlreadyHarvested(32);

        $this->assertSame(
            ['SELECT' . ' href_id FROM il_meta_oer_stat WHERE obj_id = ~int:32~'],
            $repo->exposed_queries
        );
        $this->assertTrue($harvested);
    }

    public function testIsAlreadyHarvestedFalse(): void
    {
        $repo = $this->getRepository([['href_id' => '0']]);

        $harvested = $repo->isAlreadyHarvested(32);

        $this->assertSame(
            ['SELECT' . ' href_id FROM il_meta_oer_stat WHERE obj_id = ~int:32~'],
            $repo->exposed_queries
        );
        $this->assertFalse($harvested);
    }

    public function testIsAlreadyHarvestedFalseNotFound(): void
    {
        $repo = $this->getRepository([]);

        $harvested = $repo->isAlreadyHarvested(32);

        $this->assertSame(
            ['SELECT' . ' href_id FROM il_meta_oer_stat WHERE obj_id = ~int:32~'],
            $repo->exposed_queries
        );
        $this->assertFalse($harvested);
    }

    public function testGetAllHarvestedObjIDs(): void
    {
        $repo = $this->getRepository([['obj_id' => '32'], ['obj_id' => '909'], ['obj_id' => '55']]);

        $obj_ids = iterator_to_array($repo->getAllHarvestedObjIDs());

        $this->assertSame(
            ['SELECT obj_id FROM il_meta_oer_stat WHERE href_id > 0'],
            $repo->exposed_queries
        );
        $this->assertCount(3, $obj_ids);
        $this->assertSame(32, $obj_ids[0]);
        $this->assertSame(909, $obj_ids[1]);
        $this->assertSame(55, $obj_ids[2]);
    }

    public function testGetHarvestRefID(): void
    {
        $repo = $this->getRepository([['href_id' => '90']]);

        $href_id = $repo->getHarvestRefID(32);

        $this->assertSame(
            ['SELECT' . ' href_id FROM il_meta_oer_stat WHERE obj_id = ~int:32~'],
            $repo->exposed_queries
        );
        $this->assertSame(90, $href_id);
    }

    public function testGetHarvestRefIDNotFound(): void
    {
        $repo = $this->getRepository([]);

        $href_id = $repo->getHarvestRefID(32);

        $this->assertSame(
            ['SELECT' . ' href_id FROM il_meta_oer_stat WHERE obj_id = ~int:32~'],
            $repo->exposed_queries
        );
        $this->assertSame(0, $href_id);
    }

    public function testSetHarvestRefID(): void
    {
        $repo = $this->getRepository([]);

        $repo->setHarvestRefID(32, 90);

        $this->assertSame(
            [
                'INSERT' . ' INTO il_meta_oer_stat (obj_id, href_id, blocked) VALUES (' .
                '~int:32~, ~int:90~, ~int:0~) ON DUPLICATE KEY UPDATE href_id = ~int:90~'
            ],
            $repo->exposed_queries
        );
    }

    public function testDeleteHarvestRefID(): void
    {
        $repo = $this->getRepository([]);

        $repo->deleteHarvestRefID(32);

        $this->assertSame(
            ['UPDATE' . ' il_meta_oer_stat SET href_id = 0 WHERE obj_id = ~int:32~'],
            $repo->exposed_queries
        );
    }

    public function testFilterOutBlockedObjects(): void
    {
        $repo = $this->getRepository([['obj_id' => '5'], ['obj_id' => '123'], ['obj_id' => '876']]);

        $obj_ids = iterator_to_array($repo->filterOutBlockedObjects(32, 5, 909, 123, 876, 55));

        $this->assertSame(
            [
                'SELECT' . ' obj_id FROM il_meta_oer_stat WHERE blocked = 1 AND ' .
                '~obj_id~in~(32,5,909,123,876,55)~'
            ],
            $repo->exposed_queries
        );
        $this->assertCount(3, $obj_ids);
        $this->assertSame(32, $obj_ids[0]);
        $this->assertSame(909, $obj_ids[1]);
        $this->assertSame(55, $obj_ids[2]);
    }

    public function testDeleteStatus(): void
    {
        $repo = $this->getRepository([]);

        $repo->deleteStatus(32);

        $this->assertSame(
            ['DELETE FROM' . ' il_meta_oer_stat WHERE obj_id = ~int:32~'],
            $repo->exposed_queries
        );
    }
}
