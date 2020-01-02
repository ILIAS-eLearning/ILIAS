<?php

/**
 * Class ilOrgUnitPathStorage
 *
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
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $ref_id = 0;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $obj_id = 0;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  clob
     */
    protected $path = '';
    /**
     * @var array
     */
    protected static $orgu_names = array();


    /**
     * @return array
     */
    public static function getAllOrguRefIds()
    {
        $names = self::getAllOrguNames();

        return array_keys($names);
    }


    public function store()
    {
        if (self::where(array( 'ref_id' => $this->getRefId() ))->hasSets()) {
            $this->update();
        } else {
            $this->create();
        }
    }


    /**
     * Format comma seperated ref_ids into comma seperated string representation (also filters out deleted orgunits).
     * Return "-" if $string is empty
     *
     * @param int $user_id
     * @param string $separator
     * @param bool $using_tmp_table second implementation
     *
     * @return string   comma seperated string representations of format: [OrgUnit Title] - [OrgUnits corresponding Level 1 Title]
     */
    public static function getTextRepresentationOfUsersOrgUnits($user_id, $separator = self::ORG_SEPARATOR, $using_tmp_table = true)
    {
        if ($using_tmp_table) {
            global $DIC;
            /**
             * @var ilDBInterface $ilDB
             */
            $ilDB = $DIC['ilDB'];
            ilObjOrgUnitTree::_getInstance()->buildTempTableWithUsrAssignements();

            $res = $ilDB->queryF("SELECT " . $ilDB->groupConcat("path", $separator) . " AS orgus FROM orgu_usr_assignements WHERE user_id = %s GROUP BY user_id;", array( 'integer' ), array( $user_id ));
            $dat = $ilDB->fetchObject($res);

            return $dat->orgus ? $dat->orgus : '-';
        } else {
            $array_of_org_ids = ilObjOrgUnitTree::_getInstance()->getOrgUnitOfUser($user_id);

            if (!$array_of_org_ids) {
                return '-';
            }
            $paths = ilOrgUnitPathStorage::where(array( 'ref_id' => $array_of_org_ids ))->getArray(null, 'path');

            return implode($separator, $paths);
        }
    }


    /**
     * Get ref id path array
     *
     * @param bool $sort_by_title
     *
     * @return array
     */
    public static function getTextRepresentationOfOrgUnits($sort_by_title = true)
    {
        if ($sort_by_title) {
            return ilOrgUnitPathStorage::orderBy('path')->getArray('ref_id', 'path');
        } else {
            return ilOrgUnitPathStorage::getArray('ref_id', 'path');
        }
    }


    /**
     * @param $ref_id
     * @return bool
     */
    public static function writePathByRefId($ref_id)
    {
        $original_ref_id = $ref_id;
        $names = self::getAllOrguNames();
        $root_ref_id = ilObjOrgUnit::getRootOrgRefId();
        $tree = ilObjOrgUnitTree::_getInstance();
        $path = array( $names[$ref_id] );
        if ($ref_id == $root_ref_id || !$ref_id) {
            return false;
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
            $expression = implode(self::GLUE_SIMPLE, [ $first, $middle, $last ]);
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

        return true;
    }


    public static function clearDeleted()
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


    /**
     * @param $ref_id
     * @return bool
     * @currently_unused
     */
    protected static function writeFullPathByRefId($ref_id)
    {
        $original_ref_id = $ref_id;
        $names = self::getAllOrguNames();
        $root_ref_id = ilObjOrgUnit::getRootOrgRefId();
        $tree = ilObjOrgUnitTree::_getInstance();
        $path = array( $names[$ref_id] );
        if ($ref_id == $root_ref_id || !$ref_id) {
            return false;
        }
        while ($ref_id != $root_ref_id && $ref_id) {
            $ref_id = $tree->getParent($ref_id);
            if ($ref_id != $root_ref_id && $names[$ref_id]) {
                $path[] = $names[$ref_id];
            }
        }

        $path = array_reverse($path);

        $expression = implode(self::GLUE, $path);
        /**
         * @var $ilOrgUnitPathStorage ilOrgUnitPathStorage
         */
        $ilOrgUnitPathStorage = self::findOrGetInstance($original_ref_id);
        $ilOrgUnitPathStorage->setRefId($original_ref_id);
        $ilOrgUnitPathStorage->setObjId(ilObject2::_lookupObjectId($original_ref_id));
        $ilOrgUnitPathStorage->setPath($expression);
        $ilOrgUnitPathStorage->store();

        return true;
    }


    /**
     * @param null $lng_key
     * @return array
     */
    public static function getAllOrguNames($lng_key = null)
    {
        if (count(self::$orgu_names) == 0) {
            global $DIC;
            /**
             * @var ilDBInterface $ilDB
             */
            $ilDB = $DIC['ilDB'];
            $res = $ilDB->queryF('SELECT * FROM object_reference
			JOIN object_data ON object_reference.obj_id = object_data.obj_id AND deleted IS NULL
			WHERE object_data.type = %s', array( 'text' ), array( 'orgu' ));
            while ($data = $ilDB->fetchObject($res)) {
                self::$orgu_names[$data->ref_id] = $data->title;
            }
        }

        return self::$orgu_names;
    }


    /**
     * @return string
     */
    public function getConnectorContainerName()
    {
        return self::TABLE_NAME;
    }


    /**
     * @return int
     */
    public function getRefId()
    {
        return $this->ref_id;
    }


    /**
     * @param int $ref_id
     */
    public function setRefId($ref_id)
    {
        $this->ref_id = $ref_id;
    }


    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }


    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }


    /**
     * @param int $obj_id
     */
    public function setObjId($obj_id)
    {
        $this->obj_id = $obj_id;
    }
}
