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

namespace ILIAS\Questions\AnswerForm;

use ILIAS\Data\UUID\Uuid;

class TypeGenericProperties
{
    public function __construct(
        private readonly Uuid $answer_form_id,
        private readonly Uuid $question_id,
        private ?float $available_points = null,
        private ?int $image_size = null,
        private ?bool $shuffle_answer_options = null,
        private string $additional_text = '',
        private string $additional_text_legacy = ''
    ) {
    }

    public function getAnswerFormId(): Uuid
    {
        return $this->answer_form_id;
    }

    public function getQuestionId(): Uuid
    {
        return $this->question_id;
    }

    public function getAvailablePoints(): ?float
    {
        return $this->available_points;
    }

    public function getImageSize(): ?int
    {
        return $this->image_size;
    }

    public function getShuffleAnswerOptions(): ?bool
    {
        return $this->shuffle_answer_options;
    }

    public function getAdditionalText(): string
    {
        return $this->additional_text;
    }

    public function getAdditionalTextLegacy(): string
    {
        return $this->additional_text_legacy;
    }
}
