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

namespace ILIAS\Tests\KeyValueStorage;

use ILIAS\KeyValueStorage\Exception\StorageNotAvailableException;
use ILIAS\KeyValueStorage\Implementation\StorageBackend;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StorageNotAvailableExceptionTest extends TestCase
{
    #[DataProvider('backendMessageProvider')]
    public function testContainsBackendName(StorageBackend $backend, string $expected_message): void
    {
        $exception = new StorageNotAvailableException($backend);

        self::assertSame($expected_message, $exception->getMessage());
    }

    /**
     * @return array<string, array{0: StorageBackend, 1: string}>
     */
    public static function backendMessageProvider(): array
    {
        return [
            'session' => [
                StorageBackend::SESSION,
                'No storage provider is registered for backend "session".',
            ],
            'persistent' => [
                StorageBackend::PERSISTENT,
                'No storage provider is registered for backend "persistent".',
            ],
        ];
    }
}
