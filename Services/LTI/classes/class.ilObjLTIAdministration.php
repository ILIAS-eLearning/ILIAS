<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilObjLTIAdministration
 * @author Jesús López <lopez@leifos.com>
 *
 * @package ServicesLTI
 */
class ilObjLTIAdministration extends ilObject
{
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "ltis";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * @return string[] Array of lti provider supportting object types
     */
    public function getLTIObjectTypes() : array
    {
        $obj_def = new ilObjectDefinition();
        return $obj_def->getLTIProviderTypes();
    }

    /**
     * @return array available roles for LTI
     */
    public function getLTIRoles() : array
    {
        global $rbacreview;

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
     * @param int   $a_consumer_id
     * @param array $a_obj_types
     */
    public function saveConsumerObjectTypes(int $a_consumer_id, array $a_obj_types) : void
    {
        $this->db->manipulate("DELETE FROM lti_ext_consumer_otype WHERE consumer_id = " . $this->db->quote($a_consumer_id, "integer"));

        if ($a_obj_types) {
            $query = "INSERT INTO lti_ext_consumer_otype (consumer_id, object_type) VALUES (%s, %s)";
            $types = array("integer", "text");
            foreach ($a_obj_types as $ot) {
                $values = array($a_consumer_id, $ot);
                $this->db->manipulateF($query, $types, $values);
            }
        }
    }

    /**
     * @return array consumer active objects
     */
    public static function getActiveObjectTypes(int $a_consumer_id) : array
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $result = $ilDB->query("SELECT object_type FROM lti_ext_consumer_otype WHERE consumer_id = " . $ilDB->quote($a_consumer_id, "integer"));

        $obj_ids = array();
        while ($record = $ilDB->fetchAssoc($result)) {
            $obj_ids[] = $record['object_type'];
        }
        return $obj_ids;
    }
    
    /**
     * Check if any consumer is enabled for an object type
     */
    public static function isEnabledForType(string $a_type) : bool
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
     * @return ilLTIPlatform[]
     */
    public static function getEnabledConsumersForType(string $a_type) : array
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
            $consumers[] = ilLTIPlatform::fromExternalConsumerId((int) $row->id, $connector);
        }
        return $consumers;
    }
    
    /**
     * Lookup ref_id
     */
    public static function lookupLTISettingsRefId() : ?int
    {
        $lti_ref_id = null;
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
            $lti_ref_id = (int) $row['ref_id'];
        }
        return $lti_ref_id;
    }
    
    /**
     * Read released objects
     * @return array<int, array<string, mixed>>
     */
    public static function readReleaseObjects() : array
    {
        $db = $GLOBALS['DIC']->database();
        
        $query = 'select ref_id, title from lti2_consumer join lti_ext_consumer ' .
            'on id = ext_consumer_id where enabled = ' . $db->quote(1, 'integer');
        $res = $db->query($query);
        
        ilLoggerFactory::getLogger('ltis')->debug($query);
        
        $rows = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $item['ref_id'] = $row->ref_id;
            $item['title'] = $row->title;
            
            $rows[] = $item;
        }
        return $rows;
    }
}
