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

namespace ILIAS\Questions\AnswerFormTypes\Cloze;

use ILIAS\Questions\AnswerForm\Type as TypeInterface;
use ILIAS\Questions\AnswerFormTypes\Cloze\Views\Edit;
use ILIAS\Questions\AnswerFormTypes\Cloze\Views\Participant;
use ILIAS\Language\Language;

class Type implements TypeInterface
{
    private string $id;
    private float $available_points = 0.0;
    private string $cloze_text = '';
    private string $cloze_text_legacy = '';
    private bool $case_sensitive = false;
    private bool $identical_responses_valid = true;
    private ?int $max_chars = null;
    private int $min_autocomplete = 3;

    /**
     *
     * @var array<string, \ILIAS\Questions\AnswerFormTypes\Cloze\Gap>
     */
    private array $gaps = [];

    public function __construct(
        private readonly Persistence $persistence,
        private readonly Marking $marking,
        private readonly Edit $edit_view,
        private readonly Participant $participant_view
    ) {
    }

    public function withData(
        string $id,
        ?float $available_points,
        ?int $image_size,
        ?bool $shuffle_answer_options,
        string $additional_text,
        string $additional_text_legacy,
        ?array $data
    ): static {
        $this->id = $id;
        if ($available_points !== null) {
            $this->available_points = $available_points;
        }

        $this->cloze_text = $additional_text;
        $this->cloze_text_legacy = $additional_text_legacy;
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt('assClozeTest');
    }

    public function getPersistence(): Persistence
    {
        return $this->persistence;
    }

    public function isMarkable(): bool
    {
        return true;
    }

    public function getMarking(): ?Marking
    {
        return $this->marking->withAnswerForm($this);
    }

    public function getEditView(): Edit
    {
        return $this->edit_view->withAnswerForm($this);
    }

    public function getParticipantView(): Participant
    {
        return $this->participant_view->withAnswerForm($this);
    }
}
