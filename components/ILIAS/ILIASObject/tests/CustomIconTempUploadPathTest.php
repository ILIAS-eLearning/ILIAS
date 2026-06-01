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

use ILIAS\ILIASObject\Properties\AdditionalProperties\Icon\CustomIconTempUploadPath;
use org\bovigo\vfs;
use PHPUnit\Framework\TestCase;

class CustomIconTempUploadPathTest extends TestCase
{
    private function vfsStreamIsAvailable(): bool
    {
        return class_exists(vfs\vfsStreamWrapper::class);
    }

    private function skipIfVfsStreamNotAvailable(): void
    {
        if (!$this->vfsStreamIsAvailable()) {
            $this->markTestSkipped(
                'vfsStream (https://github.com/bovigo/vfsStream) is required for virtual filesystem tests.'
            );
        }
    }

    private function buildTestingObject(
        string $temp_file_name,
        string $ilias_data_dir
    ): CustomIconTempUploadPath {
        return new class (
            $temp_file_name,
            $ilias_data_dir
        ) extends CustomIconTempUploadPath {
            #[\Override]
            protected function getRealPath(
                string $path
            ): string|false {
                $normalized = str_replace('\\', '/', $path);
                if (is_dir($path)) {
                    return rtrim($normalized, '/');
                }

                if (is_file($path)) {
                    return $normalized;
                }

                return false;
            }
        };
    }

    public function testTrustedTempUploadFileIsResolved(): void
    {
        $this->skipIfVfsStreamNotAvailable();

        vfs\vfsStream::setup();
        vfs\vfsStream::create([
            'data' => [
                'temp' => [
                    'icon_upload.svg' => '<svg xmlns="http://www.w3.org/2000/svg"/>',
                ],
            ],
        ]);

        $data_dir = vfs\vfsStream::url('root/data');
        $expected_file = vfs\vfsStream::url('root/data/temp/icon_upload.svg');

        $path = $this->buildTestingObject(
            'icon_upload.svg',
            $data_dir
        );

        self::assertSame($expected_file, $path->getAbsolutePath());
    }

    public function testParentDirectorySegmentsInUserInputAreRejected(): void
    {
        $this->skipIfVfsStreamNotAvailable();

        vfs\vfsStream::setup();
        vfs\vfsStream::create([
            'data' => [
                'secret.txt' => 'sensitive',
                'temp' => [],
            ],
        ]);

        $data_dir = vfs\vfsStream::url('root/data');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Temporary upload file not found.');

        $this->buildTestingObject(
            '../secret.txt',
            $data_dir
        );
    }

    public function testAbsentTempUploadFileIsRejected(): void
    {
        $this->skipIfVfsStreamNotAvailable();

        vfs\vfsStream::setup();
        vfs\vfsStream::create([
            'data' => [
                'temp' => [],
            ],
        ]);

        $data_dir = vfs\vfsStream::url('root/data');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Temporary upload file not found.');

        $this->buildTestingObject(
            'missing.svg',
            $data_dir
        );
    }

    /**
     * @dataProvider invalidTemporaryFileNameProvider
     */
    public function testInvalidTemporaryFileNameIsRejected(
        string $malicious_input
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid temporary upload file name.');

        $this->buildTestingObject(
            $malicious_input,
            '/does/not/matter'
        );
    }

    /**
     * @return Generator<string, array{0: string}>
     */
    public static function invalidTemporaryFileNameProvider(): Generator
    {
        yield 'parent directory segment' => ['..'];
        yield 'current directory segment' => ['.'];
    }
}
