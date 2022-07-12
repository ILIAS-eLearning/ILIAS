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

// namespace ILIAS\Modules\OrgUnit\Positions\Authorities;

/**
 * Class ilOrguAuthority
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitAuthority extends \ActiveRecord
{
    const FIELD_OVER = 'over';
    const OVER_EVERYONE = -1;
    const POSITION_ID = "position_id";
    const SCOPE_SAME_ORGU = 1;
    const SCOPE_SUBSEQUENT_ORGUS = 2;
    const SCOPE_ALL_ORGUS = 3;
    /**
     * @var array
     */
    protected static $scopes
        = array(
            self::SCOPE_SAME_ORGU,
            self::SCOPE_SUBSEQUENT_ORGUS
        );

    /**
     * @return int[]
     */
    public static function getScopes(): array
    {
        return self::$scopes;
    }

    public static function returnDbTableName() : string
    {
        return "il_orgu_authority";
    }

    /**
     * @var int
     * @con_is_primary true
     * @con_is_unique  true
     * @con_sequence   true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $id = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected int $over = self::OVER_EVERYONE;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected int $scope = self::SCOPE_SAME_ORGU;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected int $position_id = 0;
    protected static ?\Closure $name_render = null;

    /**
     * ilOrgUnitAuthority constructor.
     * @param                $primary_key
     */
    public function __construct($primary_key = 0)
    {
        parent::__construct($primary_key);
        if (static::$name_render === null) {
            self::$name_render = function($id) {
                return $id;
            };
        }
    }

    /**
     * @param \Closure $closure
     */
    public static function replaceNameRenderer(Closure $closure)
    {
        self::$name_render = $closure;
    }

    public function __toString(): string
    {
        $renderer = self::$name_render;

        return (string) $renderer($this->getId());
    }

    public function __toArray(): array
    {
        return array(
            'id' => $this->getId(),
            'over' => $this->getOver(),
            'scope' => $this->getScope(),
            'position_id' => $this->getPositionId(),
        );
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getOver(): int
    {
        return $this->over;
    }

    /**
     * This is either an ID of a position or ilOrgUnitAuthority::OVER_EVERYONE
     */
    public function setOver(int $over)
    {
        $this->over = $over;
    }

    public function getScope(): int
    {
        return $this->scope;
    }

    /**
     * This is either ilOrgUnitAuthority::SCOPE_SAME_ORGU, ilOrgUnitAuthority::SCOPE_ALL_ORGUS or
     * ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS
     * @param int $scope
     * @throws \ilException
     */
    public function setScope(int $scope): void
    {
        if (!in_array($scope, self::$scopes)) {
            throw new ilException('Selected Scop in ' . self::class . ' not allowed');
        }
        $this->scope = $scope;
    }

    public function getPositionId(): int
    {
        return $this->position_id;
    }

    public function setPositionId(int $position_id)
    {
        $this->position_id = $position_id;
    }
}
