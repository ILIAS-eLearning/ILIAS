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

namespace ILIAS\Test\ExportImport\Envelopes;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Envelope;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\ResourceNormalizer;

class Solution implements Envelope
{
    public function __construct(
        public readonly Id $active_id,
        public readonly Id $question_id,
        public readonly int $attempt,
        public readonly ?float $points,
        public readonly int $timestamp,
        public readonly ResourceIdentification|string|null $value1,
        public readonly ?string $value2,
        public readonly ?int $step,
        public readonly bool $authorized
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toArray(Transformations $tt): array
    {
        return [
            'active_id' => $tt->normalize($this->active_id),
            'question_id' => $tt->normalize($this->question_id),
            'attempt' => $this->attempt,
            'points' => $this->points,
            'timestamp' => $this->timestamp,
            'value1' => $tt->normalize($this->value1),
            'value2' => $this->value2,
            'step' => $this->step,
            'authorized' => $this->authorized
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $value, Transformations $tt): static
    {
        return new self(
            $tt->denormalize($value['active_id'], Id::class)->getId(),
            $tt->denormalize($value['question_id'], Id::class)->getId(),
            $tt->int($value['attempt']),
            $tt->nullableFloat($value['points']),
            $tt->int($value['timestamp']),
            ResourceNormalizer::isResourceIdentification($value['value1'])
                ? $tt->denormalize($value['value1'], ResourceIdentification::class)
                : $tt->nullableString($value['value1']),
            $tt->nullableString($value['value2']),
            $tt->nullableInt($value['step']),
            $tt->bool($value['authorized']),
        );
    }

    public static function fromRow(array $row): static
    {
        $value1 = $row['value1'];
        if ($row['value2'] === 'rid' && is_string($value1)) {
            $value1 = new ResourceIdentification($value1);
        }

        return new self(
            new Id($row['active_fi'], 'participant'),
            new Id($row['question_fi'], 'question'),
            (int) $row['pass'],
            $row['points'] ? (float) $row['points'] : null,
            (int) $row['tstamp'],
            $value1,
            $row['value2'],
            $row['step'] ? (int) $row['step'] : null,
            (bool) $row['authorized']
        );
    }
}
