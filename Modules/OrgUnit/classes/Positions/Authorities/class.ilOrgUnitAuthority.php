<?php

// namespace ILIAS\Modules\OrgUnit\Positions\Authorities;

/**
 * Class ilOrguAuthority
 *
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
    protected static $scopes = array(
        self::SCOPE_SAME_ORGU,
        self::SCOPE_SUBSEQUENT_ORGUS,
        //		self::SCOPE_ALL_ORGUS,
    );


    /**
     * @return array
     */
    public static function getScopes()
    {
        return self::$scopes;
    }


    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return "il_orgu_authority";
    }


    /**
     * @var int
     *
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
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $over = self::OVER_EVERYONE;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $scope = self::SCOPE_SAME_ORGU;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $position_id = 0;
    /**
     * @var \Closure
     */
    protected static $name_render;


    /**
     * ilOrgUnitAuthority constructor.
     *
     * @param int               $primary_key
     * @param \arConnector|null $connector
     */
    public function __construct($primary_key = 0, \arConnector $connector = null)
    {
        parent::__construct($primary_key, $connector);
        if (!self::$name_render) {
            self::$name_render = function ($id) {
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


    /**
     * @return string
     */
    public function __toString()
    {
        $renderer = self::$name_render;

        return $renderer($this->getId());
    }


    /**
     * @return array
     */
    public function __toArray()
    {
        return array(
            'id' => $this->getId(),
            'over' => $this->getOver(),
            'scope' => $this->getScope(),
            'position_id' => $this->getPositionId(),
        );
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return int
     */
    public function getOver()
    {
        return $this->over;
    }


    /**
     * This is either an ID of a position or ilOrgUnitAuthority::OVER_EVERYONE
     *
     * @param int $over
     */
    public function setOver($over)
    {
        $this->over = $over;
    }


    /**
     * @return int
     */
    public function getScope()
    {
        return $this->scope;
    }


    /**
     * This is either ilOrgUnitAuthority::SCOPE_SAME_ORGU, ilOrgUnitAuthority::SCOPE_ALL_ORGUS or
     * ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS
     *
     * @param int $scope
     *
     * @throws \ilException
     */
    public function setScope($scope)
    {
        if (!in_array($scope, self::$scopes)) {
            throw new ilException('Selected Scop in ' . self::class . ' not allowed');
        }
        $this->scope = $scope;
    }


    /**
     * @return int
     */
    public function getPositionId()
    {
        return $this->position_id;
    }


    /**
     * @param int $position_id
     */
    public function setPositionId($position_id)
    {
        $this->position_id = $position_id;
    }
}
