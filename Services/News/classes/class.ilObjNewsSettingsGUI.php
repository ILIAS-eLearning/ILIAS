<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");


/**
* News Settings.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjNewsSettingsGUI: ilPermissionGUI
*
* @ingroup ServicesNews
*/
class ilObjNewsSettingsGUI extends ilObjectGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilSetting
     */
    protected $settings;

    private static $ERROR_MESSAGE;
    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->type = 'nwss';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('news');
        $this->lng->loadLanguageModule('feed');
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;
        $ilAccess = $this->access;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$ilAccess->checkAccess('read', '', $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editSettings";
                }

                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs()
    {
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "news_edit_news_settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    /**
    * Edit news settings.
    */
    public function editSettings()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        
        $news_set = new ilSetting("news");
        $feed_set = new ilSetting("feed");
        
        $enable_internal_news = $ilSetting->get("block_activated_news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");
        $rss_title_format = $news_set->get("rss_title_format");
        $enable_private_feed = $news_set->get("enable_private_feed");
        $news_default_visibility = ($news_set->get("default_visibility") != "")
            ? $news_set->get("default_visibility")
            : "users";
        $disable_repository_feeds = $feed_set->get("disable_rep_feeds");
        $nr_personal_desktop_feeds = $ilSetting->get("block_limit_pdfeed");
        
        $allow_shorter_periods = $news_set->get("allow_shorter_periods");
        $allow_longer_periods = $news_set->get("allow_longer_periods");
    
        include_once("./Services/News/classes/class.ilNewsItem.php");
        $rss_period = ilNewsItem::_lookupRSSPeriod();
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("news_settings"));
        
        // Enable internal news
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("news_enable_internal_news"),
            "enable_internal_news"
        );
        $cb_prop->setValue("1");
        $cb_prop->setInfo($lng->txt("news_enable_internal_news_info"));
        $cb_prop->setChecked($enable_internal_news);
        $form->addItem($cb_prop);

        // Default Visibility
        $radio_group = new ilRadioGroupInputGUI($lng->txt("news_default_visibility"), "news_default_visibility");
        $radio_option = new ilRadioOption($lng->txt("news_visibility_users"), "users");
        $radio_group->addOption($radio_option);
        $radio_option = new ilRadioOption($lng->txt("news_visibility_public"), "public");
        $radio_group->addOption($radio_option);
        $radio_group->setInfo($lng->txt("news_news_item_visibility_info"));
        $radio_group->setRequired(false);
        $radio_group->setValue($news_default_visibility);
        $form->addItem($radio_group);

        // Number of news items per object
        $nr_opts = array(50 => 50, 100 => 100, 200 => 200);
        $nr_sel = new ilSelectInputGUI(
            $lng->txt("news_nr_of_items"),
            "news_max_items"
        );
        $nr_sel->setInfo($lng->txt("news_nr_of_items_info"));
        $nr_sel->setOptions($nr_opts);
        $nr_sel->setValue($news_set->get("max_items"));
        $form->addItem($nr_sel);

        // Access Cache
        $min_opts = array(0 => 0, 1 => 1, 2 => 2, 5 => 5, 10 => 10, 20 => 20, 30 => 30, 60 => 60);
        $min_sel = new ilSelectInputGUI(
            $lng->txt("news_cache"),
            "news_acc_cache_mins"
        );
        $min_sel->setInfo($lng->txt("news_cache_info"));
        $min_sel->setOptions($min_opts);
        $min_sel->setValue($news_set->get("acc_cache_mins"));
        $form->addItem($min_sel);
        
        // PD News Period
        $per_opts = array(
            2 => "2 " . $lng->txt("days"),
            3 => "3 " . $lng->txt("days"),
            5 => "5 " . $lng->txt("days"),
            7 => "1 " . $lng->txt("week"),
            14 => "2 " . $lng->txt("weeks"),
            30 => "1 " . $lng->txt("month"),
            60 => "2 " . $lng->txt("months"),
            120 => "4 " . $lng->txt("months"),
            180 => "6 " . $lng->txt("months"),
            366 =>  "1 " . $lng->txt("year"));
        $per_sel = new ilSelectInputGUI(
            $lng->txt("news_pd_period"),
            "news_pd_period"
        );
        $per_sel->setInfo($lng->txt("news_pd_period_info"));
        $per_sel->setOptions($per_opts);
        $per_sel->setValue((int) ilNewsItem::_lookupDefaultPDPeriod());
        $form->addItem($per_sel);

        // Allow user to choose lower values
        $sp_prop = new ilCheckboxInputGUI(
            $lng->txt("news_allow_shorter_periods"),
            "allow_shorter_periods"
        );
        $sp_prop->setValue("1");
        $sp_prop->setInfo($lng->txt("news_allow_shorter_periods_info"));
        $sp_prop->setChecked($allow_shorter_periods);
        $form->addItem($sp_prop);

        // Allow user to choose higher values
        $lp_prop = new ilCheckboxInputGUI(
            $lng->txt("news_allow_longer_periods"),
            "allow_longer_periods"
        );
        $lp_prop->setValue("1");
        $lp_prop->setInfo($lng->txt("news_allow_longer_periods_info"));
        $lp_prop->setChecked($allow_longer_periods);
        $form->addItem($lp_prop);

        // Enable rss for internal news
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("news_enable_internal_rss"),
            "enable_internal_rss"
        );
        $cb_prop->setValue("1");
        $cb_prop->setInfo($lng->txt("news_enable_internal_rss_info"));
        $cb_prop->setChecked($enable_internal_rss);

        // RSS News Period
        $rssp_opts = array(
            2 => "2 " . $lng->txt("days"),
            3 => "3 " . $lng->txt("days"),
            5 => "5 " . $lng->txt("days"),
            7 => "1 " . $lng->txt("week"),
            14 => "2 " . $lng->txt("weeks"),
            30 => "1 " . $lng->txt("month"),
            60 => "2 " . $lng->txt("months"),
            120 => "4 " . $lng->txt("months"),
            180 => "6 " . $lng->txt("months"),
            365 => "1 " . $lng->txt("year")
        );
        $rssp_sel = new ilSelectInputGUI(
            $lng->txt("news_rss_period"),
            "news_rss_period"
        );
        $rssp_sel->setOptions($rssp_opts);
        $rssp_sel->setValue((int) $rss_period);
        $cb_prop->addSubItem($rssp_sel);

        // Section Header: RSS
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("news_rss"));
        $form->addItem($sh);

        // title format for rss entries
        $options = array(
            "" => $lng->txt("news_rss_title_format_obj_news"),
            "news_obj" => $lng->txt("news_rss_title_format_news_obj"),
        );
        $si = new ilSelectInputGUI($lng->txt("news_rss_title_format"), "rss_title_format");
        $si->setOptions($options);
        $si->setValue($rss_title_format);
        $cb_prop->addSubItem($si);

        $form->addItem($cb_prop);

        // Enable private news feed
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("news_enable_private_feed"),
            "enable_private_feed"
        );
        $cb_prop->setValue("1");
        $cb_prop->setInfo($lng->txt("news_enable_private_feed_info"));
        $cb_prop->setChecked($enable_private_feed);
        $form->addItem($cb_prop);


        // Section Header: External Web Feeds Settings
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("feed_settings"));
        $form->addItem($sh);

        // Number of External Feeds on personal desktop
        $sel = new ilSelectInputGUI($lng->txt("feed_nr_pd_feeds"), "nr_pd_feeds");
        $sel->setInfo($lng->txt("feed_nr_pd_feeds_info"));
        $sel->setOptions(array(0 => "0",
            1 => "1",
            2 => "2",
            3 => "3",
            4 => "4",
            5 => "5"));
        $sel->setValue($nr_personal_desktop_feeds);
        $form->addItem($sel);

        // Disable External Web Feeds in catetegories
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("feed_disable_rep_feeds"),
            "disable_repository_feeds"
        );
        $cb_prop->setValue("1");
        $cb_prop->setInfo($lng->txt("feed_disable_rep_feeds_info"));
        $cb_prop->setChecked($disable_repository_feeds);
        $form->addItem($cb_prop);

        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            // command buttons
            $form->addCommandButton("saveSettings", $lng->txt("save"));
            $form->addCommandButton("view", $lng->txt("cancel"));
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
    * Save news and external webfeeds settings
    */
    public function saveSettings()
    {
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        
        if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $ilCtrl->redirect($this, "view");
        }
        
        // empty news cache
        include_once("./Services/News/classes/class.ilNewsCache.php");
        $this->acache = new ilNewsCache();
        $this->acache->deleteAllEntries();
        
        $news_set = new ilSetting("news");
        $feed_set = new ilSetting("feed");



        $ilSetting->set("block_activated_news", $_POST["enable_internal_news"]);
        $ilSetting->set("block_activated_pdnews", $_POST["enable_internal_news"]);
        $news_set->set("enable_rss_for_internal", $_POST["enable_internal_rss"]);
        $news_set->set("max_items", $_POST["news_max_items"]);
        $news_set->set("acc_cache_mins", $_POST["news_acc_cache_mins"]);
        $news_set->set("pd_period", $_POST["news_pd_period"]);
        $news_set->set("default_visibility", $_POST["news_default_visibility"]);
        $news_set->set("allow_shorter_periods", $_POST["allow_shorter_periods"]);
        $news_set->set("allow_longer_periods", $_POST["allow_longer_periods"]);
        $news_set->set("rss_period", $_POST["news_rss_period"]);
        $news_set->set("rss_title_format", $_POST["rss_title_format"]);
        
        $feed_set->set("disable_rep_feeds", $_POST["disable_repository_feeds"]);
        $ilSetting->set("block_limit_pdfeed", $_POST["nr_pd_feeds"]);
        if ($_POST["nr_pd_feeds"] > 0) {
            $ilSetting->set("block_activated_pdfeed", 1);
        } else {
            $ilSetting->set("block_activated_pdfeed", 0);
        }

        if ($_POST["enable_internal_rss"]!=0) {
            $news_set->set("enable_private_feed", $_POST["enable_private_feed"]);
        } else {
            $news_set->set("enable_private_feed", 0);
        }
        
        ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
        
        $ilCtrl->redirect($this, "view");
    }
}
