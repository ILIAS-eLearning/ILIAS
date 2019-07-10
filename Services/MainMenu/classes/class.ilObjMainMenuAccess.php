<?php

/**
 * Class ilObjMainMenuAccess
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjMainMenuAccess extends ilObjectAccess
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
     * ilObjMainMenuAccess constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->rbacsystem = $DIC->rbac()->system();
        $this->http = $DIC->http();
    }


    /**
     * @param string $permission
     *
     * @throws ilException
     */
    public function checkAccessAndThrowException(string $permission)
    {
        if (!$this->hasUserPermissionTo($permission)) {
            //throw new ilException('Permission denied');

            echo "KEIN ZUGRIFF!!";
            exit;
        }
    }


    /**
     * @param string $permission
     *
     * @return bool
     */
    public function hasUserPermissionTo(string $permission) : bool
    {
        return (bool) $this->rbacsystem->checkAccess($permission, $this->http->request()->getQueryParams()['ref_id']);
    }
}
