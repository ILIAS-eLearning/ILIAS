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

use ILIAS\DI\Container;

/**
 * Class ilOrgUnitUserAssignment
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitUserAssignment extends \ActiveRecord
{
    public static function returnDbTableName(): string
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
    protected ?int $id = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $user_id = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $position_id = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $orgu_id = 0;

    public static function findOrCreateAssignment(int $user_id, int $position_id, int $orgu_id): ilOrgUnitUserAssignment
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

    protected function raiseEvent(string $event): void
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

    public function create(): void
    {
        $this->raiseEvent('assignUserToPosition');
        parent::create();
    }

    public function delete(): void
    {
        $this->raiseEvent('deassignUserFromPosition');
        parent::delete();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id)
    {
        $this->user_id = $user_id;
    }

    public function getPositionId(): int
    {
        return $this->position_id;
    }

    public function setPositionId(int $position_id)
    {
        $this->position_id = $position_id;
    }

    public function getOrguId(): int
    {
        return $this->orgu_id;
    }

    public function setOrguId(int $orgu_id): void
    {
        $this->orgu_id = $orgu_id;
    }
}
