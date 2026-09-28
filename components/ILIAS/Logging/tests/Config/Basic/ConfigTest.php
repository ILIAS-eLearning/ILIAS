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

namespace ILIAS\Logging\Config\Basic;

use PHPUnit\Framework\TestCase;
use ILIAS\Logging\ILIASLogLevel;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\DataProvider;

class ConfigTest extends TestCase
{
    public function testLazyReading(): void
    {
        $reader = $this->createMock(IniReaderInterface::class);
        $reader
            ->expects($this->never())
            ->method($this->anything());

        $config = new Config($reader);
    }

    #[TestWith(['0', false])]
    #[TestWith(['', false])]
    #[TestWith(['1', true])]
    public function testIsLoggingEnabled(string $input, bool $expected_result): void
    {
        $reader = $this->createMock(IniReaderInterface::class);
        $reader
            ->expects($this->once())
            ->method('isLoggingEnabled')
            ->willReturn($input);

        $config = new Config($reader);
        $actual_result = $config->isLoggingEnabled();

        $this->assertSame($expected_result, $actual_result);
    }

    public function testIsLoggingEnabledIsCached(): void
    {
        $reader = $this->createMock(IniReaderInterface::class);
        $reader
            ->expects($this->once())
            ->method('isLoggingEnabled')
            ->willReturn('1');

        $config = new Config($reader);
        $first_result = $config->isLoggingEnabled();
        $second_result = $config->isLoggingEnabled();

        $this->assertSame($first_result, $second_result, 'Result should be cached and stable.');
    }

    #[TestWith(['/path/to/log', 'file.log', '/path/to/log/file.log'], 'no trailing slashes')]
    #[TestWith(['/path/to/log/', 'file.log', '/path/to/log/file.log'], 'slash after path')]
    #[TestWith(['/path/to/log', '/file.log', '/path/to/log/file.log'], 'slash before file')]
    public function testPathToLogFile(
        string $expected_path,
        string $expected_file,
        string $expected_result
    ): void {
        $reader = $this->createMock(IniReaderInterface::class);
        $reader
            ->expects($this->once())
            ->method('logFile')
            ->willReturn($expected_file);
        $reader
            ->expects($this->once())
            ->method('logPath')
            ->willReturn($expected_path);

        $config = new Config($reader);
        $actual_result = $config->pathToLogFile();

        $this->assertSame($expected_result, $actual_result);
    }

    public function testPathToLogFileIsCached(): void
    {
        $reader = $this->createMock(IniReaderInterface::class);
        $reader
            ->expects($this->once())
            ->method('logFile')
            ->willReturn('file.log');
        $reader
            ->expects($this->once())
            ->method('logPath')
            ->willReturn('/path/to/log');

        $config = new Config($reader);
        $first_result = $config->pathToLogFile();
        $second_result = $config->pathToLogFile();

        $this->assertSame($first_result, $second_result, 'Result should be cached and stable.');
    }

    public function testPathToLogDirectory(): void
    {
        $expected_path = '/path/to/log/';

        $reader = $this->createMock(IniReaderInterface::class);
        $reader
            ->expects($this->once())
            ->method('logPath')
            ->willReturn($expected_path);

        $config = new Config($reader);
        $actual_path = $config->pathToLogDirectory();

        $this->assertSame($expected_path, $actual_path);
    }

    public function testPathToLogDirectoryIsCached(): void
    {
        $reader = $this->createMock(IniReaderInterface::class);
        $reader
            ->expects($this->once())
            ->method('logPath')
            ->willReturn('/path/to/log/');

        $config = new Config($reader);
        $first_result = $config->pathToLogFile();
        $second_result = $config->pathToLogFile();

        $this->assertSame($first_result, $second_result, 'Result should be cached and stable.');
    }

    #[TestWith(['250', ILIASLogLevel::NOTICE], 'from integer')]
    #[TestWith(['ALERT', ILIASLogLevel::ALERT], 'from upper case string')]
    #[TestWith(['warning', ILIASLogLevel::WARNING], 'from lower case string')]
    #[TestWith(['Emergency', ILIASLogLevel::EMERGENCY], 'from capitalized string')]
    #[TestWith(['', ILIASLogLevel::INFO], 'empty, so use fallback')]
    #[TestWith(['MUJjOI', ILIASLogLevel::INFO], 'something else, so use fallback')]
    public function testDefaultLevel(string $input, ILIASLogLevel $expected_level): void
    {
        $reader = $this->createMock(IniReaderInterface::class);
        $reader
            ->expects($this->once())
            ->method('defaultLevel')
            ->willReturn($input);

        $config = new Config($reader);
        $actual_level = $config->defaultLevel();

        $this->assertSame($expected_level, $actual_level);
    }

    public function testDefaultLevelIsCached(): void
    {
        $reader = $this->createMock(IniReaderInterface::class);
        $reader
            ->expects($this->once())
            ->method('defaultLevel')
            ->willReturn('WARNING');

        $config = new Config($reader);
        $first_result = $config->defaultLevel();
        $second_result = $config->defaultLevel();

        $this->assertSame($first_result, $second_result, 'Result should be cached and stable.');
    }
}
