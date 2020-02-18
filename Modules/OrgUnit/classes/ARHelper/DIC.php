<?php

namespace ILIAS\Modules\OrgUnit\ARHelper;

/**
 * Class DIC
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait DIC
{

    /**
     * @return \ILIAS\DI\Container
     */
    public function dic()
    {
        return $GLOBALS['DIC'];
    }


    /**
     * @return \ilCtrl
     */
    protected function ctrl()
    {
        return $this->dic()->ctrl();
    }


    /**
     * @param $variable
     *
     * @return string
     */
    public function txt($variable)
    {
        return $this->lng()->txt($variable);
    }


    /**
     * @return \ilTemplate
     */
    protected function tpl()
    {
        return $this->dic()->ui()->mainTemplate();
    }


    /**
     * @return \ilLanguage
     */
    protected function lng()
    {
        return $this->dic()->language();
    }


    /**
     * @return \ilTabsGUI
     */
    protected function tabs()
    {
        return $this->dic()->tabs();
    }


    /**
     * @return \ILIAS\DI\UIServices
     */
    protected function ui()
    {
        return $this->dic()->ui();
    }


    /**
     * @return \ilObjUser
     */
    protected function user()
    {
        return $this->dic()->user();
    }


    /**
     * @return \ILIAS\DI\HTTPServices
     */
    protected function http()
    {
        return $this->dic()->http();
    }


    /**
     * @return \ilAccessHandler
     */
    protected function access()
    {
        return $this->dic()->access();
    }


    /**
     * @return \ilToolbarGUI
     */
    protected function toolbar()
    {
        return $this->dic()->toolbar();
    }


    /**
     * @return \ilDB
     */
    protected function database()
    {
        return $this->dic()->database();
    }

    //
    // Helper
    //
    public function checkPermissionAndFail($a_perm)
    {
        if (!$this->checkPermissionBoolAndReturn($a_perm)) {
            throw new \ilObjectException($this->lng()->txt("permission_denied"));
        }
    }


    /**
     * @param $a_perm
     *
     * @return bool
     */
    public function checkPermissionBoolAndReturn($a_perm)
    {
        return (bool) $this->access()->checkAccess($a_perm, '', $this->http()->request()->getQueryParams()['ref_id']);
    }
}
