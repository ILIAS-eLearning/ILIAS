<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */


use ILIAS\DI\Container;

/**
 * Class ilObjWorkflowEngineGUI
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 * @ilCtrl_IsCalledBy ilObjWorkflowEngineGUI: ilAdministrationGUI
 * @ilCtrl_Calls ilObjWorkflowEngineGUI: ilPermissionGUI
 */
class ilObjWorkflowEngineGUI extends ilObjectGUI
{
    public ilCtrl $ilCtrl;
    public ilTabsGUI $ilTabs;
    public ilLanguage $lng;
    public ilGlobalTemplateInterface $tpl;
    public ilTree $tree;
    public ilLocatorGUI $ilLocator;
    public ilToolbarGUI $ilToolbar;
    protected Container $dic;

    public function __construct()
    {
        global $DIC;

        $this->ilTabs = $DIC['ilTabs'];
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('wfe');
        $this->ilCtrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->tree = $DIC['tree'];
        $this->ilLocator = $DIC['ilLocator'];
        $this->ilToolbar = $DIC['ilToolbar'];
        $this->dic = $DIC;
        $this->service = $DIC->workflowEngine();// TODO PHP8-REVIEW Property dynamically declared
        parent::__construct($this->service->internal()->request()->getRefId());
        $this->assignObject();
    }

    public function getType() : string
    {
        return "";
    }

    /**
     * Goto-Method for the workflow engine
     * Handles calls via GOTO, e.g. request
     * http://.../goto.php?target=wfe_WF61235EVT12308154711&client_id=default
     * would end up here with $params = WF61235EVT12308154711
     * It will be unfolded to
     *   Workflow 61235
     *   Event 12308154711
     * Used to trigger an event for the engine.
     * @param string $params Params from $_GET after wfe_
     */
    public static function _goto(string $params) : void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        /** @var ilLanguage $lng */
        $lng = $DIC['lng'];

        $workflow = substr($params, 2, strpos($params, 'EVT') - 2);
        $event = substr($params, strpos($params, 'EVT') + 3);

        $type = 'endpoint_event';
        $content = 'was_requested';
        $subject_type = 'workflow';
        $subject_id = $workflow;
        $context_type = 'event';
        $context_id = $event;

        $engine = new ilWorkflowEngine();
        $engine->processEvent(
            $type,
            $content,
            $subject_type,
            $subject_id,
            $context_type,
            $context_id
        );

        $main_tpl->setOnScreenMessage('success', $lng->txt('ok'), true);
        ilUtil::redirect('ilias.php?baseClass=ilDashboardGUI');
    }

    public function executeCommand() : void
    {
        $next_class = $this->ilCtrl->getNextClass();

        if ($next_class === '' || $next_class === null) {
            $this->prepareAdminOutput();
            $this->tpl->setContent($this->dispatchCommand($this->ilCtrl->getCmd('dashboard.view')));
            return;
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->prepareAdminOutput();
                $this->initTabs('permissions');
                $this->ilTabs->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
        }
    }

    /**
     * @param string $cmd
     * @return string
     */
    public function dispatchCommand(string $cmd) : string
    {
        $cmd_parts = explode('.', $cmd);

        switch ($cmd_parts[0]) {
            case 'definitions':
                return $this->dispatchToDefinitions($cmd_parts[1]);

            case 'instances':
                return $this->dispatchToInstances($cmd_parts[1]);

            case 'settings':
                return $this->dispatchToSettings($cmd_parts[1]);

            case 'dashboard':
                return $this->dispatchToDashboard($cmd_parts[1]);

            default:
                return $this->dispatchToDefinitions($cmd_parts[0]);
        }
    }

    /**
     * @return void
     */
    public function prepareAdminOutput() : void
    {
        $this->tpl->loadStandardTemplate();

        $this->tpl->setTitleIcon(ilUtil::getImagePath('icon_wfe.svg'));
        $this->tpl->setTitle($this->object->getPresentationTitle());
        $this->tpl->setDescription($this->object->getLongDescription());

        $this->initLocator();
    }

    /**
     * @param string $section
     */
    public function initTabs(string $section) : void
    {
        global $DIC;
        /** @var $rbacsystem ilRbacSystem */
        $rbacsystem = $DIC['rbacsystem'];

        /*
        $this->ilTabs->addTab(
                'dashboard',
                $this->lng->txt('dashboard'),
                $this->ilCtrl->getLinkTarget($this, 'dashboard.view')
        );
        */
        if ($rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
            $this->ilTabs->addTab(
                'definitions',
                $this->lng->txt('definitions'),
                $this->ilCtrl->getLinkTarget($this, 'definitions.view')
            );
            $this->ilTabs->addTab(
                'settings',
                $this->lng->txt('settings'),
                $this->ilCtrl->getLinkTarget($this, 'settings.view')
            );
        }
        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->ilTabs->addTab(
                'perm_settings',
                $this->lng->txt('perm_settings'),
                $this->ilCtrl->getLinkTargetByClass(array('ilobjworkflowenginegui','ilpermissiongui'), 'perm')
            );
        }
        /*
        $this->ilTabs->addTab(
                'instances',
                $this->lng->txt('instances'),
                $this->ilCtrl->getLinkTarget($this, 'instances.view')
        );
        */

        $this->ilTabs->setTabActive($section);
    }

    /**
     * @return void
     */
    public function initLocator() : void
    {
        $path = $this->tree->getPathFull($this->service->internal()->request()->getRefId());
        array_shift($path);
        foreach ($path as $key => $row) {
            if ($row["title"] === "Workflow Engine") {
                $row["title"] = $this->lng->txt("obj_wfe");
            }

            $this->ilCtrl->setParameter($this, "ref_id", $row["child"]);
            $this->ilLocator->addItem(
                $row["title"],
                $this->ilCtrl->getLinkTarget($this, "dashboard.view"),
                ilFrameTargetInfo::_getFrame("MainContent"),
                $row["child"]
            );

            $this->ilCtrl->setParameter($this, "ref_id", $this->service->internal()->request()->getRefId());
        }

        $this->tpl->setLocator();
    }

    /**
     * @param string $command
     * @return string
     */
    public function dispatchToDashboard(string $command) : string
    {
        $this->initTabs('dashboard');
        $target_handler = new ilWorkflowEngineDashboardGUI($this);
        return $target_handler->handle($command);
    }

    /**
     * @param string $command
     * @return string
     */
    public function dispatchToDefinitions(string $command) : string
    {
        $this->initTabs('definitions');
        $target_handler = new ilWorkflowEngineDefinitionsGUI($this, $this->dic);
        return $target_handler->handle($command);
    }

    /**
     * @param string $command
     * @return string
     */
    public function dispatchToInstances(string $command) : string
    {
        $this->initTabs('instances');
        $target_handler = new ilWorkflowEngineInstancesGUI($this);
        return $target_handler->handle($command);
    }

    /**
     * @param string $command
     * @return string
     */
    public function dispatchToSettings(string $command) : string
    {
        $this->initTabs('settings');
        $target_handler = new ilWorkflowEngineSettingsGUI($this);
        return $target_handler->handle($command);
    }
}
