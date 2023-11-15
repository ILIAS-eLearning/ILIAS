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

namespace ILIAS\Exercise\Assignment;

/**
 * Assignment
 */
class Assignment
{
    protected int $order_nr;
    protected int $type;
    protected string $instructions;
    protected bool $mandatory;
    protected int $deadline_mode;
    protected int $deadline;
    protected int $deadline2;
    protected int $relative_deadline;
    protected int $rel_deadline_last_submission;
    protected int $id;
    protected int $exc_id;
    protected string $title;

    public function __construct(
        int $id,
        int $exc_id,
        string $title,
        int $order_nr,
        int $type,
        string $instructions,
        bool $mandatory,
        int $deadline_mode,
        int $deadline,
        int $deadline2,
        int $relative_deadline,
        int $rel_deadline_last_submission
    ) {
        $this->id = $id;
        $this->exc_id = $exc_id;
        $this->title = $title;
        $this->order_nr = $order_nr;
        $this->type = $type;
        $this->instructions = $instructions;
        $this->mandatory = $mandatory;
        $this->deadline_mode = $deadline_mode;
        $this->deadline = $deadline;
        $this->deadline2 = $deadline2;
        $this->relative_deadline = $relative_deadline;
        $this->rel_deadline_last_submission = $rel_deadline_last_submission;
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getExcId(): int
    {
        return $this->exc_id;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getOrderNr(): int
    {
        return $this->order_nr;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getInstructions(): string
    {
        return $this->instructions;
    }
    public function getMandatory(): bool
    {
        return $this->mandatory;
    }
    public function getDeadlineMode(): int
    {
        return $this->deadline_mode;
    }
    public function getDeadline(): int
    {
        return $this->deadline;
    }
    public function getDeadline2(): int
    {
        return $this->deadline2;
    }
    public function getRelativeDeadline(): int
    {
        return $this->relative_deadline;
    }
    public function getRelativeDeadlineLastSubmission(): int
    {
        return $this->rel_deadline_last_submission;
    }

}
