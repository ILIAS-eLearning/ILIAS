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

/**
 * Class ilCmiXapiDelModel
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider
 */

class ilCmiXapiDelModel
{
    public const DB_TABLE_NAME = 'cmix_settings';
    public const DB_USERS_TABLE_NAME = 'cmix_users';

    public const DB_DEL_OBJ = 'cmix_del_object';
    public const DB_DEL_USERS = 'cmix_del_user';

    private \ILIAS\DI\Container $dic;

    private ilDBInterface $db;

    private static ?ilCmiXapiDelModel $instance = null;

    protected ilLogger $log;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->db = $this->dic->database();
        $this->log = ilLoggerFactory::getLogger('cmix');
        //
    }

    public static function init(): ilCmiXapiDelModel
    {
        return self::$instance ?? self::$instance = new self();
    }

    public function getXapiObjIdForUser(int $userId): ?array
    {
        $data = null;
        $where = $this->db->quote($userId, 'integer');
        $result = $this->db->query("SELECT obj_id FROM " . self::DB_USERS_TABLE_NAME . " WHERE usr_id = " . $where);
        while($row = $this->db->fetchAssoc($result)) {
            if(is_null($data)) {
                $data = [];
            }
            $data[] = $row['obj_id'];
        }
        return $data;
    }

    public function setXapiUserAsDeleted(int $userId): bool
    {
        $values = [
            'usr_id' => ['integer', $userId],
            'added' => ['timestamp', date('Y-m-d H:i:s')]
        ];
        $this->db->insert(self::DB_DEL_USERS, $values);
        return true;
    }

    public function setUserAsUpdated(int $usrId)
    {
        $this->db->update(self::DB_DEL_USERS, [
            'updated' => ['timestamp', date('Y-m-d H:i:s')]
        ], [
            'usr_id' => ['integer', $usrId]
        ]);
    }

    public function resetUpdatedXapiUser(int $usrId)
    {
        $this->db->update(self::DB_DEL_USERS, [
            'updated' => ['timestamp', null]
        ], [
            'usr_id' => ['integer', $usrId]
        ]);
    }



    public function getXapiObjectsByDeletedUsers(): array
    {
        $data = [];
        $result = $this->db->query("SELECT obj.obj_id, obj.lrs_type_id, obj.activity_id, usr.usr_id, usr.usr_ident, del.added FROM " .
            self::DB_TABLE_NAME . " obj, " .
            self::DB_USERS_TABLE_NAME . " usr, " .
            self::DB_DEL_USERS . " del " .
            #" INNER JOIN " . self::DB_DEL_USERS . " del ON usr.usr_id = xdel.usr_id" .
            " WHERE usr.usr_id = del.usr_id AND obj.obj_id = usr.obj_id AND del.updated IS NULL");
        while($row = $this->db->fetchAssoc($result)) {
            if(is_null($data)) {
                $data = [];
            }
            $data[] = $row;
        }
        return $data;
    }

    public function getXapiObjectsByUser(int $userId): array
    {
        $data = [];
        $result = $this->db->query("SELECT obj.obj_id, obj.lrs_type_id, obj.activity_id FROM " .
            self::DB_TABLE_NAME . " obj, " .
            self::DB_USERS_TABLE_NAME . " usr" .
            #" INNER JOIN " . self::DB_DEL_USERS . " del ON usr.usr_id = xdel.usr_id" .
            " WHERE usr.usr_id = " . $this->db->quote($userId, 'integer') . " AND obj.obj_id = usr.obj_id");
        while($row = $this->db->fetchAssoc($result)) {
            if(is_null($data)) {
                $data = [];
            }
            $data[] = $row;
        }
        return $data;
    }

    public function getNewDeletedUsers()
    {
        $data = array();

        $result = $this->db->query("SELECT * FROM " . self::DB_DEL_USERS . " WHERE updated IS NULL");
        while($row = $this->db->fetchAssoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    public function deleteUserEntry($usrId)
    {
        $this->db->manipulateF(
            'DELETE FROM ' . self::DB_DEL_USERS . ' WHERE usr_id = %s',
            ['integer'],
            [$usrId]
        );
    }

    // XXCF OBJECTS

    public function getXapiObjectData(int $objId)
    {
        $data = null;
        $where = $this->db->quote($objId, 'integer');
        $result = $this->db->query("SELECT lrs_type_id, activity_id, delete_data FROM " . self::DB_TABLE_NAME . " WHERE obj_id = " . $where);
        while($row = $this->db->fetchAssoc($result)) {
            $data = $row;
        }
        return $data;
    }

    public function getAllXapiDelObjectData(): array
    {
        $data = array();

        $result = $this->db->query("SELECT * FROM " . self::DB_DEL_OBJ . " WHERE 1");
        while($row = $this->db->fetchAssoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    public function getNewDeletedXapiObjects()
    {
        $data = array();

        $result = $this->db->query("SELECT * FROM " . self::DB_DEL_OBJ . " WHERE updated IS NULL");
        while($row = $this->db->fetchAssoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    public function deleteXapiObjectEntry($objId)
    {
        $this->db->manipulateF(
            'DELETE FROM ' . self::DB_DEL_OBJ . ' WHERE obj_id = %s',
            ['integer'],
            [$objId]
        );
    }

    public function setXapiObjAsDeleted(int $objId, int $typeId, string $actId): void
    {
        $values = [
            'obj_id' => ['integer', $objId],
            'type_id' => ['integer', $typeId],
            'activity_id' => ['string', $actId],
            'added' => ['timestamp', date('Y-m-d H:i:s')]
        ];
        $this->db->insert(self::DB_DEL_OBJ, $values);

        if(!$this->dic->cron()->manager()->isJobActive('xapi_deletion_cron')) {
            $xapiDelete = new ilCmiXapiStatementsDeleteRequest($objId, $typeId, $actId, null, ilCmiXapiStatementsDeleteRequest::DELETE_SCOPE_ALL);
            $xapiDelete->delete();
        }
    }

    public function setXapiObjAsUpdated(int $objId)
    {

        $this->db->update(self::DB_DEL_OBJ, [
            'updated' => ['timestamp', date('Y-m-d H:i:s')]
        ], [
            'obj_id' => ['integer', $objId]
        ]);
    }

    public function resetUpdatedXapiObj(int $objId)
    {

        $this->db->update(self::DB_DEL_OBJ, [
            'updated' => ['timestamp', null]
        ], [
            'obj_id' => ['integer', $objId]
        ]);
    }

    public function removeCmixUsersForObject(int $objId): void
    {
        $this->db->manipulateF(
            'DELETE FROM cmix_users WHERE obj_id = %s',
            ['integer'],
            [$objId]
        );
        $this->log->debug('cmix_users deleted for objId=' . (string) $objId);

    }

}
