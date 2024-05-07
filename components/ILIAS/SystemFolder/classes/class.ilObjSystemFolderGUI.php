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

use ILIAS\Setup\Metrics;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Setup\ImplementationOfAgentFinder;
use ILIAS\Data\Factory;
use ILIAS\Setup\CLI\StatusCommand;

/**
 * Class ilObjSystemFolderGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @ilCtrl_Calls ilObjSystemFolderGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjSystemFolderGUI: ilObjectOwnershipManagementGUI, ilCronManagerGUI
 */
class ilObjSystemFolderGUI extends ilObjectGUI
{
    protected \ILIAS\Repository\InternalGUIService $gui;
    protected ilPropertyFormGUI $form;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;
    protected ilTabsGUI $tabs;
    protected ilRbacSystem $rbacsystem;
    protected ilObjectDefinition $obj_definition;
    protected ilErrorHandling $error;
    protected ilDBInterface $db;
    protected ilStyleDefinition $style_definition;
    protected ilHelpGUI $help;
    protected ilIniFile $client_ini;
    protected ilBenchmark $bench;
    public string $type;
    protected \ILIAS\HTTP\Wrapper\WrapperFactory $wrapper;
    protected \ILIAS\Refinery\Factory $refinery;

    /**
    * Constructor
    * @access public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->obj_definition = $DIC["objDefinition"];
        $this->settings = $DIC->settings();
        $this->error = $DIC["ilErr"];
        $this->db = $DIC->database();
        $this->style_definition = $DIC["styleDefinition"];
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->help = $DIC["ilHelp"];
        $this->toolbar = $DIC->toolbar();
        $this->client_ini = $DIC["ilClientIniFile"];
        $this->type = "adm";
        $this->bench = $DIC["ilBench"];
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->lng->loadLanguageModule("administration");
        $this->lng->loadLanguageModule("adm");
        $this->content_style_domain = $DIC->contentStyle()
                  ->domain()
                  ->styleForRefId($this->object->getRefId());
        $this->gui = $DIC->repository()->internal()->gui();
    }

    public function executeCommand(): void
    {
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilobjectownershipmanagementgui":
                $this->setSystemCheckSubTabs("no_owner");
                $gui = new ilObjectOwnershipManagementGUI(0);
                $this->ctrl->forwardCommand($gui);
                break;

            case "ilcronmanagergui":
                $ilTabs->activateTab("cron_jobs");
                $gui = new ilCronManagerGUI();
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $cmd = $this->ctrl->getCmd("view");

                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }

    /**
    * show admin subpanels and basic settings form
    *
    * @access	public
    */
    public function viewObject(): void
    {
        $ilAccess = $this->access;

        if ($ilAccess->checkAccess("read", "", $this->object->getRefId())) {
            $this->showBasicSettingsObject();
            return;
        }
        $this->showServerInfoObject();
    }

    /**
    * Set sub tabs for general settings
    */
    public function setSystemCheckSubTabs($a_activate): void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $ilTabs->addSubTab(
            "system_check_sub",
            $this->lng->txt("system_check"),
            $ilCtrl->getLinkTarget($this, "check")
        );
        $ilTabs->addSubTab(
            "no_owner",
            $this->lng->txt("system_check_no_owner"),
            $ilCtrl->getLinkTargetByClass("ilObjectOwnershipManagementGUI")
        );

        $ilTabs->setSubTabActive($a_activate);
        $ilTabs->setTabActive("system_check");
    }

    public function cancelObject(): void
    {
        $this->ctrl->redirect($this, "view");
    }


    /**
     * Benchmark settings
     */
    public function benchmarkObject(): void
    {
        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
        }

        $write_access = $this->rbacsystem->checkAccess("write", $this->object->getRefId());

        $this->benchmarkSubTabs("settings");

        $this->form = new ilPropertyFormGUI();

        // Activate DB Benchmark
        $cb = new ilCheckboxInputGUI($this->lng->txt("adm_activate_db_benchmark"), ilBenchmark::ENABLE_DB_BENCH);
        $cb->setChecked((bool) $this->settings->get(ilBenchmark::ENABLE_DB_BENCH));
        $cb->setInfo($this->lng->txt("adm_activate_db_benchmark_desc"));
        $cb->setDisabled(!$write_access);
        $this->form->addItem($cb);

        // DB Benchmark User
        $ti = new ilTextInputGUI($this->lng->txt("adm_db_benchmark_user"), ilBenchmark::DB_BENCH_USER);
        $user_id = ($this->settings->get(ilBenchmark::DB_BENCH_USER)) ?? null;
        if ($user_id !== null && ilObjUser::_lookupLogin((int) $user_id) !== '') {
            $ti->setValue(ilObjUser::_lookupLogin($user_id));
        } else {
            $ti->setValue('');
        }
        $ti->setInfo($this->lng->txt("adm_db_benchmark_user_desc"));
        $ti->setDisabled(!$write_access);
        $this->form->addItem($ti);

        if ($write_access) {
            $this->form->addCommandButton("saveBenchSettings", $this->lng->txt("save"));
        }

        $this->form->setTitle($this->lng->txt("adm_db_benchmark"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Show db benchmark results
     */
    public function showDbBenchChronologicalObject(): void
    {
        $this->benchmarkSubTabs("chronological");
        $this->showDbBenchResults("chronological");
    }

    /**
     * Show db benchmark results
     */
    public function showDbBenchSlowestFirstObject(): void
    {
        $this->benchmarkSubTabs("slowest_first");
        $this->showDbBenchResults("slowest_first");
    }

    /**
     * Show db benchmark results
     */
    public function showDbBenchSortedBySqlObject(): void
    {
        $this->benchmarkSubTabs("sorted_by_sql");
        $this->showDbBenchResults("sorted_by_sql");
    }

    /**
     * Show db benchmark results
     */
    public function showDbBenchByFirstTableObject(): void
    {
        $this->benchmarkSubTabs("by_first_table");
        $this->showDbBenchResults("by_first_table");
    }

    /**
     * Show Db Benchmark Results
     *
     * @param	string		mode
     */
    public function showDbBenchResults($a_mode): void
    {
        $tpl = $this->tpl;

        $ilBench = $this->bench;
        $rec = $ilBench->getDbBenchRecords();

        $table = new ilBenchmarkTableGUI($this, "benchmark", $rec, $a_mode);
        $tpl->setContent($table->getHTML());
    }

    /**
     * Benchmark sub tabs
     *
     * @param
     * @return
     */
    public function benchmarkSubTabs($a_current): void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilBench = $this->bench;
        $ilTabs->activateTab("benchmarks"); // #18083

        $ilTabs->addSubtab(
            "settings",
            $lng->txt("settings"),
            $ilCtrl->getLinkTarget($this, "benchmark")
        );

        $rec = $ilBench->getDbBenchRecords();
        if ($rec !== []) {
            $ilTabs->addSubtab(
                "chronological",
                $lng->txt("adm_db_bench_chronological"),
                $ilCtrl->getLinkTarget($this, "showDbBenchChronological")
            );
            $ilTabs->addSubtab(
                "slowest_first",
                $lng->txt("adm_db_bench_slowest_first"),
                $ilCtrl->getLinkTarget($this, "showDbBenchSlowestFirst")
            );
            $ilTabs->addSubtab(
                "sorted_by_sql",
                $lng->txt("adm_db_bench_sorted_by_sql"),
                $ilCtrl->getLinkTarget($this, "showDbBenchSortedBySql")
            );
            $ilTabs->addSubtab(
                "by_first_table",
                $lng->txt("adm_db_bench_by_first_table"),
                $ilCtrl->getLinkTarget($this, "showDbBenchByFirstTable")
            );
        }

        $ilTabs->activateSubTab($a_current);
    }


    /**
     * Save benchmark settings
     */
    public function saveBenchSettingsObject(): void
    {
        $write_access = $this->rbacsystem->checkAccess("write", $this->object->getRefId());
        if (!$write_access) {
            $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
            return;
        }

        if ($this->wrapper->post()->has(ilBenchmark::ENABLE_DB_BENCH)
            && $this->wrapper->post()->has(ilBenchmark::DB_BENCH_USER)) {
            $activate = $this->wrapper->post()->retrieve(ilBenchmark::ENABLE_DB_BENCH, $this->refinery->kindlyTo()->bool());
            if ($activate) {
                $user_name = $this->wrapper->post()->retrieve(ilBenchmark::DB_BENCH_USER, $this->refinery->kindlyTo()->string());
                $this->bench->enableDbBenchmarkForUserName($user_name);
            }
        } else {
            $this->bench->disableDbBenchmark();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);

        $this->ctrl->redirect($this, "benchmark");
    }


    /**
    * save benchmark settings
    */
    public function switchBenchModuleObject(): void
    {
        $this->ctrl->setParameter($this, 'cur_mod', $_POST['module']);
        $this->ctrl->redirect($this, "benchmark");
    }


    /**
    * delete all benchmark records
    */
    public function clearBenchObject(): void
    {
        $ilBench = $this->bench;
        $ilBench->clearData();
        $this->saveBenchSettingsObject();
    }

    // get tabs
    public function getAdminTabs(): void
    {
        $rbacsystem = $this->rbacsystem;
        $ilHelp = $this->help;

        //		$ilHelp->setScreenIdComponent($this->object->getType());

        $this->ctrl->setParameter($this, "ref_id", $this->object->getRefId());

        // general settings
        if ($rbacsystem->checkAccess("read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "general_settings",
                $this->ctrl->getLinkTarget($this, "showBasicSettings"),
                ["showBasicSettings", "saveBasicSettings"],
                get_class($this)
            );
        }

        // server info
        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "server",
                $this->ctrl->getLinkTarget($this, "showServerInfo"),
                ["showServerInfo", "view"],
                get_class($this)
            );
        }

        if ($rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'cron_jobs',
                $this->ctrl->getLinkTargetByClass('ilCronManagerGUI', ''),
                '',
                get_class($this)
            );

            $this->tabs_gui->addTarget(
                'benchmarks',
                $this->ctrl->getLinkTarget($this, 'benchmark'),
                'benchmark',
                get_class($this)
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }

    /**
     * Show PHP Information
     * @return never
     */
    public function showPHPInfoObject(): void
    {
        phpinfo();
        exit;
    }

    //
    //
    // Server Info
    //
    //

    // TODO: remove this subtabs
    /**
    * Set sub tabs for server info
    */
    public function setServerInfoSubTabs($a_activate): void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $rbacsystem = $this->rbacsystem;

        $ilTabs->addSubTabTarget("installation_status", $ilCtrl->getLinkTarget($this, "showServerInstallationStatus"));

        $ilTabs->addSubTabTarget("server_data", $ilCtrl->getLinkTarget($this, "showServerInfo"));

        if ($rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $ilTabs->addSubTabTarget("java_server", $ilCtrl->getLinkTarget($this, "showJavaServer"));
        }

        $ilTabs->setSubTabActive($a_activate);
        $ilTabs->setTabActive("server");
    }

    /**
    * Show server info
    */
    public function showServerInfoObject(): void
    {
        /**
         * @var $ilToolbar ilToolbarGUI
         * @var $lng       ilLanguage
         * @var $ilCtrl    ilCtrl
         * @var $tpl       ilTemplate
         */
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilToolbar = $this->toolbar;

        $this->gui->link(
            $this->lng->txt("vc_information"),
            $this->ctrl->getLinkTarget($this, 'showVcsInformation')
        )->toToolbar();

        $this->initServerInfoForm();
        // TODO: remove sub tabs
        //        $this->tabs->setTabActive("server");
        $this->setServerInfoSubTabs("server_data");

        $btpl = new ilTemplate("tpl.server_data.html", true, true, "components/ILIAS/SystemFolder");
        $btpl->setVariable("FORM", $this->form->getHTML());
        $btpl->setVariable("PHP_INFO_TARGET", $ilCtrl->getLinkTarget($this, "showPHPInfo"));
        $tpl->setContent($btpl->get());
    }

    /**
    * Init server info form.
    *
    * @param        int        $a_mode        Edit Mode
    */
    public function initServerInfoForm(): void
    {
        $lng = $this->lng;
        $ilClientIniFile = $this->client_ini;
        $ilSetting = $this->settings;

        $this->form = new ilPropertyFormGUI();

        // installation name
        $ne = new ilNonEditableValueGUI($lng->txt("inst_name"), "");
        $ne->setValue($ilClientIniFile->readVariable("client", "name"));
        $ne->setInfo($ilClientIniFile->readVariable("client", "description"));
        $this->form->addItem($ne);

        // client id
        $ne = new ilNonEditableValueGUI($lng->txt("client_id"), "");
        $ne->setValue(CLIENT_ID);
        $this->form->addItem($ne);

        // installation id
        $ne = new ilNonEditableValueGUI($lng->txt("inst_id"), "");
        $ne->setValue($ilSetting->get("inst_id"));
        $this->form->addItem($ne);

        // database version
        $ne = new ilNonEditableValueGUI($lng->txt("db_version"), "");
        $ne->setValue($ilSetting->get("db_version"));

        $this->form->addItem($ne);

        // ilias version
        $ne = new ilNonEditableValueGUI($lng->txt("ilias_version"), "");
        $ne->setValue(ILIAS_VERSION);
        $this->form->addItem($ne);

        // host
        $ne = new ilNonEditableValueGUI($lng->txt("host"), "");
        $ne->setValue($_SERVER["SERVER_NAME"]);
        $this->form->addItem($ne);

        // ip & port
        $ne = new ilNonEditableValueGUI($lng->txt("ip_address") . " & " . $this->lng->txt("port"), "");
        $ne->setValue($_SERVER["SERVER_ADDR"] . ":" . $_SERVER["SERVER_PORT"]);
        $this->form->addItem($ne);

        // server
        $ne = new ilNonEditableValueGUI($lng->txt("server_software"), "");
        $ne->setValue($_SERVER["SERVER_SOFTWARE"]);
        $this->form->addItem($ne);

        // http path
        $ne = new ilNonEditableValueGUI($lng->txt("http_path"), "");
        $ne->setValue(ILIAS_HTTP_PATH);
        $this->form->addItem($ne);

        // absolute path
        $ne = new ilNonEditableValueGUI($lng->txt("absolute_path"), "");
        $ne->setValue(ILIAS_ABSOLUTE_PATH);
        $this->form->addItem($ne);

        $not_set = $lng->txt("path_not_set");

        // convert
        $ne = new ilNonEditableValueGUI($lng->txt("path_to_convert"), "");
        $ne->setValue(PATH_TO_CONVERT ?: $not_set);
        $this->form->addItem($ne);

        // zip
        $ne = new ilNonEditableValueGUI($lng->txt("path_to_zip"), "");
        $ne->setValue(PATH_TO_ZIP ?: $not_set);
        $this->form->addItem($ne);

        // unzip
        $ne = new ilNonEditableValueGUI($lng->txt("path_to_unzip"), "");
        $ne->setValue(PATH_TO_UNZIP ?: $not_set);
        $this->form->addItem($ne);

        // java
        $ne = new ilNonEditableValueGUI($lng->txt("path_to_java"), "");
        $ne->setValue(PATH_TO_JAVA ?: $not_set);
        $this->form->addItem($ne);

        // mkisofs
        $ne = new ilNonEditableValueGUI($lng->txt("path_to_mkisofs"), "");
        $ne->setValue(PATH_TO_MKISOFS ?: $not_set);
        $this->form->addItem($ne);

        // latex
        $ne = new ilNonEditableValueGUI($lng->txt("url_to_latex"), "");
        $ne->setValue(URL_TO_LATEX ?: $not_set);
        $this->form->addItem($ne);


        $this->form->setTitle($lng->txt("server_data"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    protected function showServerInstallationStatusObject(): void
    {
        $this->setServerInfoSubTabs("installation_status");
        $this->renderServerStatus();
    }

    protected function renderServerStatus(): void
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();
        $refinery = $DIC->refinery();

        $metric = $this->getServerStatusInfo($refinery);
        $report = $metric->toUIReport($f, $this->lng->txt("installation_status"));

        $this->tpl->setContent($r->render($report));
    }

    protected function getServerStatusInfo(ILIAS\Refinery\Factory $refinery): ILIAS\Setup\Metrics\Metric
    {
        $data = new Factory();
        $lng = new ilSetupLanguage('en');
        $interface_finder = new ImplementationOfInterfaceFinder();

        $agent_finder = new ImplementationOfAgentFinder(
            $refinery,
            $data,
            $lng,
            $interface_finder,
            []
        );

        $st = new StatusCommand($agent_finder);

        return $st->getMetrics($agent_finder->getAgents());
    }

    //
    //
    // General Settings
    //
    //

    /**
    * Set sub tabs for general settings
    */
    public function setGeneralSettingsSubTabs($a_activate): void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $ilTabs->addSubTabTarget("basic_settings", $ilCtrl->getLinkTarget($this, "showBasicSettings"));
        $ilTabs->addSubTabTarget("header_title", $ilCtrl->getLinkTarget($this, "showHeaderTitle"));
        $ilTabs->addSubTabTarget("contact_data", $ilCtrl->getLinkTarget($this, "showContactInformation"));

        $ilTabs->setSubTabActive($a_activate);
        $ilTabs->setTabActive("general_settings");
    }

    //
    //
    // Basic Settings
    //
    //

    /**
    * Show basic settings
    */
    public function showBasicSettingsObject(): void
    {
        $tpl = $this->tpl;

        $this->initBasicSettingsForm();
        $this->setGeneralSettingsSubTabs("basic_settings");

        $tpl->setContent($this->form->getHTML());
    }


    /**
    * Init basic settings form.
    */
    public function initBasicSettingsForm(): void
    {
        /**
         * @var $lng ilLanguage
         * @var $ilSetting ilSetting
         */
        $lng = $this->lng;
        $ilSetting = $this->settings;

        $this->form = new ilPropertyFormGUI();
        $lng->loadLanguageModule("pd");

        // installation short title
        $ti = new ilTextInputGUI($this->lng->txt("short_inst_name"), "short_inst_name");
        $ti->setMaxLength(200);
        $ti->setSize(40);
        $ti->setValue($ilSetting->get("short_inst_name"));
        $ti->setInfo($this->lng->txt("short_inst_name_info"));
        $this->form->addItem($ti);


        $cb = new ilCheckboxInputGUI($this->lng->txt("pub_section"), "pub_section");
        $cb->setInfo($lng->txt("pub_section_info"));
        if (ilPublicSectionSettings::getInstance()->isEnabled()) {
            $cb->setChecked(true);
        }
        $this->form->addItem($cb);

        $this->lng->loadLanguageModule('administration');
        $domains = new ilTextInputGUI($this->lng->txt('adm_pub_section_domain_filter'), 'public_section_domains');
        $domains->setInfo($this->lng->txt('adm_pub_section_domain_filter_info'));
        $domains->setMulti(true);
        $domains->setValue(current(ilPublicSectionSettings::getInstance()->getDomains()));
        $domains->setMultiValues(ilPublicSectionSettings::getInstance()->getDomains());

        $cb->addSubItem($domains);


        // Enable Global Profiles
        $cb_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_user_publish'), 'enable_global_profiles');
        $cb_prop->setInfo($lng->txt('pd_enable_user_publish_info'));
        $cb_prop->setChecked((bool) $ilSetting->get('enable_global_profiles'));
        $cb->addSubItem($cb_prop);

        // search engine
        $robot_settings = ilRobotSettings::getInstance();
        $cb2 = new ilCheckboxInputGUI($this->lng->txt("search_engine"), "open_google");
        $cb2->setInfo($this->lng->txt("enable_search_engine"));
        $this->form->addItem($cb2);

        if (!$robot_settings->checkRewrite()) {
            $cb2->setAlert($lng->txt("allow_override_alert"));
            $cb2->setChecked(false);
            $cb2->setDisabled(true);
        } elseif ($ilSetting->get("open_google")) {
            $cb2->setChecked(true);
        }

        // locale
        $ti = new ilTextInputGUI($this->lng->txt("adm_locale"), "locale");
        $ti->setMaxLength(80);
        $ti->setSize(40);
        $ti->setInfo($this->lng->txt("adm_locale_info"));
        $ti->setValue($ilSetting->get("locale"));
        $this->form->addItem($ti);

        // save and cancel commands
        $this->form->addCommandButton("saveBasicSettings", $lng->txt("save"));

        $this->form->setTitle($lng->txt("basic_settings"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    /**
    * Save basic settings form
    *
    */
    public function saveBasicSettingsObject(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(self::class);
            //$ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $this->initBasicSettingsForm();
        if ($this->form->checkInput()) {
            $ilSetting->set("short_inst_name", $this->form->getInput("short_inst_name"));

            $public_section = ilPublicSectionSettings::getInstance();
            $public_section->setEnabled($this->form->getInput('pub_section'));

            $domains = [];
            foreach ((array) $this->form->getInput('public_section_domains') as $domain) {
                if (strlen(trim($domain)) !== 0) {
                    $domains[] = $domain;
                }
            }
            $public_section->setDomains($domains);
            $public_section->save();

            $global_profiles = ($this->form->getInput("pub_section"))
                ? (int) $this->form->getInput('enable_global_profiles')
                : 0;
            $ilSetting->set('enable_global_profiles', $global_profiles);

            $ilSetting->set("open_google", $this->form->getInput("open_google"));
            $ilSetting->set("locale", $this->form->getInput("locale"));

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "showBasicSettings");
        }
        $this->setGeneralSettingsSubTabs("basic_settings");
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    //
    //
    // Header title
    //
    //

    /**
    * Show header title
    */
    public function showHeaderTitleObject(
        $a_get_post_values = false,
        bool $add_entry = false
    ): void {
        $tpl = $this->tpl;
        $this->setGeneralSettingsSubTabs("header_title");
        $table = new ilObjectTranslationTableGUI($this, "showHeaderTitle", false);
        $post = $this->gui->http()->request()->getParsedBody();
        if ($a_get_post_values) {
            $vals = array();
            foreach (($post["title"] ?? []) as $k => $v) {
                $def = $post["default"] ?? "";
                $vals[] = array("title" => $v,
                    "desc" => ($post["desc"][$k] ?? ""),
                    "lang" => ($post["lang"][$k] ?? ""),
                    "default" => ($def == $k));
            }
            if ($add_entry) {
                $vals[] = array("title" => "",
                                "desc" => "",
                                "lang" => "",
                                "default" => false);
            }
            $table->setData($vals);
        } else {
            $data = $this->object->getHeaderTitleTranslations();
            if (isset($data["Fobject"]) && is_array($data["Fobject"])) {
                foreach ($data["Fobject"] as $k => $v) {
                    if ($k == $data["default_language"]) {
                        $data["Fobject"][$k]["default"] = true;
                    } else {
                        $data["Fobject"][$k]["default"] = false;
                    }
                }
            } else {
                $data["Fobject"] = array();
            }
            $table->setData($data["Fobject"]);
        }
        $tpl->setContent($table->getHTML());
    }

    /**
    * Save header titles
    */
    public function saveHeaderTitlesObject(bool $delete = false)
    {
        global $DIC;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(self::class, "showHeaderTitle");
        }

        $post = $DIC->http()->request()->getParsedBody();
        foreach ($post["title"] as $k => $v) {
            if ($delete && ($post["check"][$k] ?? false)) {
                unset($post["title"][$k]);
                unset($post["desc"][$k]);
                unset($post["lang"][$k]);
                if ($k == $post["default"]) {
                    unset($post["default"]);
                }
            }
        }



        // default language set?
        if (!isset($post["default"]) && count($post["lang"]) > 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("msg_no_default_language"));
            $this->showHeaderTitleObject(true);
            return;
        }

        // all languages set?
        if (array_key_exists("", $post["lang"])) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("msg_no_language_selected"));
            $this->showHeaderTitleObject(true);
            return;
        }

        // no single language is selected more than once?
        if (count(array_unique($post["lang"])) < count($post["lang"])) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("msg_multi_language_selected"));
            $this->showHeaderTitleObject(true);
            return;
        }

        // save the stuff
        $this->object->removeHeaderTitleTranslations();
        foreach ($post["title"] as $k => $v) {
            $desc = $post["desc"][$k] ?? "";
            $this->object->addHeaderTitleTranslation(
                ilUtil::stripSlashes($v),
                ilUtil::stripSlashes($desc),
                ilUtil::stripSlashes($post["lang"][$k]),
                ($post["default"] == $k)
            );
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "showHeaderTitle");
    }

    /**
    * Add a header title
    */
    public function addHeaderTitleObject(): void
    {
        $k = 1;
        $this->showHeaderTitleObject(true, true);
    }

    /**
    * Remove header titles
    */
    public function deleteHeaderTitlesObject(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $this->saveHeaderTitlesObject(true);
    }


    //
    //
    // Cron Jobs
    //
    //

    /*
     * OLD GLOBAL CRON JOB SWITCHES (ilSetting)
     *
     * cron_user_check => obsolete
     * cron_inactive_user_delete => obsolete
     * cron_inactivated_user_delete => obsolete
     * cron_link_check => obsolete
     * cron_web_resource_check => migrated
     * cron_lucene_index => obsolete
     * forum_notification => migrated
     * mail_notification => migrated
     * crsgrp_ntf => migrated
     * cron_upd_adrbook => migrated
     */

    public function jumpToCronJobsObject(): void
    {
        // #13010 - this is used for external settings
        $this->ctrl->redirectByClass("ilCronManagerGUI", "render");
    }


    //
    //
    // Contact Information
    //
    //

    /**
    * Show contact information
    */
    public function showContactInformationObject(): void
    {
        $tpl = $this->tpl;

        $this->initContactInformationForm();
        $this->setGeneralSettingsSubTabs("contact_data");
        $tpl->setContent($this->form->getHTML());
    }

    /**
    * Init contact information form.
    */
    public function initContactInformationForm(): void
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;

        $this->form = new ilPropertyFormGUI();

        // first name
        $ti = new ilTextInputGUI($this->lng->txt("firstname"), "admin_firstname");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_firstname"));
        $this->form->addItem($ti);

        // last name
        $ti = new ilTextInputGUI($this->lng->txt("lastname"), "admin_lastname");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_lastname"));
        $this->form->addItem($ti);

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "admin_title");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setValue($ilSetting->get("admin_title"));
        $this->form->addItem($ti);

        // position
        $ti = new ilTextInputGUI($this->lng->txt("position"), "admin_position");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setValue($ilSetting->get("admin_position"));
        $this->form->addItem($ti);

        // institution
        $ti = new ilTextInputGUI($this->lng->txt("institution"), "admin_institution");
        $ti->setMaxLength(200);
        $ti->setSize(40);
        $ti->setValue($ilSetting->get("admin_institution"));
        $this->form->addItem($ti);

        // street
        $ti = new ilTextInputGUI($this->lng->txt("street"), "admin_street");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        //$ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_street"));
        $this->form->addItem($ti);

        // zip code
        $ti = new ilTextInputGUI($this->lng->txt("zipcode"), "admin_zipcode");
        $ti->setMaxLength(10);
        $ti->setSize(5);
        //$ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_zipcode"));
        $this->form->addItem($ti);

        // city
        $ti = new ilTextInputGUI($this->lng->txt("city"), "admin_city");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        //$ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_city"));
        $this->form->addItem($ti);

        // country
        $ti = new ilTextInputGUI($this->lng->txt("country"), "admin_country");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        //$ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_country"));
        $this->form->addItem($ti);

        // phone
        $ti = new ilTextInputGUI($this->lng->txt("phone"), "admin_phone");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        //$ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_phone"));
        $this->form->addItem($ti);

        // email
        $ti = new ilEMailInputGUI($this->lng->txt("email"), "admin_email");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setRequired(true);
        $ti->allowRFC822(true);
        $ti->setValue($ilSetting->get("admin_email"));
        $this->form->addItem($ti);

        // System support contacts
        $ti = new ilTextInputGUI($this->lng->txt("adm_support_contacts"), "adm_support_contacts");
        $ti->setMaxLength(500);
        $ti->setValue(ilSystemSupportContacts::getList());
        //$ti->setSize();
        $ti->setInfo($this->lng->txt("adm_support_contacts_info"));
        $this->form->addItem($ti);

        // Accessibility support contacts
        $ti = new ilTextInputGUI($this->lng->txt("adm_accessibility_contacts"), "accessibility_support_contacts");
        $ti->setMaxLength(500);
        $ti->setValue(ilAccessibilitySupportContacts::getList());
        //$ti->setSize();
        $ti->setInfo($this->lng->txt("adm_accessibility_contacts_info"));
        $this->form->addItem($ti);

        $this->form->addCommandButton("saveContactInformation", $lng->txt("save"));

        $this->form->setTitle($lng->txt("contact_data"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    /**
    * Save contact information form
    *
    */
    public function saveContactInformationObject(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(self::class, "showContactInformation");
        }

        $this->initContactInformationForm();
        if ($this->form->checkInput()) {
            $fs = array("admin_firstname", "admin_lastname", "admin_title", "admin_position",
                "admin_institution", "admin_street", "admin_zipcode", "admin_city",
                "admin_country", "admin_phone", "admin_email");
            foreach ($fs as $f) {
                $ilSetting->set($f, $_POST[$f]);
            }

            // System support contacts
            ilSystemSupportContacts::setList($_POST["adm_support_contacts"]);

            // Accessibility support contacts
            ilAccessibilitySupportContacts::setList($_POST["accessibility_support_contacts"]);

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "showContactInformation");
        } else {
            $this->setGeneralSettingsSubTabs("contact_data");
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }

    //
    //
    // Java Server
    //
    //

    /**
    * Show Java Server Settings
    */
    public function showJavaServerObject(): void
    {
        $tpl = $this->tpl;

        $tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.java_settings.html', 'components/ILIAS/SystemFolder');

        $GLOBALS['lng']->loadLanguageModule('search');

        $this->initJavaServerForm();
        $this->setServerInfoSubTabs("java_server");
        $tpl->setVariable('SETTINGS_TABLE', $this->form->getHTML());
    }

    /**
     * Create a server ini file
     * @return
     */
    public function createJavaServerIniObject(): void
    {
        $this->setGeneralSettingsSubTabs('java_server');
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
    * Init java server form.
    */
    public function initJavaServerForm(): void
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'saveJavaServer'));

        // pdf fonts
        $pdf = new ilFormSectionHeaderGUI();
        $pdf->setTitle($this->lng->txt('rpc_pdf_generation'));
        $this->form->addItem($pdf);

        $pdf_font = new ilTextInputGUI($this->lng->txt('rpc_pdf_font'), 'rpc_pdf_font');
        $pdf_font->setInfo($this->lng->txt('rpc_pdf_font_info'));
        $pdf_font->setSize(64);
        $pdf_font->setMaxLength(1024);
        $pdf_font->setRequired(true);
        $pdf_font->setValue(
            $ilSetting->get('rpc_pdf_font', 'Helvetica, unifont')
        );
        $this->form->addItem($pdf_font);

        // save and cancel commands
        $this->form->addCommandButton("saveJavaServer", $lng->txt("save"));
    }

    /**
    * Save java server form
    *
    */
    public function saveJavaServerObject(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $this->initJavaServerForm();
        if ($this->form->checkInput()) {
            $ilSetting->set('rpc_pdf_font', ilUtil::stripSlashes($_POST['rpc_pdf_font']));
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "showJavaServer");

            // TODO check settings, ping server
        } else {
            $this->setGeneralSettingsSubTabs("java_server");
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }

    /**
     * goto target group
     */
    public static function _goto(): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();

        $a_target = SYSTEM_FOLDER_ID;

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            ilUtil::redirect("ilias.php?baseClass=ilAdministrationGUI");
            exit;
        } else {
            if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
                $main_tpl->setOnScreenMessage('failure', sprintf(
                    $lng->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                ), true);
                ilObjectGUI::_gotoRepositoryRoot();
            }
        }
        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }

    /**
     *
     */
    protected function showVcsInformationObject(): void
    {
        $vcInfo = [];

        foreach ([new ilGitInformation()] as $vc) {
            $html = $vc->getInformationAsHtml();
            if ($html) {
                $vcInfo[] = $html;
            }
        }

        if ($vcInfo !== []) {
            $this->tpl->setOnScreenMessage('info', implode("<br />", $vcInfo));
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('vc_information_not_determined'));
        }

        $this->showServerInfoObject();
    }
}
