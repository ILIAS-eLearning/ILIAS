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

namespace ILIAS\FileDelivery\Token\Serializer;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class PHPSerializer implements Serializer
{
    public function __construct()
    {
    }

    public function serializePayload(array $payload_data): string
    {
        return serialize($payload_data);
    }

    public function unserializePayload(string $payload_string): array
    {
        return unserialize($payload_string, ['allowed_classes' => false]);
    }

    public function serializeValidity(?int $valid_until_timestamp): string
    {
        return (string) ($valid_until_timestamp ?? '');
    }

    public function unserializeValidity(string $valid_until_string): ?int
    {
        return $valid_until_string === '' ? null : (int) $valid_until_string;
    }
}
