<?php

namespace ILIAS\Modules\OrgUnit\ARHelper;

use ILIAS\DI\Container;

/**
 * Interface BaseCommands
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class BaseCommands
{
    const CMD_INDEX = "index";
    const CMD_ADD = "add";
    const CMD_CREATE = "create";
    const CMD_EDIT = "edit";
    const CMD_UPDATE = "update";
    const CMD_CONFIRM = "confirm";
    const CMD_CONFIRM_RECURSIVE = "confirmRecursive";
    const CMD_DELETE = "delete";
    const CMD_DELETE_RECURSIVE = "deleteRecursive";
    const CMD_CANCEL = "cancel";
    const AR_ID = "arid";

    private \ilLanguage $lng;
    private \ilCtrl $ctrl;
    private \ilTabsGUI $tabsGUI;
    private \ilAccess $access;
    private \ILIAS\HTTP\Services $http;
    private \ilGlobalTemplateInterface $tpl;

    protected ?BaseCommands $parent_gui = null;

    private function __construct() {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("orgu");
        $this->ctrl = $DIC->ctrl();
        $this->tabsGUI = $DIC->tabs();
        $this->access = $DIC->access();
        $this->http = $DIC->http();
        $this->tpl = $DIC->ui()->mainTemplate();
    }

    public function getParentGui() : ?BaseCommands
    {
        return $this->parent_gui;
    }

    public function setParentGui(BaseCommands $parent_gui)
    {
        $this->parent_gui = $parent_gui;
    }

    abstract protected function index() : void;

    protected function getPossibleNextClasses() : array
    {
        return array();
    }

    protected function getActiveTabId() : ?string
    {
        return null;
    }

    /**
     * @throws \ilCtrlException
     */
    protected function cancel() : void
    {
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function setContent(string $html)
    {
        $this->tpl->setContent($html);
    }

    /**
     * @throws \ilCtrlException
     */
    public function executeCommand()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->access = $DIC->access();
        $this->tabsGUI = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng->loadLanguageModule("orgu");

        $cmd = $this->ctrl->getCmd(self::CMD_INDEX);
        $next_class = $this->ctrl->getNextClass();
        if ($next_class) {
            foreach ($this->getPossibleNextClasses() as $class) {
                if (strtolower($class) === $next_class) {
                    $instance = new $class();
                    if ($instance instanceof BaseCommands) {
                        $instance->setParentGui($this);
                        $this->ctrl->forwardCommand($instance);
                    }

                    return;
                }
            }
        }

        if ($this->getActiveTabId()) {
            $this->tabsGUI->activateTab($this->getActiveTabId());
        }

        switch ($cmd) {
            default:
                if ($this->checkRequestReferenceId()) {
                    $this->{$cmd}();
                }
                break;
        }
    }

    protected function pushSubTab(string $subtab_id, string $url)
    {
        $this->tabsGUI->addSubTab($subtab_id, $this->lng->txt($subtab_id), $url);
    }

    protected function activeSubTab(string $subtab_id)
    {
        $this->tabsGUI->activateSubTab($subtab_id);
    }

    protected function checkRequestReferenceId()
    {
        /**
         * @var $ilAccess \ilAccessHandler
         */
        $ref_id = $this->getParentRefId();
        if ($ref_id) {
            return $this->access->checkAccess("read", "", $ref_id);
        }

        return true;
    }

    protected function getParentRefId() : ?int
    {
        $ref_id = $this->http->request()->getQueryParams()["ref_id"];

        return $ref_id;
    }

    public function addSubTabs() : void
    {
    }
}
