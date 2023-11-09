<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilObjRoleFolder
 * @author     Stefan Meyer <meyer@leifos.com>
 * @ingroup    ServicesAccessControl
 */
class ilObjRoleFolder extends ilObject
{
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "rolf";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function read(): void
    {
        parent::read();

        if ($this->getId() != ROLE_FOLDER_ID) {
            $this->setDescription($this->lng->txt("obj_" . $this->getType() . "_local_desc") . $this->getTitle() . $this->getDescription());
            $this->setTitle($this->lng->txt("obj_" . $this->getType() . "_local"));
        }
    }

    public function delete(): bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        // put here rolefolder specific stuff
        $roles = $this->rbac_review->getRolesOfRoleFolder($this->getRefId());
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
    public function createRole(string $a_title, string $a_desc, int $a_import_id = 0): ilObjRole
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];

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
