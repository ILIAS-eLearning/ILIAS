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
* Class class.ilRegistrationAccessLimitation
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ingroup ServicesRegistration
*/

define('IL_REG_ACCESS_LIMITATION_MISSING_MODE', 1);
define('IL_REG_ACCESS_LIMITATION_OUT_OF_DATE', 2);

class ilRegistrationRoleAccessLimitations
{
    private $access_limitations = array();
    
    public $access_limits = array();

    public function __construct()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $this->db = $ilDB;
        $this->__read();
    }
    
    // Private
    public function __read()
    {

        $query = "SELECT * FROM reg_access_limit ";
        $res = $this->db->query($query);

        $this->access_limitations = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->access_limitations[$row->role_id]['id'] =  $row->role_id;
            $this->access_limitations[$row->role_id]['absolute'] = $row->limit_absolute;
            $this->access_limitations[$row->role_id]['relative_d'] = $row->limit_relative_d;
            $this->access_limitations[$row->role_id]['relative_m'] = $row->limit_relative_m;
            $this->access_limitations[$row->role_id]['mode'] = $row->limit_mode;
        }
        
        return true;
    }
    
    public function save()
    {

        foreach ($this->access_limitations as $key => $data) {
            $limit_value = "";

            // Delete old entry
            $query = "DELETE FROM reg_access_limit " .
                "WHERE role_id = " . $this->db->quote($key, 'integer');
            $res = $this->db->manipulate($query);
            
            
            $query = "INSERT INTO reg_access_limit (role_id,limit_mode,limit_absolute," .
                "limit_relative_d,limit_relative_m) " .
                "VALUES( " .
                $this->db->quote($key, 'integer') . ", " .
                $this->db->quote($data['mode'], 'text') . ", " .
                $this->db->quote($data['absolute']) . ", " .
                $this->db->quote($data['relative_d']) . ", " .
                $this->db->quote($data['relative_m']) . " " .
                ")";
            $res = $this->db->manipulate($query);
        }
        
        return true;
    }
    
    public function validate()
    {
        foreach ($this->access_limitations as $data) {
            if ($data['mode'] == "null") {
                return IL_REG_ACCESS_LIMITATION_MISSING_MODE;
            }
            
            if ($data['mode'] == 'absolute' and $data['absolute'] < time()) {
                return IL_REG_ACCESS_LIMITATION_OUT_OF_DATE;
            }
            
            if ($data['mode'] == 'relative' and ($data['relative_d'] < 1 and $data['relative_m'] < 1)) {
                return IL_REG_ACCESS_LIMITATION_OUT_OF_DATE;
            }
        }
        
        return 0;
    }
    
    public function getMode($a_role_id)
    {
        return $this->access_limitations[$a_role_id] ? $this->access_limitations[$a_role_id]['mode'] : 'null';
    }
    
    public function setMode($a_mode, $a_role_id)
    {
        $this->access_limitations[$a_role_id]['mode'] = $a_mode;
    }
    
    public function getAbsolute($a_role_id)
    {
        return $this->access_limitations[$a_role_id] ? $this->access_limitations[$a_role_id]['absolute'] : time();
    }
    
    public function setAbsolute($date, $a_role_id)
    {
        $this->access_limitations[$a_role_id]['absolute'] = strtotime($date);
    }
    
    public function getRelative($a_role_id, $a_type)
    {
        return $this->access_limitations[$a_role_id] ? $this->access_limitations[$a_role_id]['relative_' . $a_type] : 0;
    }

    public function setRelative($a_arr, $a_role_id)
    {
        $this->access_limitations[$a_role_id]['relative_d'] = $a_arr['dd'];
        $this->access_limitations[$a_role_id]['relative_m'] = $a_arr['MM'];
    }
    
    /**
     * reset access limitations
     */
    public function resetAccessLimitations()
    {
        $this->access_limitations = array();
    }
}
