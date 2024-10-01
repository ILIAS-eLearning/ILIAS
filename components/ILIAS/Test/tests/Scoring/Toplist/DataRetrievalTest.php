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

namespace Results\Toplist;

use ILIAS\Data\Factory;
use ILIAS\Test\Results\Toplist\TestTopListRepository;
use ILIAS\Test\Results\Toplist\DataRetrieval;
use ILIAS\Test\Results\Toplist\TopListOrder;
use ILIAS\Test\Results\Toplist\TopListType;

/**
 * Class DataRetrievalTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class DataRetrievalTest extends \ilTestBaseTestCase
{
    private DataRetrieval $tableObj;

    private \ilObjTest $testObjMock;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();
        $this->addGlobal_ilUser();

        $this->testObjMock = $this->getTestObjMock();

        $this->tableObj = new DataRetrieval(
            $this->testObjMock,
            $this->createMock(TestTopListRepository::class),
            $DIC['lng'],
            $DIC['ilUser'],
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $this->createMock(Factory::class),
            TopListType::GENERAL,
            TopListOrder::BY_SCORE
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(DataRetrieval::class, $this->tableObj);
    }

    public function test_getColumns_shouldReturnAllColumns(): void
    {
        $this->testObjMock->method('getHighscoreAchievedTS')->willReturn(true);
        $this->testObjMock->method('getHighscoreScore')->willReturn(true);
        $this->testObjMock->method('getHighscorePercentage')->willReturn(true);
        $this->testObjMock->method('getHighscoreHints')->willReturn(true);
        $this->testObjMock->method('getHighscoreWTime')->willReturn(true);

        $columns = $this->tableObj->getColumns();
        $this->assertIsArray($columns);
        $this->assertNotEmpty($columns);
        $this->assertArrayHasKey('is_actor', $columns);
        $this->assertArrayHasKey('rank', $columns);
        $this->assertArrayHasKey('participant', $columns);
        $this->assertArrayHasKey('achieved', $columns);
        $this->assertArrayHasKey('score', $columns);
        $this->assertArrayHasKey('percentage', $columns);
        $this->assertArrayHasKey('hints', $columns);
        $this->assertArrayHasKey('workingtime', $columns);
    }

    public function test_getColumns_shouldReturnOnlySelectedColumns(): void
    {
        $this->testObjMock->method('getHighscoreAchievedTS')->willReturn(true);
        $this->testObjMock->method('getHighscoreScore')->willReturn(false);
        $this->testObjMock->method('getHighscorePercentage')->willReturn(true);
        $this->testObjMock->method('getHighscoreHints')->willReturn(true);
        $this->testObjMock->method('getHighscoreWTime')->willReturn(false);

        $columns = $this->tableObj->getColumns();
        $this->assertIsArray($columns);
        $this->assertNotEmpty($columns);
        $this->assertArrayHasKey('achieved', $columns);
        $this->assertArrayHasKey('percentage', $columns);
        $this->assertArrayHasKey('hints', $columns);

        $this->assertArrayNotHasKey('score', $columns);
        $this->assertArrayNotHasKey('workingtime', $columns);
    }

    public function test_formatTime_shouldReturnZeroTime(): void
    {
        $this->assertEquals('00:00:00', $this->tableObj->formatTime(0));
    }

    public function test_formatTime_shouldReturnFormattedTime(): void
    {
        $this->assertEquals('01:00:00', $this->tableObj->formatTime(3600));
        $this->assertEquals('00:02:00', $this->tableObj->formatTime(120));
        $this->assertEquals('00:02:01', $this->tableObj->formatTime(121));
        $this->assertEquals('03:09:40', $this->tableObj->formatTime(11380));
    }
}
