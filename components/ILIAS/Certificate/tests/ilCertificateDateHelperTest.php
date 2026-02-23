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
            [978307200, '1. Jan 2001'],
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

    public static function dataProviderFormatDateTimeWithDateTimeFormat(): array
    {
        return [
            [null, 'No date'],
            ['2001-01-01 00:00:00', '1. Jan 2001, 01:00'],
            [978307200, '1. Jan 2001, 01:00'],
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

    public function testHelperDoesNotChangeUseRelativeDates(): void
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

    public static function provideExplicitFormatCases(): array
    {
        $ts = 1757609100; // See snippet in: https://github.com/ILIAS-eLearning/ILIAS/pull/10084

        class_exists('ilDateTime');

        return [
            'date: unix-int ok' => ['date', $ts, IL_CAL_UNIX, null, 'unix timestamp (int) accepted for date'],
            'date: unix-string ok' => [
                'date',
                (string) $ts,
                IL_CAL_UNIX,
                null,
                'unix timestamp (string) accepted for date'
            ],
            'datetime: unix-int ok' => [
                'datetime',
                $ts,
                IL_CAL_UNIX,
                null,
                'unix timestamp (int) accepted for datetime'
            ],
            'datetime: unix-string ok' => [
                'datetime',
                (string) $ts,
                IL_CAL_UNIX,
                null,
                'unix timestamp (string) accepted for datetime'
            ],

            'date: negative-unix ok' => ['date', -1, IL_CAL_UNIX, null, 'negative unix timestamp accepted as int'],
            'datetime: negative-unix ok' => [
                'datetime',
                -1,
                IL_CAL_UNIX,
                null,
                'negative unix timestamp accepted as int'
            ],

            'date: date ok' => ['date', '2025-09-12', IL_CAL_DATE, null, 'valid date string'],
            'datetime: datetime ok' => [
                'datetime',
                '2025-09-12 20:30:00',
                IL_CAL_DATETIME,
                null,
                'valid date-time string'
            ],
            'date: YYYYMMDD ok' => ['date', '20250912', IL_CAL_DATE, null, 'numeric YYYYMMDD is not treated as unix'],

            'date: unix-string with DATE throws' => [
                'date',
                (string) $ts,
                IL_CAL_DATE,
                InvalidArgumentException::class,
                'unix-like string with DATE format should be rejected'
            ],
            'datetime: unix-string with DATETIME throws' => [
                'datetime',
                (string) $ts,
                IL_CAL_DATETIME,
                InvalidArgumentException::class,
                'unix-like string with DATETIME format should be rejected'
            ],
            'date: date with UNIX throws' => [
                'date',
                '2025-09-12',
                IL_CAL_UNIX,
                InvalidArgumentException::class,
                'non-numeric with UNIX should be rejected'
            ],
            'datetime: datetime with UNIX throws' => [
                'datetime',
                '2025-09-12 20:30:00',
                IL_CAL_UNIX,
                InvalidArgumentException::class,
                'non-numeric with UNIX should be rejected'
            ],
        ];
    }

    /**
     * @param int|string                   $input
     * @param class-string<Throwable>|null $expected_exception
     */
    #[DataProvider('provideExplicitFormatCases')]
    public function testExplicitFormatsWorkAsExpected(
        string $method,
        $input,
        int $format,
        ?string $expected_exception,
        string $why
    ): void {
        $helper = new ilCertificateDateHelper();

        $cb = static function () use ($helper, $method, $input, $format) {
            return $method === 'date'
                ? $helper->formatDate($input, null, $format)
                : $helper->formatDateTime($input, null, $format);
        };

        if ($expected_exception === null) {
            $this->assertDoesNotThrow($cb, $why);
        } else {
            $this->assertThrows($cb, $expected_exception, null);
        }
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function dateFormattingMethodsProvider(): array
    {
        return [
            'formatDateTime' => ['formatDateTime'],
            'formatDate' => ['formatDate'],
        ];
    }

    #[DataProvider('dateFormattingMethodsProvider')]
    public function testCannotFormatNonDateString(string $method): void
    {
        $helper = new ilCertificateDateHelper();
        $this->expectExceptionMessage('Cannot parse date: invalid-date');
        $helper->$method('invalid-date');
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
