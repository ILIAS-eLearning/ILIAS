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

/**
 * Class ilOrgUnitPosition
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPosition extends \ActiveRecord
{
    public const CORE_POSITION_EMPLOYEE = 1;
    public const CORE_POSITION_SUPERIOR = 2;

    public static function returnDbTableName() : string
    {
        return "il_orgu_positions";
    }

    /**
     * Override for correct on return value
     * @return ilOrgUnitPosition[]
     */
    public static function get() : array
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::get();
    }

    public static function getCorePosition(int $core_identifier) : self
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return self::where(['core_identifier' => $core_identifier])->first();
    }

    public static function getCorePositionId(int $core_identifier) : int
    {
        return self::getCorePosition($core_identifier)->getId();
    }

    /**
     * @throws ilException
     */
    public function delete() : void
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
    public static function getActive() : array
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
     * @return ilOrgUnitPosition[] array of Positions (all core-positions and all positions which
     *                              have already UserAssignments at this place)
     */
    public static function getActiveForPosition(int $orgu_ref_id) : array
    {
        arObjectCache::flush(self::class);
        $q = "SELECT DISTINCT il_orgu_positions.id, il_orgu_positions.*
 				FROM il_orgu_positions 
 				LEFT JOIN il_orgu_ua ON il_orgu_positions.id = il_orgu_ua.position_id AND il_orgu_ua.orgu_id = %s 
 				WHERE il_orgu_ua.user_id IS NOT NULL 
 					OR core_position = 1";
        $database = $GLOBALS['DIC']->database();
        $st = $database->queryF($q, array('integer'), array($orgu_ref_id));

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
     * @con_is_primary true
     * @con_is_unique  true
     * @con_sequence   true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     * @con_length     8
     */
    protected ?int $id = 0;
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     512
     */
    protected string $title = "";
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     4000
     */
    protected string $description = "";
    /**
     * @var bool
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected bool $core_position = false;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     */
    protected int $core_identifier = 0;
    /**
     * @var ilOrgUnitAuthority[]
     */
    protected array $authorities = array();

    public function afterObjectLoad() : void
    {
        $this->authorities = ilOrgUnitAuthority::where(array(ilOrgUnitAuthority::POSITION_ID => $this->getId()))
                                               ->get();
    }

    public function update() : void
    {
        parent::update();
        $this->storeAuthorities();
    }

    public function create() : void
    {
        parent::create();
        $this->storeAuthorities();
    }

    public function getAuthoritiesAsArray() : array
    {
        $return = array();
        foreach ($this->authorities as $authority) {
            $return[] = $authority->__toArray();
        }

        return $return;
    }

    public function __toString() : string
    {
        return $this->getTitle();
    }

    /**
     * @return ilOrgUnitAuthority[] it's own authorities and also all which use this position
     */
    public function getDependentAuthorities() : array
    {
        $dependent = ilOrgUnitAuthority::where(array(ilOrgUnitAuthority::FIELD_OVER => $this->getId()))
                                       ->get();

        return $dependent + $this->authorities;
    }

    /**
     * This deletes the Position, it's Authorities, dependent Authorities and all User-Assignements!
     */
    public function deleteWithAllDependencies() : void
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

    public function getId() : ?int
    {
        return $this->id;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function isCorePosition() : bool
    {
        return $this->core_position;
    }

    public function setCorePosition(bool $core_position)
    {
        $this->core_position = $core_position;
    }

    /**
     * @return ilOrgUnitAuthority[]
     */
    public function getAuthorities() : array
    {
        return $this->authorities;
    }

    /**
     * @param ilOrgUnitAuthority[] $authorities
     */
    public function setAuthorities(array $authorities)
    {
        $this->authorities = $authorities;
    }

    /**
     * @return int
     */
    public function getCoreIdentifier() : int
    {
        return $this->core_identifier;
    }

    /**
     * @param int $core_identifier
     */
    public function setCoreIdentifier(int $core_identifier)
    {
        $this->core_identifier = $core_identifier;
    }

    private function storeAuthorities() : void
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
            foreach (
                ilOrgUnitAuthority::where(array(
                    'id' => $ids,
                    'position_id' => $this->getId(),
                ), array('id' => 'NOT IN', 'position_id' => '='))->get() as $authority
            ) {
                $authority->delete();
            }
        }

        if (count($ids) === 0) {
            foreach (
                ilOrgUnitAuthority::where(array(
                    'position_id' => $this->getId(),
                ))->get() as $authority
            ) {
                $authority->delete();
            }
        }
    }
}
