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

use ILIAS\KeyValueStorage\Exception\InvalidStoragePayloadException;
use PHPUnit\Framework\TestCase;

class InvalidStoragePayloadExceptionTest extends TestCase
{
    public function testFromJsonExceptionUsesFixedMessageAndPrevious(): void
    {
        $json_exception = new \JsonException('Syntax error');

        $exception = InvalidStoragePayloadException::fromJsonException($json_exception);

        self::assertSame('Stored value is not valid JSON.', $exception->getMessage());
        self::assertSame($json_exception, $exception->getPrevious());
    }

    public function testFromInvalidStructureUsesProvidedMessage(): void
    {
        $exception = InvalidStoragePayloadException::fromInvalidStructure('Decoded value contains unsupported type object.');

        self::assertSame('Decoded value contains unsupported type object.', $exception->getMessage());
        self::assertNull($exception->getPrevious());
    }
}
