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
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSParticipantSettings
{
    private static ?array $instances = null;

    private array $export = array();
    private array $import = array();

    private int $server_id;

    private ilDBInterface $db;

    /**
     * Constructor (Singleton)
     */
    private function __construct(int $a_server_id)
    {
        global $DIC;

        $this->db = $DIC['ilDB'];
        $this->server_id = $a_server_id;
        $this->read();
    }

    /**
     * Get instance by server id
     */
    public static function getInstanceByServerId(int $a_server_id) : ilECSParticipantSettings
    {
        return self::$instances[$a_server_id] ?? (self::$instances[$a_server_id] = new ilECSParticipantSettings($a_server_id));
    }
    
    /**
     * Get all available mids
     * @return int[] membership id
     */
    public function getAvailabeMids() : array
    {
        $query = 'SELECT mid FROM ecs_part_settings ' .
            'WHERE sid = ' . $this->db->quote($this->server_id, 'integer');
        $res = $this->db->query($query);
        
        $mids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $mids[] = (int) $row->mid;
        }
        return $mids;
    }


    
    /**
     * Lookup mid of current cms participant
     */
    public function lookupCmsMid() : int
    {
        $query = 'SELECT mid FROM ecs_part_settings ' .
                'WHERE sid = ' . $this->db->quote($this->server_id, 'integer') . ' ' .
                'AND import_type = ' . $this->db->quote(ilECSParticipantSetting::IMPORT_CMS);
        $res = $this->db->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->mid;
        }
        return 0;
    }

    /**
     * Get server id
     */
    public function getServerId() : int
    {
        return $this->server_id;
    }

    /**
     * Read stored entry
     */
    private function read() : void
    {
        $query = 'SELECT * FROM ecs_part_settings ' .
            'WHERE sid = ' . $this->db->quote($this->getServerId(), 'integer') . ' ';
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->export[$row->mid] = $row->export;
            $this->import[$row->mid] = $row->import;
        }
    }

    /**
     * Check if import is allowed for specific mid
     */
    public function isImportAllowed(array $a_mids) : bool
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
     * @deprecated
     */
    public function getEnabledParticipants() : array
    {
        $ret = array();
        foreach ($this->export as $mid => $enabled) {
            if ($enabled) {
                $ret[] = $mid;
            }
        }
        return $ret;
    }
    
    /**
     * is participant enabled
     *
     * @param int mid
     * @deprecated
     *
     */
    public function isEnabled($a_mid) : bool
    {
        return $this->export[$a_mid] ? true : false;
    }
}
