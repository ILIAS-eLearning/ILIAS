<?php

/**
 * Class ilOrgUnitPosition
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPosition extends \ActiveRecord
{
    const CORE_POSITION_EMPLOYEE = 1;
    const CORE_POSITION_SUPERIOR = 2;


    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return "il_orgu_positions";
    }


    /**
     * Override for correct on return value
     *
     * @return \ilOrgUnitPosition[]
     */
    public static function get()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::get();
    }


    /**
     * @param $core_identifier
     *
     * @return \ilOrgUnitPosition
     */
    public static function getCorePosition($core_identifier)
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ilOrgUnitPosition::where([ 'core_identifier' => $core_identifier ])->first();
    }


    /**
     * @param $core_identifier
     *
     * @return int
     */
    public static function getCorePositionId($core_identifier)
    {
        return self::getCorePosition($core_identifier)->getId();
    }

    /**
     * @throws \ilException whenever you try to delete a core-position like employee or superior
     */
    public function delete()
    {
        if ($this->isCorePosition()) {
            throw new ilException('Cannot delete Core-Position');
        }
        parent::delete();
    }


    /**
     * @return \ilOrgUnitPosition[] array of Positions (all core-positions and all positions which
     *                              have already UserAssignments)
     */
    public static function getActive()
    {
        arObjectCache::flush(self::class);
        $q = "SELECT DISTINCT il_orgu_positions.id, il_orgu_positions.*
 				FROM il_orgu_positions 
 				 LEFT JOIN il_orgu_ua ON il_orgu_positions.id = il_orgu_ua.position_id  
 				WHERE il_orgu_ua.user_id IS NOT NULL 
 					OR il_orgu_positions.core_position = 1";
        $database = $GLOBALS['DIC']->database();
        $st = $database->query($q);

        $positions = array();

        while ($data = $database->fetchAssoc($st)) {
            $position = new self();
            $position->buildFromArray($data);
            $positions[] = $position;
        }

        return $positions;
    }


    /**
     * @param int $orgu_ref_id
     *
     * @return \ilOrgUnitPosition[] array of Positions (all core-positions and all positions which
     *                              have already UserAssignments at this place)
     */
    public static function getActiveForPosition($orgu_ref_id)
    {
        arObjectCache::flush(self::class);
        $q = "SELECT DISTINCT il_orgu_positions.id, il_orgu_positions.*
 				FROM il_orgu_positions 
 				LEFT JOIN il_orgu_ua ON il_orgu_positions.id = il_orgu_ua.position_id AND il_orgu_ua.orgu_id = %s 
 				WHERE il_orgu_ua.user_id IS NOT NULL 
 					OR core_position = 1";
        $database = $GLOBALS['DIC']->database();
        $st = $database->queryF($q, array( 'integer' ), array( $orgu_ref_id ));

        $positions = array();

        while ($data = $database->fetchAssoc($st)) {
            $position = new self();
            $position->buildFromArray($data);
            $positions[] = $position;
        }

        return $positions;
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
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     512
     */
    protected $title = "";
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     4000
     */
    protected $description = "";
    /**
     * @var bool
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $core_position = false;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     */
    protected $core_identifier = 0;
    /**
     * @var \ilOrgUnitAuthority[]
     */
    protected $authorities = array();


    public function afterObjectLoad()
    {
        $this->authorities = ilOrgUnitAuthority::where(array( ilOrgUnitAuthority::POSITION_ID => $this->getId() ))
                                               ->get();
    }


    public function update()
    {
        parent::update();
        $this->storeAuthorities();
    }


    public function create()
    {
        parent::create();
        $this->storeAuthorities();
    }


    /**
     * @return array
     */
    public function getAuthoritiesAsArray()
    {
        $return = array();
        foreach ($this->authorities as $authority) {
            $return[] = $authority->__toArray();
        }

        return $return;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }


    /**
     * @return array  it's own authorities and also all which use this position
     */
    public function getDependentAuthorities()
    {
        $dependent = ilOrgUnitAuthority::where(array( ilOrgUnitAuthority::FIELD_OVER => $this->getId() ))
                                       ->get();

        $arr = $dependent + $this->authorities;

        return (array) $arr;
    }


    /**
     * This deletes the Position, it's Authorities, dependent Authorities and all User-Assignements!
     */
    public function deleteWithAllDependencies()
    {
        foreach ($this->getDependentAuthorities() as $authority) {
            $authority->delete();
        }

        $ilOrgUnitUserAssignmentQueries = ilOrgUnitUserAssignmentQueries::getInstance();
        foreach ($ilOrgUnitUserAssignmentQueries->getUserAssignmentsOfPosition($this->getId()) as $assignment) {
            $assignment->delete();
        }
        parent::delete();
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    /**
     * @return bool
     */
    public function isCorePosition()
    {
        return $this->core_position;
    }


    /**
     * @param bool $core_position
     */
    public function setCorePosition($core_position)
    {
        $this->core_position = $core_position;
    }


    /**
     * @return \ilOrgUnitAuthority[]
     */
    public function getAuthorities()
    {
        return $this->authorities;
    }


    /**
     * @param \ilOrgUnitAuthority[] $authorities
     */
    public function setAuthorities($authorities)
    {
        $this->authorities = $authorities;
    }


    /**
     * @return int
     */
    public function getCoreIdentifier()
    {
        return $this->core_identifier;
    }


    /**
     * @param int $core_identifier
     */
    public function setCoreIdentifier($core_identifier)
    {
        $this->core_identifier = $core_identifier;
    }


    private function storeAuthorities()
    {
        $ids = [];
        foreach ($this->getAuthorities() as $authority) {
            $authority->setPositionId($this->getId());
            if ($authority->getId()) {
                $authority->update();
            } else {
                $authority->create();
            }
            $ids[] = $authority->getId();
        }
        if (count($ids) > 0) {
            foreach (ilOrgUnitAuthority::where(array(
                'id'          => $ids,
                'position_id' => $this->getId(),
            ), array( 'id' => 'NOT IN', 'position_id' => '=' ))->get() as $authority) {
                $authority->delete();
            }
        }

        if (count($ids) === 0) {
            foreach (ilOrgUnitAuthority::where(array(
                'position_id' => $this->getId(),
            ))->get() as $authority) {
                $authority->delete();
            }
        }
    }
}
