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

namespace ILIAS\TestQuestionPool\ExportImport\Envelopes;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Envelope;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;

/**
 * Wrapper object for an image file within a question (e.g. an image map question).
 * An image belongs either to the question text, answer options or the suggested solution.
 */
class QuestionImage implements Envelope
{
    public const int TYPE_ANSWER = 1;
    public const int TYPE_SOLUTION = 2;

    public function __construct(
        private string $filename,
        private ?int $question_id = null,
        private int $type = self::TYPE_ANSWER,
        private string $id = ''
    ) {
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getQuestionId(): ?int
    {
        return $this->question_id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function toArray(Transformations $tt): array
    {
        return [
            'filename' => $this->filename,
            'question_id' => $this->question_id,
            'type' => $this->type,
            'id' => $this->id,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $value, Transformations $tt): static
    {
        return new self(
            $value['filename'],
            $tt->int($value['question_id']),
            $tt->int($value['type']),
            $value['id']
        );
    }
}
