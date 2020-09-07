<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/Object/classes/class.ilObject2GUI.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/class.ilWorkflowEngine.php';

/**
 * Class ilObjWorkflowEngineGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 *
 * @ilCtrl_IsCalledBy ilObjWorkflowEngineGUI: ilAdministrationGUI
 * @ilCtrl_Calls ilObjWorkflowEngineGUI: ilPermissionGUI
 */
class ilObjWorkflowEngineGUI extends ilObject2GUI
{
    /** @var ilCtrl $ilCtrl */
    public $ilCtrl;

    /** @var ilTabsGUI $ilTabs */
    public $ilTabs;

    /** @var ilLanguage $lng */
    public $lng;

    /** @var ilTemplate $tpl */
    public $tpl;

    /** @var ilTree $tree */
    public $tree;

    /** @var ilLocatorGUI $ilLocator */
    public $ilLocator;

    /** @var ilToolbarGUI $ilToolbar */
    public $ilToolbar;
    
    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;

    /**
     * ilObjWorkflowEngineGUI constructor.
     */
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

        parent::__construct((int) $_GET['ref_id']);
        $this->assignObject();
    }

    /**
     * @return null
     */
    public function getType()
    {
        return null;
    }

    /**
     * Goto-Method for the workflow engine
     *
     * Handles calls via GOTO, e.g. request
     * http://.../goto.php?target=wfe_WF61235EVT12308154711&client_id=default
     * would end up here with $params = WF61235EVT12308154711
     * It will be unfolded to
     *   Workflow 61235
     *   Event 12308154711
     * Used to trigger an event for the engine.
     *
     * @param string $params Params from $_GET after wfe_
     */
    public static function _goto($params)
    {
        global $DIC;
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

        ilUtil::sendSuccess($lng->txt('ok'), true);
        ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
    }

    public function executeCommand()
    {
        $next_class = $this->ilCtrl->getNextClass();

        if ($next_class == '') {
            $this->prepareAdminOutput();
            $this->tpl->setContent($this->dispatchCommand($this->ilCtrl->getCmd('dashboard.view')));
            return;
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->prepareAdminOutput();
                $this->initTabs('permissions');
                $this->ilTabs->setTabActive('perm_settings');
                require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            default:
                $this->prepareAdminOutput();
                $ilObjBibliographicAdminLibrariesGUI = new ilObjBibliographicAdminLibrariesGUI($this);
                $this->ctrl->forwardCommand($ilObjBibliographicAdminLibrariesGUI);
                break;
        }
    }

    /**
     * @param string $cmd
     *
     * @return string
     */
    public function dispatchCommand($cmd)
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
    public function prepareAdminOutput()
    {
        $this->tpl->getStandardTemplate();

        $this->tpl->setTitleIcon(ilUtil::getImagePath('icon_wfe.svg'));
        $this->tpl->setTitle($this->object->getPresentationTitle());
        $this->tpl->setDescription($this->object->getLongDescription());

        $this->initLocator();
    }

    /**
     * @param string $section
     */
    public function initTabs($section)
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
    public function initLocator()
    {
        $path = $this->tree->getPathFull((int) $_GET["ref_id"]);
        foreach ((array) $path as $key => $row) {
            if ($row["title"] == "Workflow Engine") {
                $row["title"] = $this->lng->txt("obj_wfe");
            }

            $this->ilCtrl->setParameter($this, "ref_id", $row["child"]);
            $this->ilLocator->addItem(
                $row["title"],
                $this->ilCtrl->getLinkTarget($this, "dashboard.view"),
                ilFrameTargetInfo::_getFrame("MainContent"),
                $row["child"]
            );

            $this->ilCtrl->setParameter($this, "ref_id", $_GET["ref_id"]);
        }

        $this->tpl->setLocator();
    }

    /**
     * @param string $command
     *
     * @return string
     */
    public function dispatchToDashboard($command)
    {
        $this->initTabs('dashboard');
        /** @noinspection PhpIncludeInspection */
        require_once './Services/WorkflowEngine/classes/administration/class.ilWorkflowEngineDashboardGUI.php';
        $target_handler = new ilWorkflowEngineDashboardGUI($this);
        return $target_handler->handle($command);
    }

    /**
     * @param string $command
     *
     * @return string
     */
    public function dispatchToDefinitions($command)
    {
        $this->initTabs('definitions');
        /** @noinspection PhpIncludeInspection */
        require_once './Services/WorkflowEngine/classes/administration/class.ilWorkflowEngineDefinitionsGUI.php';
        $target_handler = new ilWorkflowEngineDefinitionsGUI($this, $this->dic);
        return $target_handler->handle($command);
    }

    /**
     * @param string $command
     *
     * @return string
     */
    public function dispatchToInstances($command)
    {
        $this->initTabs('instances');
        /** @noinspection PhpIncludeInspection */
        require_once './Services/WorkflowEngine/classes/administration/class.ilWorkflowEngineInstancesGUI.php';
        $target_handler = new ilWorkflowEngineInstancesGUI($this);
        return $target_handler->handle($command);
    }

    /**
     * @param string $command
     *
     * @return string
     */
    public function dispatchToSettings($command)
    {
        $this->initTabs('settings');
        /** @noinspection PhpIncludeInspection */
        require_once './Services/WorkflowEngine/classes/administration/class.ilWorkflowEngineSettingsGUI.php';
        $target_handler = new ilWorkflowEngineSettingsGUI($this);
        return $target_handler->handle($command);
    }
}
