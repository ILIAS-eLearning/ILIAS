<?php declare(strict_types=1);

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
 
/**
 * Class ilObjectFactory
 * This class offers methods to get instances of
 * the type-specific object classes (derived from
 * ilObject) by their object or reference id
 * Note: The term "Ilias objects" means all
 * object types that are stored in the
 * database table "object_data"
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilObjectFactory
{
    /**
     * check if obj_id exists. To check for ref_ids use ilTree::isInTree()
     */
    public function ObjectIdExists(int $obj_id) : bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql =
            "SELECT obj_id, type, title, description, owner, create_date, last_update, import_id, offline" . PHP_EOL
            . "FROM object_data" . PHP_EOL
            . "WHERE obj_id = " . $ilDB->quote($obj_id, 'integer') . PHP_EOL
        ;

        $result = $ilDB->query($sql);

        return (bool) $result->numRows();
    }

    /**
     * returns all objects of an owner, filtered by type, objects are not deleted!
     */
    public function getObjectsForOwner(string $object_type, int $owner_id) : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sql =
            "SELECT object_data.obj_id" . PHP_EOL
            . "FROM object_data, object_reference" . PHP_EOL
            . "WHERE object_reference.obj_id = object_data.obj_id" . PHP_EOL
            . "AND object_data.type = " . $ilDB->quote($object_type, 'text') . PHP_EOL
            . "AND object_data.owner = " . $ilDB->quote($owner_id, 'integer') . PHP_EOL
        ;

        $result = $ilDB->query($sql);

        $obj_ids = [];
        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $obj_ids [] = $row["obj_id"];
        }

        return $obj_ids;
    }

    /**
     * get an instance of an Ilias object by object id
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    public static function getInstanceByObjId(?int $obj_id, bool $stop_on_error = true) : ?ilObject
    {
        global $DIC;
        $objDefinition = $DIC["objDefinition"];
        $ilDB = $DIC->database();

        // check object id
        if (!isset($obj_id)) {
            $message = "ilObjectFactory::getInstanceByObjId(): No obj_id given!";
            if ($stop_on_error === true) {
                throw new ilObjectNotFoundException($message);
            }
            return null;
        }

        // read object data
        $sql =
            "SELECT obj_id, type, title, description, owner, create_date, last_update, import_id, offline" . PHP_EOL
            . "FROM object_data" . PHP_EOL
            . "WHERE obj_id = " . $ilDB->quote($obj_id, 'integer') . PHP_EOL
        ;
        $result = $ilDB->query($sql);
        // check number of records
        if ($result->numRows() == 0) {
            $message = "ilObjectFactory::getInstanceByObjId(): Object with obj_id: " . $obj_id . " not found!";
            if ($stop_on_error === true) {
                throw new ilObjectNotFoundException($message);
            }
            return null;
        }

        $row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        $class_name = "ilObj" . $objDefinition->getClassName($row["type"]);

        // check class
        if ($class_name == "ilObj") {
            $message = "ilObjectFactory::getInstanceByObjId(): Not able to determine object " .
                "class for type" . $row["type"] . ".";
            if ($stop_on_error === true) {
                throw new ilObjectNotFoundException($message);
            }
            return null;
        }

        (new self())->includeClassIfNotExists($class_name, $row["type"], $objDefinition);

        // create instance
        $obj = new $class_name(0, false);    // this avoids reading of data
        $obj->setId($obj_id);
        $obj->read();

        return $obj;
    }

    /**
     * get an instance of an Ilias object by reference id
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    public static function getInstanceByRefId(int $ref_id, bool $stop_on_error = true) : ?ilObject
    {
        global $DIC;
        $objDefinition = $DIC["objDefinition"];
        $ilDB = $DIC->database();

        // check reference id
        if (!isset($ref_id)) {
            if ($stop_on_error === true) {
                $message = "ilObjectFactory::getInstanceByRefId(): No ref_id given!";
                throw new ilObjectNotFoundException($message);
            }
            return null;
        }

        // read object data
        $sql =
            "SELECT object_data.obj_id, object_data.type" . PHP_EOL
            . "FROM object_data, object_reference" . PHP_EOL
            . "WHERE object_reference.obj_id = object_data.obj_id" . PHP_EOL
            . "AND object_reference.ref_id = " . $ilDB->quote($ref_id, 'integer') . PHP_EOL
        ;

        $result = $ilDB->query($sql);

        // check number of records
        if ($result->numRows() == 0) {
            if ($stop_on_error === true) {
                $message = "ilObjectFactory::getInstanceByRefId(): Object with ref_id " . $ref_id . " not found!";
                throw new ilObjectNotFoundException($message);
            }
            return null;
        }

        $row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        $class_name = "ilObj" . $objDefinition->getClassName($row["type"]);

        // check class
        if ($class_name == "ilObj") {
            if ($stop_on_error === true) {
                $message = "ilObjectFactory::getInstanceByRefId(): Not able to determine object " .
                    "class for type" . $row["type"] . ".";
                throw new ilObjectNotFoundException($message);
            }
            return null;
        }

        (new self())->includeClassIfNotExists($class_name, $row["type"], $objDefinition);

        // create instance
        $obj = new $class_name(0, false);    // this avoids reading of data
        $obj->setId((int) $row["obj_id"]);
        $obj->setRefId($ref_id);
        $obj->read();
        return $obj;
    }

    /**
     * get object type by reference id
     * @throws ilObjectNotFoundException
     * @deprecated since version 5.3
     */
    public static function getTypeByRefId(int $ref_id, bool $stop_on_error = true) : ?string
    {
        global $DIC;
        $ilDB = $DIC->database();

        // check reference id
        if (!isset($ref_id)) {
            if ($stop_on_error === true) {
                $message = "ilObjectFactory::getTypeByRefId(): No ref_id given!";
                throw new ilObjectNotFoundException($message);
            }
            return null;
        }

        // read object data
        $sql =
            "SELECT object_data.obj_id, object_data.type" . PHP_EOL
            . "FROM object_data" . PHP_EOL
            . "LEFT JOIN object_reference ON object_data.obj_id=object_reference.obj_id " . PHP_EOL
            . "WHERE object_reference.ref_id=" . $ilDB->quote($ref_id, 'integer') . PHP_EOL
        ;
        $result = $ilDB->query($sql);

        if ($result->numRows() == 0) {
            if ($stop_on_error === true) {
                $message = "ilObjectFactory::getTypeByRefId(): Object with ref_id " . $ref_id . " not found!";
                throw new ilObjectNotFoundException($message);
            }
            return null;
        }

        $row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        return $row["type"];
    }

    public static function getClassByType(string $obj_type) : string
    {
        global $DIC;
        $objDefinition = $DIC["objDefinition"];

        $class_name = "ilObj" . $objDefinition->getClassName($obj_type);

        (new self())->includeClassIfNotExists($class_name, $obj_type, $objDefinition);

        return $class_name;
    }

    /**
     * Ensures a class is properly included. This is needed, since not
     * all possible classes are yet part of the autoloader (e.g. repo-plugins).
     * See: #27073
     * @param string $class_name
     * @param string $a_obj_type
     * @param ilObjectDefinition $objDefinition
     */
    protected function includeClassIfNotExists(
        string $class_name,
        string $a_obj_type,
        ilObjectDefinition $objDefinition
    ) : void {
        if (!class_exists($class_name)) {
            $location = $objDefinition->getLocation($a_obj_type);
            include_once($location . "/class." . $class_name . ".php");
        }
    }
}
