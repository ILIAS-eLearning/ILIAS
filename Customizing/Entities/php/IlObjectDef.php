<?php



/**
 * IlObjectDef
 */
class IlObjectDef
{
    /**
     * @var string
     */
    private $id = '';

    /**
     * @var string|null
     */
    private $className;

    /**
     * @var string|null
     */
    private $component;

    /**
     * @var string|null
     */
    private $location;

    /**
     * @var bool
     */
    private $checkbox = '0';

    /**
     * @var bool
     */
    private $inherit = '0';

    /**
     * @var string|null
     */
    private $translate;

    /**
     * @var bool
     */
    private $devmode = '0';

    /**
     * @var bool
     */
    private $allowLink = '0';

    /**
     * @var bool
     */
    private $allowCopy = '0';

    /**
     * @var bool
     */
    private $rbac = '0';

    /**
     * @var bool
     */
    private $system = '0';

    /**
     * @var bool
     */
    private $sideblock = '0';

    /**
     * @var int
     */
    private $defaultPos = '0';

    /**
     * @var string|null
     */
    private $grp;

    /**
     * @var int
     */
    private $defaultPresPos = '0';

    /**
     * @var bool
     */
    private $export = '0';

    /**
     * @var bool
     */
    private $repository = '1';

    /**
     * @var bool
     */
    private $workspace = '0';

    /**
     * @var bool
     */
    private $administration = '0';

    /**
     * @var bool
     */
    private $amet = '0';

    /**
     * @var bool
     */
    private $orgunitPermissions = '0';

    /**
     * @var bool
     */
    private $ltiProvider = '0';

    /**
     * @var bool
     */
    private $offlineHandling = '0';


    /**
     * Get id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set className.
     *
     * @param string|null $className
     *
     * @return IlObjectDef
     */
    public function setClassName($className = null)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Get className.
     *
     * @return string|null
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Set component.
     *
     * @param string|null $component
     *
     * @return IlObjectDef
     */
    public function setComponent($component = null)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Get component.
     *
     * @return string|null
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Set location.
     *
     * @param string|null $location
     *
     * @return IlObjectDef
     */
    public function setLocation($location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set checkbox.
     *
     * @param bool $checkbox
     *
     * @return IlObjectDef
     */
    public function setCheckbox($checkbox)
    {
        $this->checkbox = $checkbox;

        return $this;
    }

    /**
     * Get checkbox.
     *
     * @return bool
     */
    public function getCheckbox()
    {
        return $this->checkbox;
    }

    /**
     * Set inherit.
     *
     * @param bool $inherit
     *
     * @return IlObjectDef
     */
    public function setInherit($inherit)
    {
        $this->inherit = $inherit;

        return $this;
    }

    /**
     * Get inherit.
     *
     * @return bool
     */
    public function getInherit()
    {
        return $this->inherit;
    }

    /**
     * Set translate.
     *
     * @param string|null $translate
     *
     * @return IlObjectDef
     */
    public function setTranslate($translate = null)
    {
        $this->translate = $translate;

        return $this;
    }

    /**
     * Get translate.
     *
     * @return string|null
     */
    public function getTranslate()
    {
        return $this->translate;
    }

    /**
     * Set devmode.
     *
     * @param bool $devmode
     *
     * @return IlObjectDef
     */
    public function setDevmode($devmode)
    {
        $this->devmode = $devmode;

        return $this;
    }

    /**
     * Get devmode.
     *
     * @return bool
     */
    public function getDevmode()
    {
        return $this->devmode;
    }

    /**
     * Set allowLink.
     *
     * @param bool $allowLink
     *
     * @return IlObjectDef
     */
    public function setAllowLink($allowLink)
    {
        $this->allowLink = $allowLink;

        return $this;
    }

    /**
     * Get allowLink.
     *
     * @return bool
     */
    public function getAllowLink()
    {
        return $this->allowLink;
    }

    /**
     * Set allowCopy.
     *
     * @param bool $allowCopy
     *
     * @return IlObjectDef
     */
    public function setAllowCopy($allowCopy)
    {
        $this->allowCopy = $allowCopy;

        return $this;
    }

    /**
     * Get allowCopy.
     *
     * @return bool
     */
    public function getAllowCopy()
    {
        return $this->allowCopy;
    }

    /**
     * Set rbac.
     *
     * @param bool $rbac
     *
     * @return IlObjectDef
     */
    public function setRbac($rbac)
    {
        $this->rbac = $rbac;

        return $this;
    }

    /**
     * Get rbac.
     *
     * @return bool
     */
    public function getRbac()
    {
        return $this->rbac;
    }

    /**
     * Set system.
     *
     * @param bool $system
     *
     * @return IlObjectDef
     */
    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * Get system.
     *
     * @return bool
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Set sideblock.
     *
     * @param bool $sideblock
     *
     * @return IlObjectDef
     */
    public function setSideblock($sideblock)
    {
        $this->sideblock = $sideblock;

        return $this;
    }

    /**
     * Get sideblock.
     *
     * @return bool
     */
    public function getSideblock()
    {
        return $this->sideblock;
    }

    /**
     * Set defaultPos.
     *
     * @param int $defaultPos
     *
     * @return IlObjectDef
     */
    public function setDefaultPos($defaultPos)
    {
        $this->defaultPos = $defaultPos;

        return $this;
    }

    /**
     * Get defaultPos.
     *
     * @return int
     */
    public function getDefaultPos()
    {
        return $this->defaultPos;
    }

    /**
     * Set grp.
     *
     * @param string|null $grp
     *
     * @return IlObjectDef
     */
    public function setGrp($grp = null)
    {
        $this->grp = $grp;

        return $this;
    }

    /**
     * Get grp.
     *
     * @return string|null
     */
    public function getGrp()
    {
        return $this->grp;
    }

    /**
     * Set defaultPresPos.
     *
     * @param int $defaultPresPos
     *
     * @return IlObjectDef
     */
    public function setDefaultPresPos($defaultPresPos)
    {
        $this->defaultPresPos = $defaultPresPos;

        return $this;
    }

    /**
     * Get defaultPresPos.
     *
     * @return int
     */
    public function getDefaultPresPos()
    {
        return $this->defaultPresPos;
    }

    /**
     * Set export.
     *
     * @param bool $export
     *
     * @return IlObjectDef
     */
    public function setExport($export)
    {
        $this->export = $export;

        return $this;
    }

    /**
     * Get export.
     *
     * @return bool
     */
    public function getExport()
    {
        return $this->export;
    }

    /**
     * Set repository.
     *
     * @param bool $repository
     *
     * @return IlObjectDef
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Get repository.
     *
     * @return bool
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set workspace.
     *
     * @param bool $workspace
     *
     * @return IlObjectDef
     */
    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;

        return $this;
    }

    /**
     * Get workspace.
     *
     * @return bool
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Set administration.
     *
     * @param bool $administration
     *
     * @return IlObjectDef
     */
    public function setAdministration($administration)
    {
        $this->administration = $administration;

        return $this;
    }

    /**
     * Get administration.
     *
     * @return bool
     */
    public function getAdministration()
    {
        return $this->administration;
    }

    /**
     * Set amet.
     *
     * @param bool $amet
     *
     * @return IlObjectDef
     */
    public function setAmet($amet)
    {
        $this->amet = $amet;

        return $this;
    }

    /**
     * Get amet.
     *
     * @return bool
     */
    public function getAmet()
    {
        return $this->amet;
    }

    /**
     * Set orgunitPermissions.
     *
     * @param bool $orgunitPermissions
     *
     * @return IlObjectDef
     */
    public function setOrgunitPermissions($orgunitPermissions)
    {
        $this->orgunitPermissions = $orgunitPermissions;

        return $this;
    }

    /**
     * Get orgunitPermissions.
     *
     * @return bool
     */
    public function getOrgunitPermissions()
    {
        return $this->orgunitPermissions;
    }

    /**
     * Set ltiProvider.
     *
     * @param bool $ltiProvider
     *
     * @return IlObjectDef
     */
    public function setLtiProvider($ltiProvider)
    {
        $this->ltiProvider = $ltiProvider;

        return $this;
    }

    /**
     * Get ltiProvider.
     *
     * @return bool
     */
    public function getLtiProvider()
    {
        return $this->ltiProvider;
    }

    /**
     * Set offlineHandling.
     *
     * @param bool $offlineHandling
     *
     * @return IlObjectDef
     */
    public function setOfflineHandling($offlineHandling)
    {
        $this->offlineHandling = $offlineHandling;

        return $this;
    }

    /**
     * Get offlineHandling.
     *
     * @return bool
     */
    public function getOfflineHandling()
    {
        return $this->offlineHandling;
    }
}
