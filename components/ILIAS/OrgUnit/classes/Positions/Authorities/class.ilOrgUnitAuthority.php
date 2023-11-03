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
 * Class ilOrguAuthority
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitAuthority
{
    public const FIELD_OVER = 'over';
    public const OVER_EVERYONE = -1;
    public const POSITION_ID = "position_id";
    public const SCOPE_SAME_ORGU = 1;
    public const SCOPE_SUBSEQUENT_ORGUS = 2;
    public const SCOPES = [
        self::SCOPE_SAME_ORGU,
        self::SCOPE_SUBSEQUENT_ORGUS
    ];

    protected int $id;
    protected int $over = self::OVER_EVERYONE;
    protected int $scope = self::SCOPE_SAME_ORGU;
    protected int $position_id = 0;

    public function __construct($id = 0)
    {
        $this->id = $id;
    }

    /**
     * @return int[]
     */
    public static function getScopes(): array
    {
        return self::SCOPES;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOver(): int
    {
        return $this->over;
    }

    public function withOver(int $over): self
    {
        $clone = clone $this;
        $clone->over = $over;
        return $clone;
    }

    public function getScope(): int
    {
        return $this->scope;
    }

    public function withScope(int $scope): self
    {
        if (!in_array($scope, self::SCOPES)) {
            throw new ilException('Selected Scope in ' . self::class . ' not allowed');
        }
        $clone = clone $this;
        $clone->scope = $scope;
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
}
