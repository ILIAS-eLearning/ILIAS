<?php

namespace ILIAS\Modules\OrgUnit\ARHelper;

use ILIAS\HTTP\RawHTTPServices;

/**
 * Class DIC
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait DIC
{
    /**
     * @return \ILIAS\DI\Container
     */
    public function dic()
    {
        global $DIC;
        return $DIC;
    }

    protected function ctrl():  \ilCtrl
    {
        return $this->dic()->ctrl();
    }

    public function txt(string $variable): string
    {
        return $this->lng()->txt($variable);
    }


    protected function tpl(): \ilGlobalTemplateInterface
    {
        return $this->dic()->ui()->mainTemplate();
    }

    protected function lng(): \ilLanguage
    {
        return $this->dic()->language();
    }

    protected function tabs(): \ilTabsGUI
    {
        return $this->dic()->tabs();
    }

    protected function ui(): \ILIAS\DI\UIServices
    {
        return $this->dic()->ui();
    }

    protected function user(): \ilObjUser
    {
        return $this->dic()->user();
    }

    protected function http(): \ILIAS\HTTP\Services
    {
        return $this->dic()->http();
    }

    protected function access(): \ilAccessHandler
    {
        return $this->dic()->access();
    }

    protected function toolbar(): \ilToolbarGUI
    {
        return $this->dic()->toolbar();
    }

    protected function database(): \ilDBInterface
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
     * @return bool
     */
    public function checkPermissionBoolAndReturn($a_perm)
    {
        return (bool) $this->access()->checkAccess($a_perm, '', $this->http()->request()->getQueryParams()['ref_id']);
    }
}