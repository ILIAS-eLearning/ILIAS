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

namespace ILIAS\FileDelivery\Token\Signer\Payload;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class FilePayload extends StructuredPayload
{
    public function __construct(
        private string $uri,
        private string $mime_type,
        private string $file_name,
        private string $disposition,
        private int $user_id,
        ?int $valid_until = null
    ) {
        parent::__construct([
            'p' => $uri,
            'm' => $mime_type,
            'n' => $file_name,
            'd' => $disposition,
            'u' => $user_id,
        ], $valid_until);
    }

    public static function fromArray(array $raw_payload): self
    {
        return new self(
            $raw_payload['p'],
            $raw_payload['m'],
            $raw_payload['n'],
            $raw_payload['d'],
            $raw_payload['u'],
            $raw_payload['v'] ?? null
        );
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMimeType(): string
    {
        return $this->mime_type;
    }

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function getDisposition(): string
    {
        return $this->disposition;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }
}
