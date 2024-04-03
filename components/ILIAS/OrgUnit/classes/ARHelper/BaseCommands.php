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
 ********************************************************************
 */

namespace ILIAS\components\OrgUnit\ARHelper;

use ILIAS\DI\Container;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\URLBuilderToken;

/**
 * Interface BaseCommands
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class BaseCommands
{
    public const CMD_INDEX = "index";
    public const CMD_ADD = "add";
    public const CMD_CREATE = "create";
    public const CMD_EDIT = "edit";
    public const CMD_UPDATE = "update";
    public const CMD_CONFIRM = "confirm";
    public const CMD_CONFIRM_RECURSIVE = "confirmRecursive";
    public const CMD_DELETE = "delete";
    public const CMD_DELETE_RECURSIVE = "deleteRecursive";
    public const CMD_CANCEL = "cancel";
    public const AR_ID = "arid";

    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;
    private \ilTabsGUI $tabsGUI;
    private \ilAccess $access;
    protected \ILIAS\HTTP\Services $http;
    protected \ilGlobalTemplateInterface $tpl;
    protected ?BaseCommands $parent_gui = null;
    protected ?URLBuilderToken $action_token = null;
    protected Refinery $refinery;

    protected function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("orgu");
        $this->ctrl = $DIC->ctrl();
        $this->tabsGUI = $DIC->tabs();
        $this->access = $DIC->access();
        $this->http = $DIC->http();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->refinery = $DIC['refinery'];

    }

    public function getParentGui(): ?BaseCommands
    {
        return $this->parent_gui;
    }

    public function setParentGui(BaseCommands $parent_gui)
    {
        $this->parent_gui = $parent_gui;
    }

    abstract protected function index(): void;

    protected function getPossibleNextClasses(): array
    {
        return array();
    }

    protected function getActiveTabId(): ?string
    {
        return null;
    }

    /**
     * @throws \ilCtrlException
     */
    protected function cancel(): void
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

        if ($this->action_token &&
            $this->query->has($this->action_token->getName())
        ) {
            $cmd = $this->query->retrieve(
                $this->action_token->getName(),
                $this->refinery->to()->string()
            );
        }

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

    protected function getParentRefId(): ?int
    {
        $ref_id = $this->http->request()->getQueryParams()["ref_id"];

        return $ref_id;
    }

    public function addSubTabs(): void
    {
    }
}
