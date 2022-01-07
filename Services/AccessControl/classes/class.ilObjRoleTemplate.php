<?php declare(strict_types=1);
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
 * Class ilObjRoleTemplate
 * @author     Stefan Meyer <meyer@leifos.com>
 * @ingroup    ServicesAccessControl
 */
class ilObjRoleTemplate extends ilObject
{
    public function __construct($a_id = 0, $a_call_by_reference = false)
    {
        $this->type = "rolt";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function delete()
    {
        // put here role template specific stuff
        // delete rbac permissions
        $this->rbacadmin->deleteTemplate($this->getId());

        // always call parent delete function at the end!!
        return parent::delete();
    }

    public function isInternalTemplate() : bool
    {
        if (substr($this->getTitle(), 0, 3) == "il_") {
            return true;
        }

        return false;
    }

    public function getFilterOfInternalTemplate()
    {
        $filter = array();
        switch ($this->getTitle()) {
            case "il_grp_admin":
            case "il_grp_member":
            case "il_grp_status_closed":
            case "il_grp_status_open":
                $obj_data = $this->objDefinition->getSubObjects('grp', false);
                unset($obj_data["rolf"]);
                $filter = array_keys($obj_data);
                $filter[] = 'grp';
                break;

            case "il_crs_admin":
            case "il_crs_tutor":
            case "il_crs_member":
            case "il_crs_non_member":
                $obj_data = $this->objDefinition->getSubObjects('crs', false);
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
