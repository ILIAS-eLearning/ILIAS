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

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\DataProvider;

class ilCertificateDateHelperTest extends ilCertificateBaseTestCase
{
    private const string USER_TIME_ZONE = 'Europe/Berlin';
    private const string DEFAULT_TIME_ZONE = 'UTC';

    private int $current_time;

    protected function setUp(): void
    {
        parent::setUp();

        class_exists('ilDateTime');

        ilTimeZone::_setDefaultTimeZone(self::DEFAULT_TIME_ZONE);

        $logger = $this->getMockBuilder(ilLogger::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $logger_factory = $this->getMockBuilder(ilLoggerFactory::class)
                               ->disableOriginalConstructor()
                               ->onlyMethods(['getComponentLogger'])
                               ->getMock();
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);
        $this->setGlobalVariable('lng', $this->getSystemLanguageMock());
        $this->setGlobalVariable('ilUser', $this->getUserMock());
        $this->current_time = time();
    }

    protected function tearDown(): void
    {
        ilTimeZone::_restoreDefaultTimeZone();
        parent::tearDown();
    }

    public static function dataProviderFormatDateWithDateFormat(): array
    {
        return [
            [null, 'No date'],
            ['2001-01-01', '1. Jan 2001'],
            ['2001-01-01 00:00:00', '1. Jan 2001'],
            [20010101, '1. Jan 2001'],
            [0, 'No date'],
            ['', 'No date'],
        ];
    }

    #[DataProvider('dataProviderFormatDateWithDateFormat')]
    public function testFormatDateWithDefaultFormat($input, $output): void
    {
        $helper = new ilCertificateDateHelper();
        $this->assertEquals($output, $helper->formatDate($input));
    }

    public function doesNotChangeUseRelativeDates(): void
    {
        $used_relative_dates = ilDatePresentation::useRelativeDates();

        $helper = new ilCertificateDateHelper();
        $helper->formatDate('2001-01-01');
        $this->assertEquals($used_relative_dates, ilDatePresentation::useRelativeDates());

        ilDatePresentation::setUseRelativeDates(true);
        $helper->formatDate('2001-01-01');
        $this->assertTrue(ilDatePresentation::useRelativeDates());

        ilDatePresentation::setUseRelativeDates(false);
        $helper->formatDate('2001-01-01');
        $this->assertFalse(ilDatePresentation::useRelativeDates());

        ilDatePresentation::setUseRelativeDates($used_relative_dates);
    }

    public function testUnixFormatIsCastedToString(): void
    {
        $helper = new ilCertificateDateHelper();
        $this->assertEquals(
            $helper->formatDate((string) $this->current_time, null, IL_CAL_UNIX),
            $helper->formatDate($this->current_time, null, IL_CAL_UNIX)
        );
    }

    public function testDateTimeFormatIsCastToInt(): void
    {
        $helper = new ilCertificateDateHelper();
        $this->assertEquals(
            $helper->formatDate('20010101', null, IL_CAL_DATE),
            $helper->formatDate(20010101, null, IL_CAL_DATE)
        );
    }

    public function testFormatDateWithUnixFormat(): void
    {
        $helper = new ilCertificateDateHelper();
        $this->assertEquals(
            $helper->formatDate((string) $this->current_time, null, IL_CAL_UNIX),
            $helper->formatDate($this->current_time, null, IL_CAL_UNIX)
        );
        $this->assertEquals('1. Jan 2024', $helper->formatDate(1704067200, null, IL_CAL_UNIX));
        $this->assertNotEquals('Today', $helper->formatDate($this->current_time, null, IL_CAL_UNIX));
    }

    public static function dataProviderFormatDateTimeWithDateTimeFormat(): array
    {
        return [
            [null, 'No date'],
            ['2001-01-01 00:00:00', '1. Jan 2001, 01:00'],
            [20010101000000, '1. Jan 2001, 01:00'],
            [0, 'No date'],
            ['', 'No date'],
        ];
    }

    #[DataProvider('dataProviderFormatDateTimeWithDateTimeFormat')]
    public function testFormatDateTimeWithDefaultFormat($input, $output): void
    {
        $helper = new ilCertificateDateHelper();
        $this->assertEquals($output, $helper->formatDateTime($input));
    }

    /**
     * @return array{'formatDateTime'|'formatDate', array{0: string}}
     */
    public static function invalidDateProvider(): array
    {
        return [
            'formatDateTime' => ['formatDateTime'],
            'formatDate' => ['formatDate'],
        ];
    }

    #[DataProvider('invalidDateProvider')]
    public function testCannotFormatString(string $method): void
    {
        $helper = new ilCertificateDateHelper();
        $this->expectExceptionMessage('Cannot parse date: invalid-date');
        $helper->$method('invalid-date');
    }

    #[DataProvider('invalidDateProvider')]
    public function testCannotParseTimestampWithDateTimeFormat(string $method): void
    {
        $helper = new ilCertificateDateHelper();
        $this->expectExceptionMessage('Cannot parse date: ' . $this->current_time);
        $helper->$method($this->current_time);
    }

    public function testFormatDateWithoutRelativeDates(): void
    {
        $helper = new ilCertificateDateHelper();
        $this->assertNotEquals('Today', $helper->formatDateTime($this->current_time, null, IL_CAL_UNIX));

        $used_relative_dates = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(true);
        $this->assertNotEquals('Today', $helper->formatDateTime($this->current_time, null, IL_CAL_UNIX));
        ilDatePresentation::setUseRelativeDates($used_relative_dates);
    }

    #[RunInSeparateProcess]
    public function testFormatDateWithUserLanguage(): void
    {
        $this->mockUserLanguageGerman();

        $helper = new ilCertificateDateHelper();
        $this->assertEquals('1. Mai 2001', $helper->formatDate('2001-05-01 01:30:59', $this->getUserMock()));
    }

    #[RunInSeparateProcess]
    public function testFormatDateTimeWithUserLanguage(): void
    {
        $this->mockUserLanguageGerman();

        $helper = new ilCertificateDateHelper();
        $this->assertEquals('1. Mai 2001, 03:30', $helper->formatDateTime('2001-05-01 01:30:59', $this->getUserMock()));
    }

    private function getUserMock(): ilObjUser
    {
        $user = $this->getMockBuilder(ilObjUser::class)
                     ->disableOriginalConstructor()
                     ->onlyMethods(['getTimeFormat', 'getLanguage', 'getTimeZone'])
                     ->getMock();
        $user->prefs = ['language' => 'de'];
        $user->method('getTimeFormat')->willReturn((string) ilCalendarSettings::TIME_FORMAT_24);
        $user->method('getLanguage')->willReturn('de');
        $user->method('getTimeZone')->willReturn(self::USER_TIME_ZONE);

        return $user;
    }

    private function getSystemLanguageMock(): ilLanguage
    {
        $lng = $this->getMockBuilder(ilLanguage::class)
                    ->onlyMethods(['txt', 'loadLanguageModule'])
                    ->disableOriginalConstructor()
                    ->getMock();
        $lng->method('txt')->willReturnCallback(function (string $topic): string {
            return match ($topic) {
                'month_01_short' => 'Jan',
                'month_01_long' => 'January',
                'month_02_short' => 'Feb',
                'month_03_short' => 'Mar',
                'month_04_short' => 'Apr',
                'month_05_short' => 'May',
                'month_06_short' => 'Jun',
                'month_07_short' => 'Jul',
                'month_08_short' => 'Aug',
                'month_09_short' => 'Sep',
                'month_10_short' => 'Oct',
                'month_11_short' => 'Nov',
                'month_12_short' => 'Dec',
                'no_date' => 'No date',
                'today' => 'Today',
                'yesterday' => 'Yesterday',
                'tomorrow' => 'Tomorrow',
                default => '-' . $topic . '-'
            };
        });

        return $lng;
    }

    private function mockUserLanguageGerman(): void
    {
        if (!defined('ILIAS_LOG_ENABLED')) {
            define('ILIAS_LOG_ENABLED', false);
        }
        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', dirname(__FILE__, 5));
        }

        $ilClientIniFile = $this->getMockBuilder(ilIniFile::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->setGlobalVariable('ilClientIniFile', $ilClientIniFile);

        $ilDB = $this->createMock(ilDBInterface::class);
        $ilDB->method('query')->willReturnCallback(function ($query): ilDBStatement {
            $statement = $this->createMock(ilDBStatement::class);

            if (str_contains($query, 'SELECT * FROM lng_modules')) {
                $statement->method('numRows')->willReturn(1);
                $statement->method('fetchRow')->willReturn([
                    'lang_array' => serialize([
                        'month_01_short' => 'Jan',
                        'month_01_long' => 'Januar',
                        'month_02_short' => 'Feb',
                        'month_03_short' => 'Mär',
                        'month_04_short' => 'Apr',
                        'month_05_short' => 'Mai',
                        'month_06_short' => 'Jun',
                        'month_07_short' => 'Jul',
                        'month_08_short' => 'Aug',
                        'month_09_short' => 'Sep',
                        'month_10_short' => 'Okt',
                        'month_11_short' => 'Nov',
                        'month_12_short' => 'Dez',
                        'no_date' => 'Kein Datum',
                        'today' => 'Heute',
                        'yesterday' => 'Gestern',
                        'tomorrow' => 'Morgen'
                    ]),
                ]);
            } else {
                $statement->method('numRows')->willReturn(0);
                $statement->method('fetchRow')->willReturn(false);
            }

            return $statement;
        });

        $this->setGlobalVariable('ilDB', $ilDB);
    }
}
