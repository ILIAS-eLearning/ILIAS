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

use ILIAS\Questions\AnswerForm\Views\Edit;
use ILIAS\Questions\AnswerForm\Views\Participant;
use ILIAS\Language\Language;

interface Type
{
    public function withData(
        string $id,
        ?float $available_points,
        ?int $image_size,
        ?bool $shuffle_answer_options,
        string $additional_text,
        string $additional_text_legacy,
        ?array $data
    ): static;

    public function getLabel(Language $lng): string;

    public function getPersistence(): Persistence;
    public function isMarkable(): bool;
    public function getMarking(): ?Marking;
    public function getEditView(): Edit;
    public function getParticipantView(): Participant;
}
