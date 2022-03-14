<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * See bug discussion 24472
 *
 * Do not use this class yet. This might need a general factory interface first.
 * This could be moved to $DIC->object() service asap.
 *
 * @author <killing@leifos.de>
 *
 */
class ilObjectGUIFactory
{
    protected ilObjectDefinition $obj_definition;
    protected ilDBInterface $db;

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
     * @throws ilObjectException
     * @throws ilObjectNotFoundException
     */
    public function getInstanceByRefId(int $ref_id) : ilObject
    {
        // check reference id
        if (!isset($ref_id)) {
            throw new ilObjectNotFoundException("ilObjectGUIFactory::getInstanceByRefId(): No ref_id given!");
        }

        $sql =
            "SELECT object_data.type" . PHP_EOL
            ."FROM object_data,object_reference" . PHP_EOL
            ."WHERE object_reference.obj_id = object_data.obj_id" . PHP_EOL
            ."AND object_reference.ref_id = %s" . PHP_EOL
        ;
        // check if object exists
        $result = $this->db->queryF($sql, ["integer"], [$ref_id]);
        if (!($row = $this->db->fetchAssoc($result))) {
            throw new ilObjectNotFoundException(
                "ilObjectGUIFactory::getInstanceByRefId(): Object with ref_id " . $ref_id . " not found!"
            );
        }

        // check class name
        $class_name = "ilObj" . $this->obj_definition->getClassName($row["type"]) . "GUI";
        if ($class_name == "ilObjGUI") {
            throw new ilObjectException("ilObjectGUIFactory::getInstanceByRefId(): Not able to determine object " .
                "class for type" . $row["type"] . ".");
        }

        // create instance
        $location = $this->obj_definition->getLocation($row["type"]);
        include_once($location . "/class." . $class_name . ".php");
        return new $class_name("", $ref_id, true, false);
    }
}
