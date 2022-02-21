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
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSParticipantSettings
{
    private static ?array $instances = null;

    private array $export = array();
    private array $import = array();
    private array $export_type = array();
    private int $server_id;

    private ilDBInterface $db;
    
    /**
     * Constructor (Singleton)
     *
     * @access private
     *
     */
    private function __construct($a_server_id)
    {
        global $DIC;

        $this->db = $DIC['ilDB'];
        $this->server_id = $a_server_id;
        $this->read();
    }

    /**
     * Get instance by server id
     * @param int $a_server_id
     * @return ilECSParticipantSettings
     */
    public static function getInstanceByServerId($a_server_id)
    {
        if (isset(self::$instances[$a_server_id])) {
            return self::$instances[$a_server_id];
        }
        return self::$instances[$a_server_id] = new ilECSParticipantSettings($a_server_id);
    }
    
    /**
     * Get all available mids
     * @return type
     */
    public function getAvailabeMids()
    {
        $query = 'SELECT mid FROM ecs_part_settings ' .
            'WHERE sid = ' . $this->db->quote($this->server_id, 'integer');
        $res = $this->db->query($query);
        
        $mids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $mids[] = $row->mid;
        }
        return $mids;
    }


    
    /**
     * Lookup mid of current cms participant
     */
    public function lookupCmsMid()
    {
        $query = 'SELECT mid FROM ecs_part_settings ' .
                'WHERE sid = ' . $this->db->quote($this->server_id, 'integer') . ' ' .
                'AND import_type = ' . $this->db->quote(ilECSParticipantSetting::IMPORT_CMS);
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->mid;
        }
        return 0;
    }

    /**
     * Get server id
     * @return int
     */
    public function getServerId()
    {
        return $this->server_id;
    }

    /**
     * Read stored entry
     * @return <type>
     */
    private function read()
    {
        $query = 'SELECT * FROM ecs_part_settings ' .
            'WHERE sid = ' . $this->db->quote($this->getServerId(), 'integer') . ' ';
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->export[$row->mid] = $row->export;
            $this->import[$row->mid] = $row->import;
            $this->import_type[$row->mid] = $row->import_type;
            $this->export_types[$row->mid] = (array) unserialize($row->export_types);
            $this->import_types[$row->mid] = (array) unserialize($row->import_types);
        }
        return true;
    }

    /**
     * Check if import is allowed for specific mid
     * @param array $a_mids
     * @return <type>
     */
    public function isImportAllowed(array $a_mids)
    {
        foreach ($a_mids as $mid) {
            if ($this->import[$mid]) {
                return true;
            }
        }
        return false;
    }

    /**
     * get number of participants that are enabled
     *
     * @access public
     * @deprecated
     */
    public function getEnabledParticipants()
    {
        $ret = array();
        foreach ($this->export as $mid => $enabled) {
            if ($enabled) {
                $ret[] = $mid;
            }
        }
        return $ret;
        #return $this->enabled ? $this->enabled : array();
    }
    
    /**
     * is participant enabled
     *
     * @access public
     * @param int mid
     * @deprecated
     *
     */
    public function isEnabled($a_mid)
    {
        return $this->export[$a_mid] ? true : false;
    }
    
    /**
     * set enabled participants by community
     *
     * @access public
     * @param int community id
     * @param array participant ids
     */
    public function setEnabledParticipants($a_parts)
    {
        $this->enabled = (array) $a_parts;
    }
}
