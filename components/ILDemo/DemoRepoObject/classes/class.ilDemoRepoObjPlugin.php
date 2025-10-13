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

class ilDemoRepoObjPlugin extends ilRepositoryObjectPlugin
{
    public const COPY_OPERATION_ID = 58;

    public function getPluginName(): string
    {
        return 'DemoRepoObj';
    }

    public function uninstallCustom(): void
    {
    }

    /**
     * @return bool
     * @throws ilPluginException
     */
    protected function beforeActivation(): bool
    {
        parent::beforeActivation();
        global $DIC;
        $db = $DIC->database();

        $type = $this->getId();

        if (!$this->isRepositoryPlugin($type)) {
            throw new ilPluginException("Object plugin type must start with an x. Current type is " . $type . ".");
        }

        $type_id = $this->getTypeId($type, $db);
        if (!$type_id) {
            $type_id = $this->createTypeId($type, $db);
        }

        $this->assignCopyPermissionToPlugin((int) $type_id, $db);

        return true;
    }

    protected function isRepositoryPlugin(string $type): bool
    {
        return substr($type, 0, 1) == "x";
    }

    /**
     * @param string $type
     * @param $db
     * @return int | null
     */
    protected function getTypeId(string $type, ilDBInterface $db)
    {
        $sql =
             "SELECT obj_id FROM object_data" . PHP_EOL
            . "WHERE type = " . $db->quote("typ", "text") . PHP_EOL
            . "AND title = " . $db->quote($type, "text") . PHP_EOL
        ;

        $result = $db->query($sql);

        if ($db->numRows($result) == 0) {
            return null;
        }

        $rec = $db->fetchAssoc($result);
        return $rec["obj_id"];
    }

    /**
     * Create a new entry in object data
     *
     * @param string 	$type
     * @param 			$db
     *
     * @return int
     */
    protected function createTypeId(string $type, ilDBInterface $db)
    {
        $type_id = $db->nextId("object_data");

        $sql =
             "INSERT INTO object_data" . PHP_EOL
            . "(obj_id, type, title, description, owner, create_date, last_update)" . PHP_EOL
            . "VALUES (" . PHP_EOL
            . $db->quote($type_id, "integer") . "," . PHP_EOL
            . $db->quote("typ", "text") . "," . PHP_EOL
            . $db->quote($type, "text") . "," . PHP_EOL
            . $db->quote("Plugin " . $this->getPluginName(), "text") . "," . PHP_EOL
            . $db->quote(-1, "integer") . "," . PHP_EOL
            . $db->quote(ilUtil::now(), "timestamp") . "," . PHP_EOL
            . $db->quote(ilUtil::now(), "timestamp") . PHP_EOL
            . ")" . PHP_EOL
        ;

        $db->manipulate($sql);

        return $type_id;
    }

    protected function assignCopyPermissionToPlugin(int $type_id, ilDBInterface $db)
    {
        $ops = array(self::COPY_OPERATION_ID);

        foreach ($ops as $op) {
            if (!$this->permissionIsAssigned($type_id, $op, $db)) {
                $sql =
                     "INSERT INTO rbac_ta" . PHP_EOL
                    . "(typ_id, ops_id)" . PHP_EOL
                    . "VALUES (" . PHP_EOL
                    . $db->quote($type_id, "integer") . "," . PHP_EOL
                    . $db->quote($op, "integer") . PHP_EOL
                    . ")" . PHP_EOL
                ;

                $db->manipulate($sql);
            }
        }
    }

    protected function permissionIsAssigned(int $type_id, int $op_id, ilDBInterface $db): bool
    {
        $sql =
             "SELECT count(typ_id) as cnt" . PHP_EOL
            . "FROM rbac_ta" . PHP_EOL
            . "WHERE typ_id = " . $db->quote($type_id, "integer") . PHP_EOL
            . "AND ops_id = " . $db->quote($op_id, "integer") . PHP_EOL
        ;

        $set = $db->query($sql);
        $rec = $db->fetchAssoc($set);

        return $rec["cnt"] > 0;
    }

}
