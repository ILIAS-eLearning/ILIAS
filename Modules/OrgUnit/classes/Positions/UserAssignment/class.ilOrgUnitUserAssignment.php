<?php

use ILIAS\DI\Container;

/**
 * Class ilOrgUnitUserAssignment
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitUserAssignment extends \ActiveRecord
{
    final public static function returnDbTableName() : string
    {
        return 'il_orgu_ua';
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
    private int $id = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    private int $user_id = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    private int $position_id = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    private int $orgu_id = 0;

    final public static function findOrCreateAssignment(int $user_id, int $position_id, int $orgu_id): ilOrgUnitUserAssignment
    {
        $inst = self::where(array(
            'user_id' => $user_id,
            'position_id' => $position_id,
            'orgu_id' => $orgu_id
        ))->first();
        if (!$inst) {
            $inst = new self();
            $inst->setPositionId($position_id);
            $inst->setUserId($user_id);
            $inst->setOrguId($orgu_id);
            $inst->create();
        }

        return $inst;
    }

    final protected function raiseEvent(string $event): void
    {
        global $DIC;

        if (!$DIC->offsetExists('ilAppEventHandler')) {
            return;
        }
        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        $ilAppEventHandler->raise('Modules/OrgUnit', $event, array(
            'obj_id' => $this->getOrguId(),
            'usr_id' => $this->getUserId(),
            'position_id' => $this->getPositionId()
        ));
    }

    final public function create() : void
    {
        $this->raiseEvent('assignUserToPosition');
        parent::create();
    }

    final public function delete(): void
    {
        $this->raiseEvent('deassignUserFromPosition');
        parent::delete();
    }

    final public function getId(): int
    {
        return $this->id;
    }

    final public function setId(int $id): void
    {
        $this->id = $id;
    }

    final public function getUserId(): int
    {
        return $this->user_id;
    }

    final public function setUserId(int $user_id)
    {
        $this->user_id = $user_id;
    }

    final  public function getPositionId(): int
    {
        return $this->position_id;
    }

    final public function setPositionId(int $position_id)
    {
        $this->position_id = $position_id;
    }

    final public function getOrguId(): int
    {
        return $this->orgu_id;
    }

    final public function setOrguId(int $orgu_id): void
    {
        $this->orgu_id = $orgu_id;
    }
}
