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
 ********************************************************************
 */
declare(strict_types=1);

use ILIAS\DI\Container;

/**
 * Class ilOrgUnitUserAssignment
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitUserAssignment
{
    protected int $id;
    protected int $user_id = 0;
    protected int $position_id = 0;
    protected int $orgu_id = 0;

    public function __construct(?int $id = 0)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function withId(?int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function withUserId(int $user_id): self
    {
        $clone = clone $this;
        $clone->user_id = $user_id;
        return $clone;
    }

    public function getPositionId(): int
    {
        return $this->position_id;
    }

    public function withPositionId(int $position_id): self
    {
        $clone = clone $this;
        $clone->position_id = $position_id;
        return $clone;
    }

    public function getOrguId(): int
    {
        return $this->orgu_id;
    }

    public function withOrguId(int $orgu_id): self
    {
        $clone = clone $this;
        $clone->orgu_id = $orgu_id;
        return $clone;
    }
}
