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

namespace ILIAS\KeyValueStorage\Exception;

/**
 * Raised when a stored value cannot be decoded as JSON or has an invalid structure.
 */
final class InvalidStoragePayloadException extends \InvalidArgumentException
{
    public static function fromJsonException(\JsonException $exception): self
    {
        return new self('Stored value is not valid JSON.', 0, $exception);
    }

    public static function fromInvalidStructure(string $message): self
    {
        return new self($message);
    }
}
