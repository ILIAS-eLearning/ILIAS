<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * See bug discussion 24472
 *
 * Do not use this class yet. This might need a general factory interface first.
 *
 * This could be moved to $DIC->object() service asap.
 *
 *
 * @author <killing@leifos.de>
 *
 */
class ilObjectGUIFactory
{
    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * Constructor
     * @param ilObjectDefinition|null $obj_definition
     * @param ilDBInterface|null $db
     */
    public function __construct(ilObjectDefinition $obj_definition = null, ilDBInterface $db = null)
    {
        global $DIC;

        if (is_null($obj_definition)) {
            $obj_definition = $DIC["objDefinition"];
        }
        $this->obj_definition = $obj_definition;

        if (is_null($db)) {
            $db = $DIC->database();
        }
        $this->db = $db;
    }


    /**
     * Get ilObj...GUI instance by reference id
     *
     * @param $a_ref_id
     * @return mixed
     * @throws ilObjectException
     * @throws ilObjectNotFoundException
     */
    public function getInstanceByRefId($a_ref_id)
    {
        $obj_definition = $this->obj_definition;
        $db = $this->db;

        // check reference id
        if (!isset($a_ref_id)) {
            throw new ilObjectNotFoundException("ilObjectGUIFactory::getInstanceByRefId(): No ref_id given!");
        }

        // check if object exists
        $set = $db->queryF(
            "SELECT * FROM object_data,object_reference " .
            "WHERE object_reference.obj_id = object_data.obj_id " .
            "AND object_reference.ref_id = %s ",
            array("integer"),
            array($a_ref_id)
        );
        if (!($rec = $db->fetchAssoc($set))) {
            throw new ilObjectNotFoundException("ilObjectGUIFactory::getInstanceByRefId(): Object with ref_id " . $a_ref_id . " not found!");
        }

        // check class name
        $class_name = "ilObj" . $obj_definition->getClassName($rec["type"]) . "GUI";
        if ($class_name == "ilObjGUI") {
            throw new ilObjectException("ilObjectGUIFactory::getInstanceByRefId(): Not able to determine object " .
                "class for type" . $rec["type"] . ".");
        }

        // create instance
        $location = $obj_definition->getLocation($rec["type"]);
        include_once($location . "/class." . $class_name . ".php");
        $gui_obj = new $class_name("", $a_ref_id, true, false);

        return $gui_obj;
    }
}
