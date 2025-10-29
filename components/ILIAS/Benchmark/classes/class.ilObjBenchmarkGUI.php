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

use ILIAS\HTTP\Wrapper\WrapperFactory as WrapperFactoryAlias;

/**
 * @ilCtrl_isCalledBy ilObjBenchmarkGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjBenchmarkGUI: ilPermissionGUI
 */
class ilObjBenchmarkGUI extends ilObject2GUI
{
    private ilPropertyFormGUI $form;
    private ilBenchmark $bench;
    private WrapperFactoryAlias $wrapper;

    public function __construct(int $id = 0, int $id_type = self::REPOSITORY_NODE_ID, int $parent_node_id = 0)
    {
        parent::__construct($id, $id_type, $parent_node_id);

        global $DIC;
        $this->wrapper = $DIC->http()->wrapper();
        $this->bench = $DIC["ilBench"];
    }

    public function getType(): string
    {
        return ilObjBenchmark::TYPE;
    }

    public function executeCommand(): void
    {
        $this->checkPermission('read');

        $this->lng->loadLanguageModule($this->getType());
        $this->prepareOutput();

        switch ($this->ctrl->getNextClass($this)) {
            case strtolower(ilPermissionGUI::class):
                $this->tabs_gui->activateTab('permissions');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                $cmd = $this->ctrl->getCmd("view");
                switch ($cmd) {
                    case 'settings':
                    case 'update':
                        $this->tabs_gui->activateTab('settings');
                        $this->checkPermission('write');
                        $this->$cmd();
                        break;

                    case 'view':
                    case 'slowest_first':
                    case 'sorted_by_sql':
                    case 'by_first_table':
                        $this->getViewSubtabs();
                        $this->tabs_gui->activateTab('view');
                        $this->tabs_gui->activateSubTab($cmd);
                        $this->view();
                        break;
                }
        }
    }

    public function getAdminTabs(): void
    {
        $this->tabs_gui->addTab(
            'view',
            $this->lng->txt('view'),
            $this->ctrl->getLinkTarget($this, 'view')
        );

        if ($this->checkPermissionBool('write')) {
            $this->tabs_gui->addTab(
                'settings',
                $this->lng->txt('settings'),
                $this->ctrl->getLinkTarget($this, 'settings')
            );
        }
        if ($this->checkPermissionBool('edit_permission')) {
            $this->tabs_gui->addTab(
                'permissions',
                $this->lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass([self::class, ilPermissionGUI::class], 'perm')
            );
        }
    }

    private function getViewSubtabs(): void
    {
        $this->tabs_gui->addSubTab(
            'view',
            $this->lng->txt('adm_db_bench_chronological'),
            $this->ctrl->getLinkTarget($this, 'view')
        );

        $this->tabs_gui->addSubTab(
            'slowest_first',
            $this->lng->txt('adm_db_bench_slowest_first'),
            $this->ctrl->getLinkTarget($this, 'slowest_first')
        );

        $this->tabs_gui->addSubTab(
            'sorted_by_sql',
            $this->lng->txt('adm_db_bench_sorted_by_sql'),
            $this->ctrl->getLinkTarget($this, 'sorted_by_sql')
        );

        $this->tabs_gui->addSubTab(
            'by_first_table',
            $this->lng->txt('adm_db_bench_by_first_table'),
            $this->ctrl->getLinkTarget($this, 'by_first_table')
        );
    }

    private function settings(): void
    {
        $this->form = new ilPropertyFormGUI();

        // Activate DB Benchmark
        $cb = new ilCheckboxInputGUI($this->lng->txt("adm_activate_db_benchmark"), ilBenchmark::ENABLE_DB_BENCH);
        $cb->setChecked((bool) $this->settings->get(ilBenchmark::ENABLE_DB_BENCH));
        $cb->setInfo($this->lng->txt("adm_activate_db_benchmark_desc"));
        $this->form->addItem($cb);

        // DB Benchmark User
        $ti = new ilTextInputGUI($this->lng->txt("adm_db_benchmark_user"), ilBenchmark::DB_BENCH_USER);
        $login = ilObjUser::_lookupLogin((int) ($this->settings->get(ilBenchmark::DB_BENCH_USER)));
        $ti->setValue($login);
        $ti->setInfo($this->lng->txt("adm_db_benchmark_user_desc"));
        $this->form->addItem($ti);

        $this->form->setTitle($this->lng->txt("adm_db_benchmark"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->addCommandButton('update', $this->lng->txt('save'));

        $this->tpl->setContent($this->form->getHTML());
    }

    public function update(): void
    {
        if ($this->wrapper->post()->has(ilBenchmark::ENABLE_DB_BENCH)
            && $this->wrapper->post()->has(ilBenchmark::DB_BENCH_USER)) {
            $activate = $this->wrapper->post()->retrieve(ilBenchmark::ENABLE_DB_BENCH, $this->refinery->kindlyTo()->bool());
            if ($activate) {
                $user_name = $this->wrapper->post()->retrieve(ilBenchmark::DB_BENCH_USER, $this->refinery->kindlyTo()->string());
                $this->bench->clearData();
                $this->bench->enableDbBenchmarkForUserName($user_name);
            }
        } else {
            $this->bench->clearData();
            $this->bench->disableDbBenchmark();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, 'settings');
    }

    public function view(): void
    {
        $cmd = $this->ctrl->getCmd("view");
        $mode = $cmd === 'view' ? 'chronological' : $cmd;

        $table = new ilBenchmarkTableGUI($this, $cmd, $this->bench->getDbBenchRecords(), $mode);
        $this->tpl->setContent($table->getHTML());
    }
}
