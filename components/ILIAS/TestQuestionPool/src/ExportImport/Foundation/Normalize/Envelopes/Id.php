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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Envelopes;

use ILIAS\Data\UUID\Uuid;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Envelope;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\NormalizingException;

/**
 * Wrapper object for an object identifier and an optional object type the identifier belongs to.
 * The wrapper can be used to detect ids within the normalization and denormalization pipes.
 */
class Id implements Envelope
{
    public function __construct(
        private int|Uuid|null $id = null,
        private string $object = ''
    ) {
    }

    public function getId(): int|Uuid|null
    {
        return $this->id;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    #[\Override]
    public function unpack(Transformations $tt): array
    {
        return [
            'id' => $tt->normalize($this->id),
            'type' => $this->id instanceof Uuid ? Uuid::class : gettype($this->id),
            'object' => $this->object,
        ];

    }

    #[\Override]
    public static function pack(array $value, Transformations $tt): static
    {
        $raw_id = $value['id'];
        $type = $value['type'];

        $id = match ($type) {
            Uuid::class => $tt->denormalize($raw_id, Uuid::class),
            'integer' => (int) $raw_id,
            'null' => null,
            default => throw new NormalizingException("Invalid type for id: {$type}")
        };

        return new Id($id, $value['object']);
    }
}
