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

namespace ILIAS\Filesystem\Configuration;

use ILIAS\Database\Connection;
use ILIAS\FileServices\Policy\UploadRestrictionBypass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DatabaseBackedFilesystemConfigTest extends TestCase
{
    public static function asciiProvider(): \Iterator
    {
        yield 'switched on' => ['1', true];
        yield 'switched off' => ['0', false];
    }

    #[DataProvider('asciiProvider')]
    public function testASCIIConvertionFollowsTheStoredSetting(string $stored, bool $expected): void
    {
        $config = $this->configWith([
            DatabaseBackedFilesystemConfig::MODULE_NAME => [
                DatabaseBackedFilesystemConfig::F_DOWNLOAD_ASCII_FILENAME => $stored,
            ],
        ]);

        $this->assertSame($expected, $config->isASCIIConvertionEnabled());
    }

    public function testASCIIConvertionIsEnabledByDefault(): void
    {
        $db = $this->createStub(Connection::class);
        $db->method('fetchAssoc')->willReturn(null);

        $config = new DatabaseBackedFilesystemConfig($db, $this->createStub(UploadRestrictionBypass::class));

        $this->assertTrue($config->isASCIIConvertionEnabled());
    }

    private function configWith(array $resolved_values): DatabaseBackedFilesystemConfig
    {
        $config = new DatabaseBackedFilesystemConfig(
            $this->createStub(Connection::class),
            $this->createStub(UploadRestrictionBypass::class)
        );

        // the settings table is not what this test is about, so the values it would
        // resolve to are preset instead
        $reflection = new \ReflectionClass(DatabaseBackedFilesystemConfig::class);
        $reflection->getProperty('resolved_values')->setValue($config, $resolved_values);

        return $config;
    }
}
