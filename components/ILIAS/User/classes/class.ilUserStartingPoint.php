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

class ilUserStartingPoint
{
    private const ROLE_BASED = 2;

    public function __construct(
        private ?int $id,
        private ?int $starting_point_type = null,
        private int $starting_object = 0,
        private ?int $starting_position = null,
        private ?int $rule_type = null,
        private ?string $rule_options = null, // array serialized in db
        private int $calendar_view = 0,
        private int $calendar_period = 0
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setStartingPointType(int $starting_point_type): void
    {
        $this->starting_point_type = $starting_point_type;
    }

    public function getStartingPointType(): int
    {
        return $this->starting_point_type;
    }

    public function setStartingObject(int $a_starting_object): void
    {
        $this->starting_object = $a_starting_object;
    }

    public function getStartingObject(): int
    {
        return $this->starting_object;
    }

    public function setPosition(int $a_starting_position): void
    {
        $this->starting_position = $a_starting_position;
    }

    public function getPosition(): int
    {
        return $this->starting_position;
    }

    public function getRuleType(): ?int
    {
        return $this->rule_type;
    }

    public function isRoleBasedStartingPoint(): bool
    {
        return $this->rule_type === self::ROLE_BASED;
    }

    public function setRuleTypeRoleBased(): void
    {
        $this->rule_type = self::ROLE_BASED;
    }

    /**
     * Gets calendar view
     */
    public function getCalendarView(): int
    {
        return $this->calendar_view;
    }

    /**
     * Sets calendar view
     */
    public function setCalendarView(int $calendar_view): void
    {
        $this->calendar_view = $calendar_view;
    }

    public function getCalendarPeriod(): int
    {
        return $this->calendar_period;
    }

    public function setCalendarPeriod(int $calendar_period): void
    {
        $this->calendar_period = $calendar_period;
    }

    public function getRuleOptions(): ?string
    {
        return $this->rule_options;
    }

    public function setRuleOptions(string $a_rule_options): void
    {
        $this->rule_options = $a_rule_options;
    }
}
