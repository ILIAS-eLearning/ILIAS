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
 * Class ilObjRoleFolder
 * @author     Stefan Meyer <meyer@leifos.com>
 * @ingroup    ServicesAccessControl
 */
class ilObjRoleFolder extends ilObject
{
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "rolf";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function read()
    {
        parent::read();

        if ($this->getId() != ROLE_FOLDER_ID) {
            $this->setDescription($this->lng->txt("obj_" . $this->getType() . "_local_desc") . $this->getTitle() . $this->getDescription());
            $this->setTitle($this->lng->txt("obj_" . $this->getType() . "_local"));
        }
    }

    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        // put here rolefolder specific stuff
        $roles = $this->rbacreview->getRolesOfRoleFolder($this->getRefId());
        foreach ($roles as $role_id) {
            $roleObj = ilObjectFactory::getInstanceByObjId($role_id);
            $roleObj->setParent($this->getRefId());
            $roleObj->delete();
            unset($roleObj);
        }
        return true;
    }

    /**
     * creates a local role in current rolefolder (this object)
     */
    public function createRole(string $a_title, string $a_desc, int $a_import_id = 0) : ilObjRole
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];

        $roleObj = new ilObjRole();
        $roleObj->setTitle($a_title);
        $roleObj->setDescription($a_desc);
        //echo "aaa-1-";
        if ($a_import_id != "") {
            //echo "aaa-2-".$a_import_id."-";
            $roleObj->setImportId((string) $a_import_id);
        }
        $roleObj->create();

        // ...and put the role into local role folder...
        $rbacadmin->assignRoleToFolder($roleObj->getId(), $this->getRefId(), "y");

        return $roleObj;
    }

}
