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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Envelope;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;

class RandomTestQuestion implements Envelope
{
    public function __construct(
        public readonly Id $active_id,
        public readonly Id $question_id,
        public readonly int $sequence,
        public readonly int $pass,
        public readonly int $timestamp,
        public readonly Id $src_pool_def_id
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
            'sequence' => $this->sequence,
            'pass' => $this->pass,
            'timestamp' => $this->timestamp,
            'src_pool_def_id' => $tt->normalize($this->src_pool_def_id),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $value, Transformations $tt): static
    {
        return new self(
            $tt->denormalize($value['active_id'], Id::class),
            $tt->denormalize($value['question_id'], Id::class),
            $tt->int($value['sequence']),
            $tt->int($value['pass']),
            $tt->int($value['timestamp']),
            $tt->denormalize($value['src_pool_def_id'], Id::class),
        );
    }

    public static function fromRow(array $row): static
    {
        return new self(
            new Id($row['active_fi'], 'participant'),
            new Id($row['question_fi'], 'question'),
            (int) $row['sequence'],
            (int) $row['pass'],
            (int) $row['tstamp'],
            new Id($row['src_pool_def_fi'], 'rnd_src_pool_def'),
        );
    }
}
