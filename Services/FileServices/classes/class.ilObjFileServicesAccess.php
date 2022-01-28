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

use ILIAS\HTTP\Services;

/**
 * Class ilObjFileServicesAccess
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 */
class ilObjFileServicesAccess extends ilObjectAccess
{
    private Services $http;
    private \ilRbacSystem $rbacsystem;

    /**
     * ilObjFileServicesAccess constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->rbacsystem = $DIC->rbac()->system();
        $this->http = $DIC->http();
    }

    public function checkAccessAndThrowException(string $permission) : void
    {
        if (!$this->hasUserPermissionTo($permission)) {
            throw new ilException('Permission denied');
        }
    }


    public function hasUserPermissionTo(string $permission) : bool
    {
        return $this->rbacsystem->checkAccess($permission, $this->http->request()->getQueryParams()['ref_id']);
    }
}
