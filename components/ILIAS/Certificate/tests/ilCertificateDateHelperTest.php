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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;

class ilCertificateDateHelperTest extends ilCertificateBaseTestCase
{
    private int $current_time;

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../Calendar/classes/class.ilDateTime.php';
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

    public static function dataProviderFormatDate(): array
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

    #[DataProvider('dataProviderFormatDate')]
    public function testFormatDateWithDefaultFormat($input, $output): void
    {
        $helper = new ilCertificateDateHelper();
        $this->assertEquals($output, $helper->formatDate($input));
    }

    public function doesNotChangeUseRelativeDates(): void
    {
        $oldDatePresentationValue = ilDatePresentation::useRelativeDates();
        $helper = new ilCertificateDateHelper();
        $helper->formatDate('2001-01-01');
        $this->assertEquals($oldDatePresentationValue, ilDatePresentation::useRelativeDates());

        ilDatePresentation::setUseRelativeDates(true);
        $helper->formatDate('2001-01-01');
        $this->assertTrue(ilDatePresentation::useRelativeDates());

        ilDatePresentation::setUseRelativeDates(false);
        $helper->formatDate('2001-01-01');
        $this->assertFalse(ilDatePresentation::useRelativeDates());

        ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);
    }

    public function testUnixFormatIsCastToString(): void
    {
        $helper = new ilCertificateDateHelper();
        $this->assertNotEmpty($helper->formatDate(time(), null, IL_CAL_UNIX));
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

    public static function dataProviderFormatDateTime(): array
    {
        return [
            [null, 'No date'],
            ['2001-01-01 00:00:00', '1. Jan 2001, 00:00'],
            [20010101000000, '1. Jan 2001, 00:00'],
            [0, 'No date'],
            ['', 'No date'],
        ];
    }

    #[DataProvider('dataProviderFormatDateTime')]
    public function testFormatDateTimeWithDefaultFormat($input, $output): void
    {
        $helper = new ilCertificateDateHelper();
        $this->assertEquals($output, $helper->formatDateTime($input));
    }

    public function testCannotFormatString(): void
    {
        $helper = new ilCertificateDateHelper();
        $this->expectExceptionMessage('Cannot parse date: invalid-date');
        $helper->formatDateTime('invalid-date');
        $this->expectExceptionMessage('Cannot parse date: invalid-date');
        $helper->formatDate('invalid-date');
    }

    public function testCannotParseTimestampWithDateTimeFormat(): void
    {
        $helper = new ilCertificateDateHelper();
        $this->expectExceptionMessage('Cannot parse date: ' . $this->current_time);
        $helper->formatDateTime($this->current_time);

        $this->expectExceptionMessage('Cannot parse date: ' . $this->current_time);
        $helper->formatDate($this->current_time);
    }

    public function testFormatDateTimeWithUnixFormat(): void
    {
        $helper = new ilCertificateDateHelper();
        $this->assertEquals(
            $helper->formatDateTime((string) $this->current_time, null, IL_CAL_UNIX),
            $helper->formatDateTime($this->current_time, null, IL_CAL_UNIX)
        );
        $this->assertEquals('1. Jan 2024, 00:00', $helper->formatDateTime(1704067200, null, IL_CAL_UNIX));
        $this->assertNotEquals('Today', $helper->formatDateTime($this->current_time, null, IL_CAL_UNIX));
    }

    public function testFormatDateWithUserLanguage(): void
    {
        $ilClientIniFile = $this->getMockBuilder(ilIniFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->setGlobalVariable('ilClientIniFile', $ilClientIniFile);

        if (!defined('ILIAS_LOG_ENABLED')) {
            define('ILIAS_LOG_ENABLED', false);
        }
        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', dirname(__FILE__, 5));
        }
        $ilDB = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ilDB->method('query')->willReturnCallback(function ($query): ilDBStatement {
            $mock_object = $this->createMock(ilDBStatement::class);
            $mock_object->method('fetchRow')->willReturn([
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

            return $mock_object;
        });
        $this->setGlobalVariable('ilDB', $ilDB);

        $helper = new ilCertificateDateHelper();
        $this->assertEquals('1. Mai 2001', $helper->formatDate('2001-05-01 00:00:00', $this->getUserMock()));
        $this->assertEquals('1. Mai 2001, 01:30', $helper->formatDateTime('2001-05-01 01:30:59', $this->getUserMock()));
    }

    private function getUserMock(): ilObjUser
    {
        $user = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->prefs = ['language' => 'de'];
        $user->method('getTimeFormat')->willReturn((string) ilCalendarSettings::TIME_FORMAT_24);
        $user->method('getLanguage')->willReturn('de');

        return $user;
    }

    private function getSystemLanguageMock(): ilLanguage
    {
        $lng = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lng->method('txt')->willReturnMap([
            ['month_01_short', '', 'Jan'],
            ['month_01_long', '', 'January'],
            ['month_02_short', '', 'Feb'],
            ['month_03_short', '', 'Mar'],
            ['month_04_short', '', 'Apr'],
            ['month_05_short', '', 'May'],
            ['month_06_short', '', 'Jun'],
            ['month_07_short', '', 'Jul'],
            ['month_08_short', '', 'Aug'],
            ['month_09_short', '', 'Sep'],
            ['month_10_short', '', 'Oct'],
            ['month_11_short', '', 'Nov'],
            ['month_12_short', '', 'Dec'],
            ['no_date', '', 'No date'],
            ['today', '', 'Today'],
            ['yesterday', '', 'Yesterday'],
            ['tomorrow', '', 'Tomorrow'],
        ]);

        return $lng;
    }
}
