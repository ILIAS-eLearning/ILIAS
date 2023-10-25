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
 */

declare(strict_types=1);

namespace ILIAS\Tests\UI\Component\Input;

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class UploadLimitResolverTest extends TestCase
{
    protected UploadHandler $upload_handler_with_chunks;
    protected UploadHandler $upload_handler_without_chunks;

    protected function setUp(): void
    {
        $pseudo_upload_handler = $this->createMock(UploadHandler::class);
        $pseudo_upload_handler->method('getFileIdentifierParameterName')->willReturn('');

        $this->upload_handler_with_chunks = clone $pseudo_upload_handler;
        $this->upload_handler_with_chunks->method('supportsChunkedUploads')->willReturn(true);

        $this->upload_handler_without_chunks = clone $pseudo_upload_handler;
        $this->upload_handler_without_chunks->method('supportsChunkedUploads')->willReturn(false);
    }

    public static function provideUploadLimitResolutionDataSet(): array
    {
        return [
            [
                'ini_value' => 10,
                'custom_global_value' => null,
                'local_value' => null,
                'upload_handler_supports_chunks' => true,
                'result' => 10
            ],
            [
                'ini_value' => 10,
                'custom_global_value' => null,
                'local_value' => 20,
                'upload_handler_supports_chunks' => true,
                'result' => 20
            ],
            [
                'ini_value' => 10,
                'custom_global_value' => null,
                'local_value' => 20,
                'upload_handler_supports_chunks' => true,
                'result' => 20
            ],
            [
                'ini_value' => 10,
                'custom_global_value' => 15,
                'local_value' => 20,
                'upload_handler_supports_chunks' => true,
                'result' => 20
            ],
            [
                'ini_value' => 10,
                'custom_global_value' => 20,
                'local_value' => 15,
                'upload_handler_supports_chunks' => true,
                'result' => 15
            ],
            [
                'ini_value' => 10,
                'custom_global_value' => null,
                'local_value' => 20,
                'upload_handler_supports_chunks' => false,
                'result' => 10
            ],
            [
                'ini_value' => 10,
                'custom_global_value' => 20,
                'local_value' => null,
                'upload_handler_supports_chunks' => false,
                'result' => 10
            ],
            [
                'ini_value' => 10,
                'custom_global_value' => 20,
                'local_value' => 5,
                'upload_handler_supports_chunks' => false,
                'result' => 5
            ],
        ];
    }

    /**
     * @dataProvider provideUploadLimitResolutionDataSet
     */
    public function testUploadLimitResolution(
        int $php_ini_value,
        ?int $custom_global_value,
        ?int $local_value,
        bool $upload_handler_supports_chunks,
        int $expected_result,
    ): void {
        $resolver = new UploadLimitResolver($php_ini_value, $custom_global_value);

        if ($upload_handler_supports_chunks) {
            $upload_handler = $this->upload_handler_with_chunks;
        } else {
            $upload_handler = $this->upload_handler_without_chunks;
        }

        $actual_value = $resolver->getBestPossibleUploadLimitInBytes($upload_handler, $local_value);

        $this->assertEquals($expected_result, $actual_value);
    }
}
