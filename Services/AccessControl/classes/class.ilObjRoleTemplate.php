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

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjRoleTemplate
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup	ServicesAccessControl
*/
class ilObjRoleTemplate extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = false)
    {
        $this->type = "rolt";
        parent::__construct($a_id, $a_call_by_reference);
    }


    /**
    * delete role template and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // put here role template specific stuff
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];

        // delete rbac permissions
        $rbacadmin->deleteTemplate($this->getId(), $_GET["ref_id"]);

        // always call parent delete function at the end!!
        return (parent::delete()) ? true : false;
    }

    public function isInternalTemplate()
    {
        if (substr($this->getTitle(), 0, 3) == "il_") {
            return true;
        }
        
        return false;
    }
    
    public function getFilterOfInternalTemplate()
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        
        $filter = array();

        switch ($this->getTitle()) {
            case "il_grp_admin":
            case "il_grp_member":
            case "il_grp_status_closed":
            case "il_grp_status_open":
                $obj_data = $objDefinition->getSubObjects('grp', false);
                unset($obj_data["rolf"]);
                $filter = array_keys($obj_data);
                $filter[] = 'grp';
                break;
                
            case "il_crs_admin":
            case "il_crs_tutor":
            case "il_crs_member":
            case "il_crs_non_member":
                $obj_data = $objDefinition->getSubObjects('crs', false);
                unset($obj_data["rolf"]);
                $filter = array_keys($obj_data);
                $filter[] = 'crs';
                break;
            case "il_frm_moderator":
                $filter[] = 'frm';
                break;
            case "il_chat_moderator":
                $filter[] = 'chtr';
                break;
        }
        
        return $filter;
    }
} // END class.ilObjRoleTemplate
