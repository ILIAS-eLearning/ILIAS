<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Object/classes/class.ilObject.php';

/**
 * Class ilObjLTIAdministration
 * @author Jesús López <lopez@leifos.com>
 *
 * @package ServicesLTI
 */
class ilObjLTIAdministration extends ilObject
{
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "ltis";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * @return string[] Array of lti provider supportting object types
     */
    public function getLTIObjectTypes()
    {
        $obj_def = new ilObjectDefinition();
        return $obj_def->getLTIProviderTypes();
    }

    /**
     * @return array available roles for LTI
     */
    public function getLTIRoles()
    {
        global $rbacreview;

        require_once("Services/AccessControl/classes/class.ilObjRole.php");

        $global_roles = $rbacreview->getGlobalRoles();

        $filtered_roles = array_diff($global_roles, array(SYSTEM_ROLE_ID, ANONYMOUS_ROLE_ID));

        $roles = array();
        foreach ($filtered_roles as $role) {
            $obj_role = new ilObjRole($role);
            $roles[$role] = $obj_role->getTitle();
        }

        return $roles;
    }

    /**
     * @param integer $a_consumer_id
     * @param array $a_obj_types
     */
    public function saveConsumerObjectTypes($a_consumer_id, $a_obj_types)
    {
        global $ilDB;

        $ilDB->manipulate("DELETE FROM lti_ext_consumer_otype WHERE consumer_id = " . $ilDB->quote($a_consumer_id, "integer"));

        if ($a_obj_types) {
            $query = "INSERT INTO lti_ext_consumer_otype (consumer_id, object_type) VALUES (%s, %s)";
            $types = array("integer", "text");
            foreach ($a_obj_types as $ot) {
                $values = array($a_consumer_id, $ot);
                $ilDB->manipulateF($query, $types, $values);
            }
        }
    }

    /**
     * @param integer $a_consumer_id
     * @return array consumer active objects
     */
    public function getActiveObjectTypes($a_consumer_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query("SELECT object_type FROM lti_ext_consumer_otype WHERE consumer_id = " . $ilDB->quote($a_consumer_id, "integer"));

        $obj_ids = array();
        while ($record = $ilDB->fetchAssoc($result)) {
            array_push($obj_ids, $record['object_type']);
        }
        return $obj_ids;
    }
    
    /**
     * Check if any consumer is enabled for an object type
     */
    public static function isEnabledForType($a_type)
    {
        /**
         * @var ilDBInterface
         */
        $db = $GLOBALS['DIC']->database();
        
        $query = 'select id from lti_ext_consumer join lti_ext_consumer_otype on id = consumer_id ' .
            'WHERE active = ' . $db->quote(1, 'integer') . ' ' .
            'AND object_type = ' . $db->quote($a_type, 'text');
        $res = $db->query($query);
        while ($row = $res->fetchObject()) {
            return true;
        }
        return false;
    }
    
    /**
     * Get enabled consumers for type
     * @param string object type
     * @return ilLTIToolConsumer[]
     */
    public static function getEnabledConsumersForType($a_type)
    {
        /**
         * @var ilDBInterface
         */
        $db = $GLOBALS['DIC']->database();
        
        $query = 'select distinct(id) id from lti_ext_consumer join lti_ext_consumer_otype on id = consumer_id ' .
            'WHERE active = ' . $db->quote(1, 'integer') . ' ' .
            'AND object_type = ' . $db->quote($a_type, 'text');
        $res = $db->query($query);
        
        $connector = new ilLTIDataConnector();
        $consumers = array();
        while ($row = $res->fetchObject()) {
            $consumers[] = ilLTIToolConsumer::fromExternalConsumerId($row->id, $connector);
        }
        return $consumers;
    }
    
    /**
     * Lookup ref_id
     */
    public static function lookupLTISettingsRefId()
    {
        $res = $GLOBALS['DIC']->database()->queryF(
            '
			SELECT object_reference.ref_id FROM object_reference, tree, object_data
			WHERE tree.parent = %s
			AND object_data.type = %s
			AND object_reference.ref_id = tree.child
			AND object_reference.obj_id = object_data.obj_id',
            array('integer', 'text'),
            array(SYSTEM_FOLDER_ID, 'ltis')
        );
        while ($row = $GLOBALS['DIC']->database()->fetchAssoc($res)) {
            $lti_ref_id = $row['ref_id'];
        }
        return $lti_ref_id;
    }
    
    /**
     * Read released objects
     */
    public static function readReleaseObjects()
    {
        $db = $GLOBALS['DIC']->database();
        
        $query = 'select ref_id, title from lti2_consumer join lti_ext_consumer ' .
            'on id = ext_consumer_id where enabled = ' . $db->quote(1, 'integer');
        $res = $db->query($query);
        
        ilLoggerFactory::getLogger('lti')->debug($query);
        
        $rows = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $item['ref_id'] = $row->ref_id;
            $item['title'] = $row->title;
            
            $rows[] = $item;
        }
        return $rows;
    }
}
