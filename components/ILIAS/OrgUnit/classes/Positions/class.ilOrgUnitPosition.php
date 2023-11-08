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

/**
 * Class ilOrgUnitPosition
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPosition
{
    public const CORE_POSITION_EMPLOYEE = 1;
    public const CORE_POSITION_SUPERIOR = 2;

    protected int $id;
    protected string $title = "";
    protected string $description = "";
    protected bool $core_position = false;
    protected int $core_identifier = 0;

    /**
     * @var ilOrgUnitAuthority[]
     */
    protected array $authorities = [];

    public function __construct($id = 0)
    {
        $this->id = $id;
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function withTitle(string $title): self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function withDescription(string $description): self
    {
        $clone = clone $this;
        $clone->description = $description;
        return $clone;
    }

    public function isCorePosition(): bool
    {
        return $this->core_position;
    }

    public function withCorePosition(bool $core_position): self
    {
        $clone = clone $this;
        $clone->core_position = $core_position;
        return $clone;
    }

    public function getCoreIdentifier(): int
    {
        return $this->core_identifier;
    }

    public function withCoreIdentifier(int $core_identifier): self
    {
        $clone = clone $this;
        $clone->core_identifier = $core_identifier;
        return $clone;
    }

    /**
     * @return ilOrgUnitAuthority[]
     */
    public function getAuthorities(): array
    {
        return $this->authorities;
    }

    public function getAuthoritiesAsArray(): array
    {
        $return = [];
        foreach ($this->authorities as $authority) {
            $return[] = [
                'id' => $authority->getId(),
                'over' => $authority->getOver(),
                'scope' => $authority->getScope(),
                'position_id' => $authority->getPositionId()
            ];
        }

        return $return;
    }

    /**
     * @param ilOrgUnitAuthority[] $authorities
     */
    public function withAuthorities(array $authorities): self
    {
        $clone = clone $this;
        $clone->authorities = $authorities;
        return $clone;
    }
}
