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

namespace ILIAS\TestQuestionPool;

use ILIAS\HTTP\Services;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Request-scoped mutable view of uploaded files in the legacy PHP files-array shape.
 */
class UploadSubmit
{
    /** @var array<string, array<string, mixed>> */
    private array $fields = [];
    private bool $initialized = false;

    public function __construct(
        private readonly Services $http
    ) {
    }

    public function hasAny(): bool
    {
        $this->ensureInitialized();

        return $this->fields !== [];
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $post_var): array
    {
        $this->ensureInitialized();

        return $this->fields[$post_var] ?? [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function set(string $post_var, array $data): void
    {
        $this->ensureInitialized();
        $this->fields[$post_var] = $data;
    }

    private function ensureInitialized(): void
    {
        if ($this->initialized) {
            return;
        }

        foreach ($this->http->request()->getUploadedFiles() as $key => $value) {
            $this->fields[(string) $key] = $this->convertToLegacy($value);
        }

        $this->initialized = true;
    }

    /**
     * @return array<string, mixed>
     */
    private function convertToLegacy(UploadedFileInterface|array $node): array
    {
        if ($node instanceof UploadedFileInterface) {
            return $this->normalizeUploadedFile($node);
        }

        $legacy = [
            'name' => [],
            'type' => [],
            'tmp_name' => [],
            'error' => [],
            'size' => [],
        ];

        foreach ($node as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized = $this->normalizeUploadedFile($value);
                foreach ($legacy as $property => $_) {
                    $legacy[$property][$key] = $normalized[$property];
                }
                continue;
            }

            if (!is_array($value)) {
                continue;
            }

            $nested = $this->convertToLegacy($value);
            if (
                isset($nested['name'], $nested['type'], $nested['tmp_name'], $nested['error'], $nested['size'])
                && !isset($nested['name']['name'])
            ) {
                foreach ($legacy as $property => $_) {
                    $legacy[$property][$key] = $nested[$property];
                }
                continue;
            }

            foreach ($legacy as $property => $_) {
                $legacy[$property][$key] = $nested[$property] ?? [];
            }
        }

        return $legacy;
    }

    /**
     * @return array{name: string, type: string, tmp_name: string, error: int, size: int}
     */
    private function normalizeUploadedFile(UploadedFileInterface $file): array
    {
        $tmp = $file->getStream()->getMetadata('uri');

        return [
            'name' => $file->getClientFilename() ?? '',
            'type' => $file->getClientMediaType() ?? '',
            'tmp_name' => is_string($tmp) ? $tmp : '',
            'error' => $file->getError(),
            'size' => $file->getSize() ?? 0,
        ];
    }
}
