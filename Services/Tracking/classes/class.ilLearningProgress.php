<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Class ilLearningProgress
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* @package ilias-core
*/

class ilLearningProgress
{
    public $db = null;

    public function __construct()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->db = $ilDB;
    }

    // Static
    public static function _tracProgress($a_user_id, $a_obj_id, $a_ref_id, $a_obj_type = '')
    {
        require_once('Services/Tracking/classes/class.ilChangeEvent.php');
        ilChangeEvent::_recordReadEvent($a_obj_type, $a_ref_id, $a_obj_id, $a_user_id);
        
        require_once 'Services/Tracking/classes/class.ilLPStatus.php';
        ilLPStatus::setInProgressIfNotAttempted($a_obj_id, $a_user_id);

        return true;
    }

    public static function _getProgress($a_user_id, $a_obj_id)
    {
        require_once 'Services/Tracking/classes/class.ilChangeEvent.php';
        $events = ilChangeEvent::_lookupReadEvents($a_obj_id, $a_user_id);
        
        include_once './Services/Calendar/classes/class.ilDateTime.php';

        foreach ($events as $row) {
            $tmp_date = new ilDateTime($row['last_access'], IL_CAL_UNIX);
            $row['last_access'] = $tmp_date->get(IL_CAL_UNIX);
            
            $tmp_date = new ilDateTime($row['first_access'], IL_CAL_DATETIME);
            $row['first_access'] = $tmp_date->get(IL_CAL_UNIX);
            
            if ($progress) {
                $progress['spent_seconds'] += $row['spent_seconds'];
                $progress['access_time'] = max($progress['access_time'], $row['last_access']);
                $progress['access_time_min'] = min($progress['access_time_min'], $row['first_access']);
            } else {
                $progress['obj_id'] = $row['obj_id'];
                $progress['user_id'] = $row['usr_id'];
                $progress['spent_seconds'] = $row['spent_seconds'];
                $progress['access_time'] = $row['last_access'];
                $progress['access_time_min'] = $row['first_access'];
                $progress['visits'] = $row['read_count'];
            }
        }
        return $progress ? $progress : array();
    }
    
    /**
     * lookup progress for a specific object
     *
     * @access public
     * @param int obj_id
     * @return array of progress data
     */
    public static function _lookupProgressByObjId($a_obj_id)
    {
        include_once('./Services/Tracking/classes/class.ilChangeEvent.php');
        foreach (ilChangeEvent::_lookupReadEvents($a_obj_id) as $row) {
            if (isset($progress[$row['usr_id']])) {
                $progress[$row['usr_id']]['spent_seconds'] += $row['spent_seconds'];
                $progress[$row['usr_id']]['read_count'] += $row['read_count'];
                $progress[$row['usr_id']]['ts'] = max($row['last_access'], $progress[$row['usr_id']]['ts']);
            } else {
                $progress[$row['usr_id']]['spent_seconds'] = $row['spent_seconds'];
                $progress[$row['usr_id']]['read_count'] = $row['read_count'];
                $progress[$row['usr_id']]['ts'] = $row['last_access'];
            }
            $progress[$row['usr_id']]['usr_id'] = $row['usr_id'];
            $progress[$row['usr_id']]['obj_id'] = $row['obj_id'];
        }
        return $progress ? $progress : array();
    }

    /*	deprecated
        function _updateProgress($data)
        {
            require_once 'Services/Tracking/classes/class.ilChangeEvent.php';
            ilChangeEvent::_recordReadEvent($data['type'], $data['ref_id'],
                $data['obj_id'], $data['usr_id']);
        }
     */
}
