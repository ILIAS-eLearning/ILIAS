<?php

namespace ILIAS\Modules\OrgUnit\ARHelper;

/**
 * Interface BaseCommands
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class BaseCommands
{
    use DIC;

    public const CMD_INDEX = "index";
    public const CMD_ADD = "add";
    public const CMD_CREATE = "create";
    public const CMD_EDIT = "edit";
    public const CMD_UPDATE = "update";
    public const CMD_CONFIRM = "confirm";
    public const CMD_DELETE = "delete";
    public const CMD_CANCEL = "cancel";
    public const AR_ID = "arid";
    protected ?BaseCommands $parent_gui = null;

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
        $this->ctrl()->redirect($this, self::CMD_INDEX);
    }

    protected function setContent(string $html)
    {
        $this->tpl()->setContent($html);
    }

    public function executeCommand()
    {
        $this->dic()->language()->loadLanguageModule("orgu");
        $cmd = $this->dic()->ctrl()->getCmd(self::CMD_INDEX);
        $next_class = $this->dic()->ctrl()->getNextClass();
        if ($next_class) {
            foreach ($this->getPossibleNextClasses() as $class) {
                if (strtolower($class) === $next_class) {
                    $instance = new $class();
                    if ($instance instanceof BaseCommands) {
                        $instance->setParentGui($this);
                        $this->ctrl()->forwardCommand($instance);
                    }

                    return;
                }
            }
        }

        if ($this->getActiveTabId()) {
            $this->dic()->tabs()->activateTab($this->getActiveTabId());
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
        $this->dic()->tabs()->addSubTab($subtab_id, $this->txt($subtab_id), $url);
    }

    protected function activeSubTab(string $subtab_id)
    {
        $this->dic()->tabs()->activateSubTab($subtab_id);
    }

    protected function checkRequestReferenceId()
    {
        /**
         * @var $ilAccess \ilAccessHandler
         */
        $ref_id = $this->getParentRefId();
        if ($ref_id) {
            return $this->dic()->access()->checkAccess("read", "", $ref_id);
        }

        return true;
    }

    protected function getParentRefId() : ?int
    {
        $http = $this->dic()->http();
        $ref_id = $http->request()->getQueryParams()["ref_id"];

        return $ref_id;
    }

    public function addSubTabs() : void
    {
    }
}
