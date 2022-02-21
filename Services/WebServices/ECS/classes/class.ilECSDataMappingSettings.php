<?php declare(strict_types = 1);

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
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSDataMappingSettings
{
    private static ?array $instances = null;

    private ilECSSetting $settings;
    private array $mappings;
    
    private ilDbInterface $db;
    /**
     * Singleton Constructor
     *
     * @access private
     *
     */
    private function __construct($a_server_id)
    {
        global $DIC;
        $this->db = $DIC->database();

        $this->settings = ilECSSetting::getInstanceByServerId($a_server_id);
        $this->read();
    }

    /**
     * Get singleton instance
     * @param int $a_server_id
     * @return ilECSDataMappingSettings
     */
    public static function getInstanceByServerId($a_server_id) : ilECSDataMappingSettings
    {
        if (isset(self::$instances[$a_server_id])) {
            return self::$instances[$a_server_id];
        }
        return self::$instances[$a_server_id] = new ilECSDataMappingSettings($a_server_id);
    }

    /**
     * Delete server
     * @global ilDB $ilDB
     * @param int $a_server_id
     */
    public function delete() : void
    {
        $server_id = $this->settings->getServerId();
        unset(self::$instances[$server_id]);

        $query = 'DELETE from ecs_data_mapping ' .
            'WHERE sid = ' . $this->db->quote($server_id, 'integer');
        $this->db->manipulate($query);
    }

    /**
     * Get actice ecs setting
     * @return ilECSSetting
     */
    public function getServer() : ilECSSetting
    {
        return $this->settings;
    }


    /**
     * get mappings
     *
     */
    public function getMappings($a_mapping_type = ilECSDataMappingSetting::MAPPING_IMPORT_RCRS) : array
    {
        return $this->mappings[$a_mapping_type];
    }
    
    
    /**
     * get mapping by key
     *
     * @param int mapping type import, export, crs, rcrs
     * @param string ECS data field name. E.g. 'lecturer'
     * @return int AdvancedMetaData field id or 0 (no mapping)
     *
     */
    public function getMappingByECSName(int $a_mapping_type, string $a_key) : int
    {
        return isset($this->mappings[$a_mapping_type][$a_key]) ?
            $this->mappings[$a_mapping_type][$a_key] :
            0;
    }

    

    /**
     * Read settings
     *
     */
    private function read() : void
    {
        $this->mappings = array();

        $query = 'SELECT * FROM ecs_data_mapping ' .
            'WHERE sid = ' . $this->db->quote($this->getServer()->getServerId(), 'integer') . ' ';
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->mappings[$row->mapping_type][$row->ecs_field] = $row->advmd_id;
        }
    }
}
