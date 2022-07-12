<?php

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
 * Class ilObjAdministrativeNotificationAccess
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjAdministrativeNotificationAccess extends ilObjectAccess
{
    private ilRbacSystem $rbacsystem;
    
    protected ?int $ref_id;
    
    /**
     * ilObjAdministrativeNotificationAccess constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->rbacsystem = $DIC->rbac()->system();
        $this->ref_id = $DIC->http()->wrapper()->query()->has('ref_id')
            ? $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int())
            : null;
    }
    
    /**
     * @throws ilException
     */
    public function checkAccessAndThrowException(string $permission) : void
    {
        if (!$this->hasUserPermissionTo($permission)) {
            throw new ilException('Permission denied');
        }
    }

    public function hasUserPermissionTo(string $permission) : bool
    {
        return $this->rbacsystem->checkAccess($permission, $this->ref_id);
    }
}
