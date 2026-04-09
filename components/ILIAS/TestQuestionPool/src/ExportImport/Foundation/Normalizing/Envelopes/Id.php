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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Envelope;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;

/**
 * Wrapper object for an id (int, string, uuid, ...) and an optional object type the id belongs to. The wrapper can be
 * used to detect ids within the normalization and denormalization pipes.
 *
 * @template T
 */
class Id implements Envelope
{
    /**
     * @param T $id
     */
    public function __construct(
        private mixed $id = null,
        private string $object = ''
    ) {
    }

    /**
     * @return T
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    /**
     * @inheritDoc
     */
    public function toArray(Transformations $tt): array
    {
        if (is_object($this->id)) {
            return [
                'id' => $tt->normalize($this->id),
                'type' => get_class($this->id),
                'object' => $this->object,
            ];
        } else {
            return [
                'id' => (string) $this->id,
                'type' => gettype($this->id),
                'object' => $this->object,
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $value, Transformations $tt): static
    {
        $raw_id = $value['id'];
        $type = $value['type'];

        if (class_exists($type)) {
            return new Id($tt->denormalize($raw_id, $type), $value['object']);
        } else {
            $id = match($type) {
                'integer' => (int) $raw_id,
                'string' => (string) $raw_id,
                'float' => (float) $raw_id,
                'bool' => (bool) $raw_id,
                'null' => null,
                default => throw new NormalizingException("Invalid type for id: {$type}")
            };
            return new Id($id, $value['object']);
        }
    }
}
