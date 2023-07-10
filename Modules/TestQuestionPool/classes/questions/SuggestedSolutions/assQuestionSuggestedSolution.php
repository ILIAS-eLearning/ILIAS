<?php

declare(strict_types=1);

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
 */

namespace ILIAS\TA\Questions;

/**
 * a suggested solution
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
abstract class assQuestionSuggestedSolution
{
    public const TYPE_LM = 'lm';
    public const TYPE_LM_CHAPTER = 'st';
    public const TYPE_LM_PAGE = 'pg';
    public const TYPE_GLOSARY_TERM = 'git';
    public const TYPE_FILE = 'file';
    public const TYPE_TEXT = 'text';

    public const TYPES = [
        self::TYPE_LM => 'obj_lm',
        self::TYPE_LM_CHAPTER => 'obj_st',
        self::TYPE_LM_PAGE => 'obj_pg',
        self::TYPE_GLOSARY_TERM => 'glossary_term',
        self::TYPE_FILE => 'fileDownload',
        self::TYPE_TEXT => 'solutionText'
    ];

    protected int $id;
    protected int $question_id;
    protected int $subquestion_index;
    protected string $import_id;
    protected \DateTimeImmutable $last_update;

    public function __construct(
        int $id,
        int $question_id,
        int $subquestion_index,
        string $import_id,
        \DateTimeImmutable $last_update
    ) {
        $this->id = $id;
        $this->question_id = $question_id;
        $this->subquestion_index = $subquestion_index;
        $this->import_id = $import_id;
        $this->last_update = $last_update;
    }

    abstract public function getType(): string;
    abstract public function getStorableValue(): string;

    public function getId(): int
    {
        return $this->id;
    }
    public function withId(int $id): static
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }
    public function withQuestionId(int $question_id): static
    {
        $clone = clone $this;
        $clone->question_id = $question_id;
        return $clone;
    }

    public function getImportId(): string
    {
        return $this->import_id;
    }
    public function withImportId(string $import_id): static
    {
        $clone = clone $this;
        $clone->import_id = $import_id;
        return $clone;
    }

    public function getSubquestionIndex(): int
    {
        return $this->subquestion_index;
    }
    public function withSubquestionIndex(int $subquestion_index): static
    {
        $clone = clone $this;
        $clone->subquestion_index = $subquestion_index;
        return $clone;
    }

    public function getLastUpdate(): \DateTimeImmutable
    {
        return $this->last_update;
    }

    public function isOfTypeFile(): bool
    {
        return $this->getType() === self::TYPE_FILE;
    }

    public function isOfTypeText(): bool
    {
        return $this->getType() === self::TYPE_TEXT;
    }

    public function isOfTypeLink(): bool
    {
        return in_array(
            $this->getType(),
            [
                self::TYPE_LM,
                self::TYPE_LM_CHAPTER,
                self::TYPE_LM_PAGE,
                self::TYPE_GLOSARY_TERM,
            ]
        );
    }
}
