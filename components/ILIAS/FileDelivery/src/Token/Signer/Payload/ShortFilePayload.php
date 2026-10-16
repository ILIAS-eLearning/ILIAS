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
    protected string $mime_type = '';
    protected string $disposition = '';
    protected int $user_id = 0;

    public function __construct(
        private string $uri,
        private string $file_name,
        private bool $isolated = true
    ) {
        $modification_time = @filemtime($uri);
        // try to shorten uri
        $base = BaseDirObjective::get();
        if ($base !== null && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $data = [
            'p' => $uri,
            'n' => $file_name,
            'mt' => $modification_time,
        ];
        // only tokens leaving the content domain carry the flag, so all others stay as short as before
        if (!$isolated) {
            $data['ni'] = 1;
        }

        parent::__construct($data);
    }

    public static function fromArray(array $raw_payload): self
    {
        return new self(
            $raw_payload['p'],
            $raw_payload['n'],
            empty($raw_payload['ni'])
        );
    }

    public function getUri(): string
    {
        $uri = $this->uri;
        // try to expand uri
        $base = BaseDirObjective::get();
        // only shortened (relative) uris are expanded, absolute ones lie outside the base dir
        if ($base !== null && !str_starts_with($uri, '/')) {
            return $base . $uri;
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

    /**
     * Whether the file is delivered from the content domain when User Content
     * Isolation is active (the default), or from the ILIAS domain.
     */
    public function isIsolated(): bool
    {
        return $this->isolated;
    }
}
