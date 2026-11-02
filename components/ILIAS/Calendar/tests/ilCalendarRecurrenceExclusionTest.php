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

use ILIAS\DI\Container;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[BackupGlobals(false)]
class ilCalendarRecurrenceExclusionTest extends TestCase
{
    private mixed $previous_dic;
    private string $previous_timezone;
    private string $previous_php_timezone;
    private ilDBInterface $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previous_dic = $GLOBALS['DIC'] ?? null;
        $this->previous_timezone = ilTimeZone::_getDefaultTimeZone();
        $this->previous_php_timezone = date_default_timezone_get();

        $logger = $this->createMock(ilLogger::class);
        $factory = $this->getMockBuilder(ilLoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getComponentLogger'])
            ->getMock();
        $factory->method('getComponentLogger')->willReturn($logger);

        $dic = new Container();
        $this->db = $this->createMock(ilDBInterface::class);
        $dic['ilDB'] = $this->db;
        $dic['lng'] = $this->createMock(ilLanguage::class);
        $dic['ilErr'] = $this->createMock(ilErrorHandling::class);
        $dic['ilLoggerFactory'] = $factory;
        $GLOBALS['DIC'] = $dic;
        ilTimeZone::_setDefaultTimeZone('UTC');
    }

    protected function tearDown(): void
    {
        ilTimeZone::_setDefaultTimeZone($this->previous_timezone);
        date_default_timezone_set($this->previous_php_timezone);
        if ($this->previous_dic === null) {
            unset($GLOBALS['DIC']);
        } else {
            $GLOBALS['DIC'] = $this->previous_dic;
        }
        parent::tearDown();
    }

    public static function exclusions(): array
    {
        return [
            'summer to winter' => ['2024-07-02 07:15:00', '2024-12-03', 'Europe/Berlin', false, 'EXDATE:20241203T081500Z'],
            'winter to summer' => ['2024-01-02 08:15:00', '2024-07-02', 'Europe/Berlin', false, 'EXDATE:20240702T071500Z'],
            'same season' => ['2024-07-02 07:15:00', '2024-07-09', 'Europe/Berlin', false, 'EXDATE:20240709T071500Z'],
            'previous UTC date' => ['2024-07-01 22:30:00', '2024-12-03', 'Europe/Berlin', false, 'EXDATE:20241202T233000Z'],
            'next UTC date' => ['2024-07-03 03:30:00', '2024-12-03', 'America/New_York', false, 'EXDATE:20241204T043000Z'],
            'southern hemisphere' => ['2024-01-01 22:15:00', '2024-07-02', 'Australia/Sydney', false, 'EXDATE:20240701T231500Z'],
            'half-hour DST change' => ['2024-01-01 22:15:00', '2024-07-02', 'Australia/Lord_Howe', false, 'EXDATE:20240701T224500Z'],
            'zone without DST' => ['2024-07-02 03:45:00', '2024-12-03', 'Asia/Kolkata', false, 'EXDATE:20241203T034500Z'],
            'UTC recurrence' => ['2024-07-02 07:15:00', '2024-12-03', 'UTC', false, 'EXDATE:20241203T071500Z'],
            'default timezone' => ['2024-07-02 07:15:00', '2024-12-03', '', false, 'EXDATE:20241203T071500Z'],
            'all-day event' => ['2024-07-02 00:00:00', '2024-12-03', 'Europe/Berlin', true, 'EXDATE;VALUE=DATE:20241203'],
        ];
    }

    #[DataProvider('exclusions')]
    public function testExclusionMatchesOccurrence(
        string $start,
        string $excluded_date,
        string $timezone,
        bool $all_day,
        string $expected
    ): void {
        $statement = $this->createMock(ilDBStatement::class);
        $statement->method('fetchRow')->willReturnOnConsecutiveCalls((object) [
            'last_update' => '2024-01-01 00:00:00',
            'title' => 'Recurring meeting',
            'subtitle' => '',
            'description' => '',
            'location' => '',
            'informations' => '',
            'fullday' => $all_day,
            'auto_generated' => false,
            'context_id' => 0,
            'context_info' => '',
            'translation_type' => 0,
            'notification' => false,
            'starta' => $start,
            'enda' => $start,
        ], false);
        $this->db->method('query')->willReturn($statement);

        $exclusion = new ilCalendarRecurrenceExclusion();
        $exclusion->setEntryId(42);
        $exclusion->setDate(new ilDate($excluded_date, IL_CAL_DATE));

        $this->assertSame($expected, $timezone === '' ? $exclusion->toICal() : $exclusion->toICal($timezone));
    }
}
