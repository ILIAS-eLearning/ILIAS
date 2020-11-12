<?php

/**
 * Class ilObjWebDAVAccess
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 */
class ilObjWebDAVAccess extends ilObjectAccess
{
    /**
     * @var \ILIAS\DI\HTTPServices
     */
    private $http;
    /**
     * @var ilRbacSystem
     */
    private $rbacsystem;

    /**
     * ilObjWebDAVAccess constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->rbacsystem = $DIC->rbac()->system();
        $this->http       = $DIC->http();
    }

    /**
     * @param string $permission
     * @throws ilException
     */
    public function checkAccessAndThrowException(string $permission) : void
    {
        if (!$this->hasUserPermissionTo($permission)) {
            throw new ilException('Permission denied');
        }
    }

    /**
     * @param string $permission
     * @return bool
     */
    public function hasUserPermissionTo(string $permission) : bool
    {
        return (bool) $this->rbacsystem->checkAccess($permission, $this->http->request()->getQueryParams()['ref_id']);
    }

}