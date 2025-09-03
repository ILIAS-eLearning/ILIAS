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

namespace ILIAS\Questions\Question\Persistence;

class UpdateQuery
{
    private array $tables = [];
    private array $columns = [];
    private array $values = [];

    private ?float $available_points = null;
    private ?int $image_size = null;
    private ?bool $shuffle_answer_options = null;
    private string $additional_text = '';

    public function __construct()
    {
        ;
    }

    public function withAvailablePoints(float $available_points): self
    {
        $clone = clone $this;
        $clone->available_points = $available_points;
        return $clone;
    }

    public function withImageSize(int $image_size): self
    {
        $clone = clone $this;
        $clone->image_size = $image_size;
        return $clone;
    }

    public function withShuffleAnswerOptions(bool $shuffle_answer_options): self
    {
        $clone = clone $this;
        $clone->shuffle_answer_options = $shuffle_answer_options;
        return $clone;
    }
    public function withAddtionalText(string $additional_text): self
    {
        $clone = clone $this;
        $clone->additional_text = $additional_text;
        return $clone;
    }

}
