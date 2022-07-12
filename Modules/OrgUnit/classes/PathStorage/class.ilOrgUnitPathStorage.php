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
 * Class ilOrgUnitPathStorage
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPathStorage extends ActiveRecord
{
    const GLUE = ' > ';
    const GLUE_SIMPLE = ' - ';
    const ORG_SEPARATOR = ' | ';
    const TABLE_NAME = 'orgu_path_storage';
    const MAX_MIDDLE_PATH_LENGTH = 50;
    /**
     * @var int
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?int $ref_id = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $obj_id = 0;
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  clob
     */
    protected string $path = '';

    protected static array $orgu_names = array();

    /**
     * @return string[]
     */
    public static function getAllOrguRefIds() : array
    {
        $names = self::getAllOrguNames();

        return array_keys($names);
    }

    public function store() : void
    {
        if (self::where(array('ref_id' => $this->getRefId()))->hasSets()) {
            $this->update();
        } else {
            $this->create();
        }
    }

    /**
     * Format comma seperated ref_ids into comma seperated string representation (also filters out deleted orgunits).
     * Return "-" if $string is empty
     * @param int    $user_id
     * @param string $separator
     * @param bool   $using_tmp_table second implementation
     * @return string   comma seperated string representations of format: [OrgUnit Title] - [OrgUnits corresponding Level 1 Title]
     */
    public static function getTextRepresentationOfUsersOrgUnits(
        int $user_id,
        string $separator = self::ORG_SEPARATOR,
        bool $using_tmp_table = true
    ) {
        if ($using_tmp_table === true) {
            global $DIC;
            /**
             * @var ilDBInterface $ilDB
             */
            $ilDB = $DIC['ilDB'];
            ilObjOrgUnitTree::_getInstance()->buildTempTableWithUsrAssignements();

            $res = $ilDB->queryF(
                "SELECT " . $ilDB->groupConcat(
                    "path",
                    $separator
                ) . " AS orgus FROM orgu_usr_assignements WHERE user_id = %s GROUP BY user_id;",
                array('integer'),
                array($user_id)
            );
            $dat = $ilDB->fetchObject($res);

            return (isset($dat->orgus) && $dat->orgus) ? $dat->orgus : '-';
        } else {
            $array_of_org_ids = ilObjOrgUnitTree::_getInstance()->getOrgUnitOfUser($user_id);

            if (!$array_of_org_ids) {
                return '-';
            }
            $paths = ilOrgUnitPathStorage::where(array('ref_id' => $array_of_org_ids))->getArray(null, 'path');

            return implode($separator, $paths);
        }
    }

    /**
     * Get ref id path array
     * @param bool $sort_by_title
     * @return array
     */
    public static function getTextRepresentationOfOrgUnits(bool $sort_by_title = true) : array
    {
        if ($sort_by_title) {
            return ilOrgUnitPathStorage::orderBy('path')->getArray('ref_id', 'path');
        }
        return ilOrgUnitPathStorage::getArray('ref_id', 'path');
    }

    public static function writePathByRefId(string $ref_id) : void
    {
        $original_ref_id = $ref_id;
        $names = self::getAllOrguNames();
        $root_ref_id = ilObjOrgUnit::getRootOrgRefId();
        $tree = ilObjOrgUnitTree::_getInstance();
        $path = array($names[$ref_id]);
        if ($ref_id == $root_ref_id || !$ref_id) {
            return;
        }
        while ($ref_id != $root_ref_id && $ref_id) {
            $ref_id = $tree->getParent($ref_id);
            if ($ref_id != $root_ref_id && $names[$ref_id]) {
                $path[] = $names[$ref_id];
            }
        }

        if (count($path) > 2) {
            $first = array_shift($path);
            $last = array_pop($path);
            $middle = implode(self::GLUE_SIMPLE, $path);
            if (strlen($middle) > self::MAX_MIDDLE_PATH_LENGTH) {
                $middle = substr($middle, 0, self::MAX_MIDDLE_PATH_LENGTH) . " ...";
            }
            $expression = implode(self::GLUE_SIMPLE, [$first, $middle, $last]);
        } else {
            $expression = implode(self::GLUE_SIMPLE, $path);
        }
        /**
         * @var $ilOrgUnitPathStorage ilOrgUnitPathStorage
         */
        $ilOrgUnitPathStorage = self::findOrGetInstance($original_ref_id);
        $ilOrgUnitPathStorage->setRefId($original_ref_id);
        $ilOrgUnitPathStorage->setObjId(ilObject2::_lookupObjectId($original_ref_id));
        $ilOrgUnitPathStorage->setPath($expression);
        $ilOrgUnitPathStorage->store();
    }

    public static function clearDeleted() : void
    {
        global $DIC;
        /**
         * @var ilDBInterface $ilDB
         */
        $ilDB = $DIC['ilDB'];
        $ref_ids = self::getAllOrguRefIds();
        $q = "DELETE FROM " . self::TABLE_NAME . " WHERE  " . $ilDB->in('ref_id', $ref_ids, true, 'integer');
        $ilDB->manipulate($q);
    }

    /** @return string[] */
    public static function getAllOrguNames(array $lng_key = null) : array
    {
        if (count(self::$orgu_names) == 0) {
            global $DIC;
            /**
             * @var ilDBInterface $ilDB
             */
            $ilDB = $DIC['ilDB'];
            $res = $ilDB->queryF('SELECT * FROM object_reference
			JOIN object_data ON object_reference.obj_id = object_data.obj_id AND deleted IS NULL
			WHERE object_data.type = %s', array('text'), array('orgu'));
            while ($data = $ilDB->fetchObject($res)) {
                self::$orgu_names[$data->ref_id] = $data->title;
            }
        }

        return self::$orgu_names;
    }

    public function getConnectorContainerName() : string
    {
        return self::TABLE_NAME;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function setRefId(int $ref_id) : void
    {
        $this->ref_id = $ref_id;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function setPath(string $path) : void
    {
        $this->path = $path;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id) : void
    {
        $this->obj_id = $obj_id;
    }
}
