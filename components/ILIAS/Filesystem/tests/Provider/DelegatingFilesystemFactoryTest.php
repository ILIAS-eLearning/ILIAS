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

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\Configuration\DirectoryPathConfig;
use ILIAS\Filesystem\Decorator\ReadOnlyDecorator;
use ILIAS\Filesystem\FileSystems\FilesystemCustomizing;
use ILIAS\Filesystem\FileSystems\FilesystemLibs;
use ILIAS\Filesystem\FileSystems\FilesystemNodeModules;
use ILIAS\Filesystem\FileSystems\FilesystemWeb;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DelegatingFilesystemFactoryTest extends TestCase
{
    private DelegatingFilesystemFactory $factory;

    protected function setUp(): void
    {
        $directories = $this->createStub(DirectoryPathConfig::class);
        foreach (
            [
                'getWebDirectoryPath',
                'getStorageDirectoryPath',
                'getCustomizingDirectoryPath',
                'getNodeModulesDirectoryPath',
                'getLibsDirectoryPath',
                'getTempDirectoryPath',
            ] as $method
        ) {
            $directories->method($method)->willReturn(sys_get_temp_dir());
        }

        $this->factory = new DelegatingFilesystemFactory(
            $this->createStub(FilenameSanitizer::class),
            $directories
        );
    }

    public static function codeDirectoryProvider(): \Iterator
    {
        yield 'libs' => [FilesystemLibs::class];
        yield 'node_modules' => [FilesystemNodeModules::class];
    }

    #[DataProvider('codeDirectoryProvider')]
    public function testCodeDirectoriesAreReadOnly(string $interface): void
    {
        $this->assertInstanceOf(ReadOnlyDecorator::class, $this->factory->buildFor($interface));
    }

    public function testDataDirectoriesAreWritable(): void
    {
        $this->assertNotInstanceOf(ReadOnlyDecorator::class, $this->factory->buildFor(FilesystemWeb::class));
    }

    public function testCustomizingDirectoryCanBeBuilt(): void
    {
        $this->assertNotInstanceOf(
            ReadOnlyDecorator::class,
            $this->factory->buildFor(FilesystemCustomizing::class)
        );
    }
}
