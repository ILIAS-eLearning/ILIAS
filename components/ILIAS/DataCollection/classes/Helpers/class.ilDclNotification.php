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

class ilDclNotification
{
    private const string TABLE_NAME = 'il_dcl_notification';

    public function __construct(private readonly ILDBInterface $db)
    {
    }

    public function has(ilObjDataCollection $obj, int $user_id, ilDclNotificationType $type): bool
    {
        return null !== $this->db->fetchAssoc($this->db->queryF(
            'SELECT 1 FROM ' . $this::TABLE_NAME . ' WHERE obj_id = %s AND usr_id = %s AND setting = %s LIMIT 1',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$obj->getId(), $user_id, $type->value]
        ));
    }

    public function add(ilObjDataCollection $obj, ilObjUser $user, ilDclNotificationType $type): void
    {
        if (!$this->has($obj, $user->getId(), $type)) {
            $this->db->insert('il_dcl_notification', [
                'obj_id' => [ilDBConstants::T_INTEGER, $obj->getId()],
                'usr_id' => [ilDBConstants::T_INTEGER, $user->getId()],
                'setting' => [ilDBConstants::T_INTEGER, $type->value]
            ]);
        }
    }

    public function delete(ilObjDataCollection $obj, ilObjUser $user, ilDclNotificationType $type): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . $this::TABLE_NAME . ' WHERE obj_id = %s AND usr_id = %s AND setting = %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$obj->getId(), $user->getId(), $type->value]
        );
    }

    public function clear(ilObjDataCollection $obj, ilObjUser $user): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . $this::TABLE_NAME . ' WHERE obj_id = %s AND usr_id = %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$obj->getId(), $user->getId()]
        );
    }

    public function deleteForObject(ilObjDataCollection $obj): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . $this::TABLE_NAME . ' WHERE obj_id = %s',
            [ilDBConstants::T_INTEGER],
            [$obj->getId()]
        );
    }

    public function deleteForUser(int $user_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . $this::TABLE_NAME . ' WHERE usr_id = %s',
            [ilDBConstants::T_INTEGER],
            [$user_id]
        );
    }
}
