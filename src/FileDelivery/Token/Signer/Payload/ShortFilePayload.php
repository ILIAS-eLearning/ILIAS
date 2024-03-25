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

use ILIAS\FileDelivery\Setup\BaseDirObjective;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ShortFilePayload extends StructuredPayload
{
    public function __construct(
        private string $uri,
        private string $file_name
    ) {
        // try to shorten uri
        $base = BaseDirObjective::get();
        if ($base !== null) {
            $uri = str_replace($base, '', $uri);
        }

        parent::__construct([
            'p' => $uri,
            'n' => $file_name
        ]);
    }

    public static function fromArray(array $raw_payload): self
    {
        return new self(
            $raw_payload['p'],
            $raw_payload['n']
        );
    }

    public function getUri(): string
    {
        $uri = $this->uri;
        // try to expand uri
        $base = BaseDirObjective::get();
        if ($base !== null) {
            $uri = $base . $uri;
        }

        return $uri;
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
