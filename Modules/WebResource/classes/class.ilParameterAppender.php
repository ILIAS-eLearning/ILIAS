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

define("LINKS_USER_ID", 1);
define("LINKS_SESSION_ID", 2);
define("LINKS_LOGIN", 3);
define('LINKS_MATRICULATION', 4);

// Errors
define("LINKS_ERR_NO_NAME", 1);
define("LINKS_ERR_NO_VALUE", 2);
define("LINKS_ERR_NO_NAME_VALUE", 3);

/**
* Class ilParameterAppender
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesWebResource
*/
class ilParameterAppender
{
    public $webr_id = null;
    public $db = null;

    public $err = null;


    /**
    * Constructor
    * @access public
    */
    public function __construct($webr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->webr_id = $webr_id;
        $this->db = $ilDB;
    }
    
    /**
     * Get Parameter ids of link
     * @param int $a_webr_id
     * @param int $a_link_id
     * @return
     */
    public static function getParameterIds($a_webr_id, $a_link_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM webr_params " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer') . " " .
            "AND link_id = " . $ilDB->quote($a_link_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $params[] = $row['param_id'];
        }
        return (array) $params;
    }

    public function getErrorCode()
    {
        return $this->err;
    }

    // SET GET
    public function setObjId($a_obj_id)
    {
        $this->webr_id = $a_obj_id;
    }
    
    public function getObjId()
    {
        return $this->webr_id;
    }

    public function setName($a_name)
    {
        $this->name = $a_name;
    }
    public function getName()
    {
        return $this->name;
    }
    public function setValue($a_value)
    {
        $this->value = $a_value;
    }
    public function getValue()
    {
        return $this->value;
    }

    public function validate()
    {
        if (!strlen($this->getName()) and !$this->getValue()) {
            $this->err = LINKS_ERR_NO_NAME_VALUE;
            return false;
        }
        if (!strlen($this->getName())) {
            $this->err = LINKS_ERR_NO_NAME;
            return false;
        }
        if (!$this->getValue()) {
            $this->err = LINKS_ERR_NO_VALUE;
            return false;
        }
        return true;
    }

    
    public function add($a_link_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$a_link_id) {
            return false;
        }
        if (!strlen($this->getName() or !strlen($this->getValue()))) {
            return false;
        }

        $next_id = $ilDB->nextId('webr_params');
        $query = "INSERT INTO webr_params (param_id,webr_id,link_id,name,value) " .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($this->getObjId(), 'integer') . ", " .
            $ilDB->quote($a_link_id, 'integer') . ", " .
            $ilDB->quote($this->getName(), 'text') . ", " .
            $ilDB->quote($this->getValue(), 'integer') .
            ")";
        $res = $ilDB->manipulate($query);

        return $next_id;
    }
    
    public function delete($a_param_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM webr_params " .
            "WHERE param_id = " . $ilDB->quote($a_param_id, 'integer') . " " .
            "AND webr_id = " . $ilDB->quote($this->getObjId(), 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }
    
    /**
     * Check if dynamic parameters are enabled
     * @return
     */
    public static function _isEnabled()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        return $ilSetting->get('links_dynamic', false) ? true : false;
    }

    public static function _append($a_link_data)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if (!is_array($a_link_data)) {
            return false;
        }
        if (count($params = ilParameterAppender::_getParams($a_link_data['link_id']))) {
            // Check for prefix
            foreach ($params as $param_data) {
                if (!strpos($a_link_data['target'], '?')) {
                    $a_link_data['target'] .= "?";
                } else {
                    $a_link_data['target'] .= "&";
                }
                $a_link_data['target'] .= ($param_data['name'] . "=");
                switch ($param_data['value']) {
                    case LINKS_LOGIN:
                        $a_link_data['target'] .= (urlencode($ilUser->getLogin()));
                        break;
                        
                    case LINKS_SESSION_ID:
                        $a_link_data['target'] .= (session_id());
                        break;
                        
                    case LINKS_USER_ID:
                        $a_link_data['target'] .= ($ilUser->getId());
                        break;
                        
                    case LINKS_MATRICULATION:
                        $a_link_data['target'] .= ($ilUser->getMatriculation());
                        break;
                }
            }
        }
        return $a_link_data;
    }
        
    /**
     * Get dynamic parameter definitions
     * @param int $a_link_id
     * @return
     */
    public static function _getParams($a_link_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $params = [];

        $res = $ilDB->query("SELECT * FROM webr_params WHERE link_id = " .
            $ilDB->quote((int) $a_link_id, 'integer'));
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $params[$row->param_id]['name'] = $row->name;
            $params[$row->param_id]['value'] = $row->value;
        }

        return $params;
    }
    
    /**
     * Get info text describing an existing dynamic link
     * @param string $a_name
     * @param int $a_value
     * @return
     */
    public static function parameterToInfo($a_name, $a_value)
    {
        $info = $a_name;
        
        switch ($a_value) {
            case LINKS_USER_ID:
                return $info . '=USER_ID';
                
            case LINKS_SESSION_ID:
                return $info . '=SESSION_ID';
                
            case LINKS_LOGIN:
                return $info . '=LOGIN';
                
            case LINKS_MATRICULATION:
                return $info . '=MATRICULATION';
        }
        return '';
    }

    public static function _deleteAll($a_webr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM webr_params WHERE webr_id = " .
            $ilDB->quote((int) $a_webr_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
     * Get options as array
     * @return
     */
    public static function _getOptionSelect()
    {
        global $DIC;

        $lng = $DIC['lng'];

        return array(0 => $lng->txt('links_select_one'),
                     LINKS_USER_ID => $lng->txt('links_user_id'),
                     LINKS_LOGIN => $lng->txt('links_user_name'),
                     LINKS_SESSION_ID => $lng->txt('links_session_id'),
                     LINKS_MATRICULATION => $lng->txt('matriculation')
        );
    }
}
