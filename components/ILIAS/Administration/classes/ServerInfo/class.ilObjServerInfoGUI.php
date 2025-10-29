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
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Administration\JavaServerGUI;
use ILIAS\Setup\AgentFinder;
use ILIAS\Setup\CLI\StatusCommand;

/**
 * @ilCtrl_isCalledBy ilObjServerInfoGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjServerInfoGUI: ilPermissionGUI
 */
class ilObjServerInfoGUI extends ilObject2GUI
{
    private ilIniFile $client_ini;
    private AgentFinder $agent_finder;

    public function __construct(int $id = 0, int $id_type = self::REPOSITORY_NODE_ID, int $parent_node_id = 0)
    {
        parent::__construct($id, $id_type, $parent_node_id);

        global $DIC;
        $this->client_ini = $DIC->clientIni();
        $this->agent_finder = $DIC['setup.agentfinder'];
    }

    public function getType(): string
    {
        return ilObjServerInfo::TYPE;
    }

    private function getJavaServerGUI(): JavaServerGUI
    {
        return new JavaServerGUI(
            $this->ctrl,
            $this->tpl,
            $this->lng,
            $this->settings,
            $this->checkPermissionBool('write')
        );
    }

    public function executeCommand(): void
    {
        $this->checkPermission('read');

        $this->lng->loadLanguageModule('administration');
        $this->prepareOutput();

        switch ($this->ctrl->getNextClass($this)) {
            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->activateTab('perm_settings');
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                break;

            case strtolower(JavaServerGUI::class):
                $this->tabs_gui->activateTab('java_server');
                $this->ctrl->forwardCommand($this->getJavaServerGUI());
                break;

            default:
                $cmd = $this->ctrl->getCmd("view");
                switch ($cmd) {
                    case 'view':
                    case 'viewVcs':
                        $this->tabs_gui->activateTab('server_data');
                        $this->$cmd();
                        break;
                    case 'status':
                        $this->tabs_gui->activateTab('installation_status');
                        $this->$cmd();
                        break;
                    case 'phpinfo':
                        phpinfo();
                        exit;
                }
        }
    }

    public function getAdminTabs(): void
    {
        $this->tabs_gui->addTab(
            'installation_status',
            $this->lng->txt('installation_status'),
            $this->ctrl->getLinkTarget($this, 'status')
        );

        $this->tabs_gui->addTab(
            'server_data',
            $this->lng->txt('server_data'),
            $this->ctrl->getLinkTarget($this, 'view')
        );

        $this->tabs_gui->addTab(
            'java_server',
            $this->lng->txt('java_server'),
            $this->ctrl->getLinkTargetByClass([self::class, JavaServerGUI::class]),
        );

        if ($this->checkPermissionBool('edit_permission')) {
            $this->tabs_gui->addTab(
                'perm_settings',
                $this->lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass([self::class, ilPermissionGUI::class], 'perm')
            );
        }
    }

    public function view(): void
    {
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt("vc_information"),
                $this->ctrl->getLinkTarget($this, 'viewVcs')
            )
        );

        $form = new ilPropertyFormGUI();

        // installation name
        $ne = new ilNonEditableValueGUI($this->lng->txt("inst_name"), "");
        $ne->setValue($this->client_ini->readVariable("client", "name"));
        $ne->setInfo($this->client_ini->readVariable("client", "description"));
        $form->addItem($ne);

        // client id
        $ne = new ilNonEditableValueGUI($this->lng->txt("client_id"), "");
        $ne->setValue(CLIENT_ID);
        $form->addItem($ne);

        // installation id
        $ne = new ilNonEditableValueGUI($this->lng->txt("inst_id"), "");
        $ne->setValue($this->settings->get("inst_id"));
        $form->addItem($ne);

        // database version
        $ne = new ilNonEditableValueGUI($this->lng->txt("db_version"), "");
        $ne->setValue($this->settings->get("db_version"));

        $form->addItem($ne);

        // ilias version
        $ne = new ilNonEditableValueGUI($this->lng->txt("ilias_version"), "");
        $ne->setValue(ILIAS_VERSION);
        $form->addItem($ne);

        // host
        $ne = new ilNonEditableValueGUI($this->lng->txt("host"), "");
        $ne->setValue($_SERVER["SERVER_NAME"]);
        $form->addItem($ne);

        // ip & port
        $ne = new ilNonEditableValueGUI($this->lng->txt("ip_address") . " & " . $this->lng->txt("port"), "");
        $ne->setValue($_SERVER["SERVER_ADDR"] . ":" . $_SERVER["SERVER_PORT"]);
        $form->addItem($ne);

        // server
        $ne = new ilNonEditableValueGUI($this->lng->txt("server_software"), "");
        $ne->setValue($_SERVER["SERVER_SOFTWARE"]);
        $form->addItem($ne);

        // http path
        $ne = new ilNonEditableValueGUI($this->lng->txt("http_path"), "");
        $ne->setValue(ILIAS_HTTP_PATH);
        $form->addItem($ne);

        // absolute path
        $ne = new ilNonEditableValueGUI($this->lng->txt("absolute_path"), "");
        $ne->setValue(ILIAS_ABSOLUTE_PATH);
        $form->addItem($ne);

        $not_set = $this->lng->txt("path_not_set");

        // convert
        $ne = new ilNonEditableValueGUI($this->lng->txt("path_to_convert"), "");
        $ne->setValue(PATH_TO_CONVERT ?: $not_set);
        $form->addItem($ne);

        // zip
        $ne = new ilNonEditableValueGUI($this->lng->txt("path_to_zip"), "");
        $ne->setValue(PATH_TO_ZIP ?: $not_set);
        $form->addItem($ne);

        // unzip
        $ne = new ilNonEditableValueGUI($this->lng->txt("path_to_unzip"), "");
        $ne->setValue(PATH_TO_UNZIP ?: $not_set);
        $form->addItem($ne);

        // java
        $ne = new ilNonEditableValueGUI($this->lng->txt("path_to_java"), "");
        $ne->setValue(PATH_TO_JAVA ?: $not_set);
        $form->addItem($ne);

        // mkisofs
        $ne = new ilNonEditableValueGUI($this->lng->txt("path_to_mkisofs"), "");
        $ne->setValue(PATH_TO_MKISOFS ?: $not_set);
        $form->addItem($ne);

        $form->setTitle($this->lng->txt("server_data"));

        $tpl = new ilTemplate("tpl.server_data.html", true, true, "components/ILIAS/Administration");
        $tpl->setVariable("FORM", $form->getHTML());
        $tpl->setVariable("PHP_INFO_TARGET", $this->ctrl->getLinkTarget($this, "phpinfo"));

        $this->tpl->setContent($tpl->get());
    }

    public function status(): void
    {
        $st = new StatusCommand($this->agent_finder);
        $metric = $st->getMetrics($this->agent_finder->getAgents());
        $report = $metric->toUIReport($this->ui_factory, $this->lng->txt("installation_status"));
        $this->tpl->setContent($this->ui_renderer->render($report));
    }

    public function viewVcs()
    {
        $vc = new ilGitInformation();
        $html = $vc->getInformationAsHtml();

        if ($html) {
            $this->tpl->setOnScreenMessage('info', $html);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('vc_information_not_determined'));
        }

        $this->view();
    }
}
