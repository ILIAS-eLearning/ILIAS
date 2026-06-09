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

use ILIAS\Init\ErrorHandling\Infrastructure\Logging\LoggingErrorFileStorageAdapter;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\TestCase;
use Whoops\Exception\Inspector;

class LoggingErrorFileStorageAdapterTest extends TestCase
{
    public function testWritesExceptionDetailsToVirtualLogFile(): void
    {
        $this->skipIfVfsStreamNotAvailable();

        vfsStream::setup();
        vfsStream::create([
            'errors' => [],
        ]);

        $log_directory = vfsStream::url('root/errors');
        $log_file = vfsStream::url('root/errors/abcde_42.log');
        $inspector = new Inspector(new RuntimeException('adapter test'));

        $adapter = new LoggingErrorFileStorageAdapter();
        $adapter->write(
            $inspector,
            $log_directory,
            'abcde_42',
            ['password']
        );

        self::assertFileExists($log_file);
        self::assertStringContainsString('adapter test', (string) file_get_contents($log_file));
    }

    private function skipIfVfsStreamNotAvailable(): void
    {
        if (!class_exists(vfsStreamWrapper::class)) {
            self::markTestSkipped(
                'vfsStream (https://github.com/bovigo/vfsStream) is required for virtual filesystem tests.'
            );
        }
    }
}
