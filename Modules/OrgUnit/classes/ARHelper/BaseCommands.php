<?php

namespace ILIAS\Modules\OrgUnit\ARHelper;

/**
 * Interface BaseCommands
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class BaseCommands
{
    use DIC;
    const CMD_INDEX = "index";
    const CMD_ADD = "add";
    const CMD_CREATE = "create";
    const CMD_EDIT = "edit";
    const CMD_UPDATE = "update";
    const CMD_CONFIRM = "confirm";
    const CMD_DELETE = "delete";
    const CMD_CANCEL = "cancel";
    const AR_ID = "arid";
    /**
     * @var \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands
     */
    protected $parent_gui = null;


    /**
     * @return \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands
     */
    public function getParentGui()
    {
        return $this->parent_gui;
    }


    /**
     * @param \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands $parent_gui
     */
    public function setParentGui($parent_gui)
    {
        $this->parent_gui = $parent_gui;
    }


    abstract protected function index();


    /**
     * @return array of GUI_Class-Names
     */
    protected function getPossibleNextClasses()
    {
        return array();
    }


    /**
     * @return null|string of active Tab
     */
    protected function getActiveTabId()
    {
        return null;
    }


    protected function cancel()
    {
        $this->ctrl()->redirect($this, self::CMD_INDEX);
    }


    /***
     * @param $html
     */
    protected function setContent($html)
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


    /**
     * @param $subtab_id
     * @param $url
     */
    protected function pushSubTab($subtab_id, $url)
    {
        $this->dic()->tabs()->addSubTab($subtab_id, $this->txt($subtab_id), $url);
    }


    /**
     * @param $subtab_id
     */
    protected function activeSubTab($subtab_id)
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


    /**
     * @return int|null
     */
    protected function getParentRefId()
    {
        $http = $this->dic()->http();
        $ref_id = $http->request()->getQueryParams()["ref_id"];

        return $ref_id;
    }


    public function addSubTabs()
    {
    }
}
