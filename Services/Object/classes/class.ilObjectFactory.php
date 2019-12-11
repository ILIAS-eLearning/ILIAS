<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjectFactory
*
* This class offers methods to get instances of
* the type-specific object classes (derived from
* ilObject) by their object or reference id
*
* Note: The term "Ilias objects" means all
* object types that are stored in the
* database table "object_data"
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilObjectFactory
{
    /**
    * check if obj_id exists. To check for ref_ids use ilTree::isInTree()
    *
    * @param	int		$obj_id		object id
    * @return	bool
    */
    public function ObjectIdExists($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM object_data " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        
        return $res->numRows() ? true : false;
    }
    
    /**
     * returns all objects of an owner, filtered by type, objects are not deleted!
     *
     * @param unknown_type $object_type
     * @param unknown_type $owner_id
     * @return unknown
     */
    public function getObjectsForOwner($object_type, $owner_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM object_data,object_reference " .
            "WHERE object_reference.obj_id = object_data.obj_id " .
            " AND object_data.type=" . $ilDB->quote($object_type, 'text') .
            " AND object_data.owner = " . $ilDB->quote($owner_id, 'integer');
        $res = $ilDB->query($query);

        $obj_ids = array();
        while ($object_rec = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $obj_ids [] = $object_rec["obj_id"];
        }
        
        return $obj_ids;
    }
        
    /**
     * get an instance of an Ilias object by object id
     * @param $a_obj_id
     * @param bool $stop_on_error
     * @return bool|ilObject
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    public static function getInstanceByObjId($a_obj_id, $stop_on_error = true)
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        $ilDB = $DIC->database();

        // check object id
        if (!isset($a_obj_id)) {
            $message = "ilObjectFactory::getInstanceByObjId(): No obj_id given!";
            if ($stop_on_error === true) {
                throw new ilObjectNotFoundException($message);
            }

            return false;
        }

        // read object data
        $q = "SELECT * FROM object_data " .
             "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $object_set = $ilDB->query($q);
        // check number of records
        if ($object_set->numRows() == 0) {
            $message = "ilObjectFactory::getInstanceByObjId(): Object with obj_id: " . $a_obj_id . " not found!";
            if ($stop_on_error === true) {
                throw new ilObjectNotFoundException($message);
            }
            return false;
        }

        $object_rec = $object_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        $class_name = "ilObj" . $objDefinition->getClassName($object_rec["type"]);
        
        // check class
        if ($class_name == "ilObj") {
            $message = "ilObjectFactory::getInstanceByObjId(): Not able to determine object " .
                "class for type" . $object_rec["type"] . ".";
            if ($stop_on_error === true) {
                throw new ilObjectNotFoundException($message);
            }
            return false;
        }

        // get location
        $location = $objDefinition->getLocation($object_rec["type"]);

        // create instance
        include_once($location . "/class." . $class_name . ".php");
        $obj = new $class_name(0, false);	// this avoids reading of data
        $obj->setId($a_obj_id);
        $obj->read();

        return $obj;
    }


    /**
     * get an instance of an Ilias object by reference id
     * @param $a_ref_id
     * @param bool $stop_on_error
     * @return bool|ilObject
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    public static function getInstanceByRefId($a_ref_id, $stop_on_error = true)
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        $ilDB = $DIC->database();

        // check reference id
        if (!isset($a_ref_id)) {
            if ($stop_on_error === true) {
                $message = "ilObjectFactory::getInstanceByRefId(): No ref_id given!";
                throw new ilObjectNotFoundException($message);
            }
            
            return false;
        }

        // read object data
        
        $query = "SELECT * FROM object_data,object_reference " .
            "WHERE object_reference.obj_id = object_data.obj_id " .
            "AND object_reference.ref_id = " . $ilDB->quote($a_ref_id, 'integer');
        $object_set = $ilDB->query($query);

        // check number of records
        if ($object_set->numRows() == 0) {
            if ($stop_on_error === true) {
                $message = "ilObjectFactory::getInstanceByRefId(): Object with ref_id " . $a_ref_id . " not found!";
                throw new ilObjectNotFoundException($message);
            }
            
            return false;
        }

        $object_rec = $object_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        $class_name = "ilObj" . $objDefinition->getClassName($object_rec["type"]);

        // check class
        if ($class_name == "ilObj") {
            if ($stop_on_error === true) {
                $message = "ilObjectFactory::getInstanceByRefId(): Not able to determine object " .
                           "class for type" . $object_rec["type"] . ".";
                throw new ilObjectNotFoundException($message);
            }
            
            return false;
        }

        // get location
        $location = $objDefinition->getLocation($object_rec["type"]);

        // create instance
        include_once($location . "/class." . $class_name . ".php");
        $obj = new $class_name(0, false);	// this avoids reading of data
        $obj->setId($object_rec["obj_id"]);
        $obj->setRefId($a_ref_id);
        $obj->read();
        return $obj;
    }

    /**
     * get object type by reference id
     *
     * @deprecated since version 5.3
     * @param $a_ref_id
     * @param bool $stop_on_error
     * @return bool
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    public static function getTypeByRefId($a_ref_id, $stop_on_error = true)
    {
        global $DIC;

        $ilDB = $DIC->database();

        // check reference id
        if (!isset($a_ref_id)) {
            if ($stop_on_error === true) {
                $message = "ilObjectFactory::getTypeByRefId(): No ref_id given!";
                throw new ilObjectNotFoundException($message);
            }
            
            return false;
        }

        // read object data
        $q = "SELECT * FROM object_data " .
             "LEFT JOIN object_reference ON object_data.obj_id=object_reference.obj_id " .
             "WHERE object_reference.ref_id=" . $ilDB->quote($a_ref_id, 'integer');
        $object_set = $ilDB->query($q);

        if ($object_set->numRows() == 0) {
            if ($stop_on_error === true) {
                $message = "ilObjectFactory::getTypeByRefId(): Object with ref_id " . $a_ref_id . " not found!";
                throw new ilObjectNotFoundException($message);
            }
            
            return false;
        }

        $object_rec = $object_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        return $object_rec["type"];
    }
    
    /**
     * Get class by type
     *
     * @return
     */
    public static function getClassByType($a_obj_type)
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];

        $location = $objDefinition->getLocation($a_obj_type);
        $class_name = "ilObj" . $objDefinition->getClassName($a_obj_type);

        // create instance
        include_once($location . "/class." . $class_name . ".php");
        return $class_name;
    }
}
