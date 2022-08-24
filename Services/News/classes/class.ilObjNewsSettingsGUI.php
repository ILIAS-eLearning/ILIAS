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

/**
 * News Settings.
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjNewsSettingsGUI: ilPermissionGUI
 */
class ilObjNewsSettingsGUI extends ilObjectGUI
{
    protected ilNewsCache $acache;

    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        $this->type = 'nwss';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('news');
        $this->lng->loadLanguageModule('feed');
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd === null || $cmd === '' || $cmd === 'view') {
                    $cmd = "editSettings";
                }

                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs(): void
    {
        $rbacsystem = $this->rbac_system;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "news_edit_news_settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                ["editSettings", "view"]
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                [],
                'ilpermissiongui'
            );
        }
    }

    public function editSettings(): void
    {
        $form = $this->getSettingsForm();
        $this->tpl->setContent($form->getHTML());
    }

    public function getSettingsForm(): ilPropertyFormGUI
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

        $allow_shorter_periods = $news_set->get("allow_shorter_periods");
        $allow_longer_periods = $news_set->get("allow_longer_periods");

        $rss_period = ilNewsItem::_lookupRSSPeriod();

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
        $cb_prop->setChecked((bool) $enable_internal_news);
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
        $nr_opts = [50 => 50, 100 => 100, 200 => 200];
        $nr_sel = new ilSelectInputGUI(
            $lng->txt("news_nr_of_items"),
            "news_max_items"
        );
        $nr_sel->setInfo($lng->txt("news_nr_of_items_info"));
        $nr_sel->setOptions($nr_opts);
        $nr_sel->setValue((string) $news_set->get("max_items"));
        $form->addItem($nr_sel);

        // Access Cache
        $min_opts = [0 => 0, 1 => 1, 2 => 2, 5 => 5, 10 => 10, 20 => 20, 30 => 30, 60 => 60];
        $min_sel = new ilSelectInputGUI(
            $lng->txt("news_cache"),
            "news_acc_cache_mins"
        );
        $min_sel->setInfo($lng->txt("news_cache_info"));
        $min_sel->setOptions($min_opts);
        $min_sel->setValue((string) $news_set->get("acc_cache_mins"));
        $form->addItem($min_sel);

        // PD News Period
        $per_opts = [
            7 => "1 " . $lng->txt("week"),
            30 => "1 " . $lng->txt("month"),
            366 => "1 " . $lng->txt("year")
        ];
        $per_sel = new ilSelectInputGUI(
            $lng->txt("news_pd_period"),
            "news_pd_period"
        );
        $per_sel->setInfo($lng->txt("news_pd_period_info"));
        $per_sel->setOptions($per_opts);
        $per_sel->setValue((string) ilNewsItem::_lookupDefaultPDPeriod());
        $form->addItem($per_sel);

        // Allow user to choose lower values
        $sp_prop = new ilCheckboxInputGUI(
            $lng->txt("news_allow_shorter_periods"),
            "allow_shorter_periods"
        );
        $sp_prop->setValue("1");
        $sp_prop->setInfo($lng->txt("news_allow_shorter_periods_info"));
        $sp_prop->setChecked((bool) $allow_shorter_periods);
        $form->addItem($sp_prop);

        // Allow user to choose higher values
        $lp_prop = new ilCheckboxInputGUI(
            $lng->txt("news_allow_longer_periods"),
            "allow_longer_periods"
        );
        $lp_prop->setValue("1");
        $lp_prop->setInfo($lng->txt("news_allow_longer_periods_info"));
        $lp_prop->setChecked((bool) $allow_longer_periods);
        $form->addItem($lp_prop);

        // Enable rss for internal news
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("news_enable_internal_rss"),
            "enable_internal_rss"
        );
        $cb_prop->setValue("1");
        $cb_prop->setInfo($lng->txt("news_enable_internal_rss_info"));
        $cb_prop->setChecked((bool) $enable_internal_rss);

        // RSS News Period
        $rssp_opts = [
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
        ];
        $rssp_sel = new ilSelectInputGUI(
            $lng->txt("news_rss_period"),
            "news_rss_period"
        );
        $rssp_sel->setOptions($rssp_opts);
        $rssp_sel->setValue($rss_period);
        $cb_prop->addSubItem($rssp_sel);

        // Section Header: RSS
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("news_rss"));
        $form->addItem($sh);

        // title format for rss entries
        $options = [
            "" => $lng->txt("news_rss_title_format_obj_news"),
            "news_obj" => $lng->txt("news_rss_title_format_news_obj"),
        ];
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
        $cb_prop->setChecked((bool) $enable_private_feed);
        $form->addItem($cb_prop);

        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            // command buttons
            $form->addCommandButton("saveSettings", $lng->txt("save"));
            $form->addCommandButton("view", $lng->txt("cancel"));
        }
        return $form;
    }

    public function saveSettings(): void
    {
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $ilCtrl->redirect($this, "view");
        }

        // empty news cache
        $this->acache = new ilNewsCache();
        $this->acache->deleteAllEntries();

        $news_set = new ilSetting("news");
        $feed_set = new ilSetting("feed");

        $form = $this->getSettingsForm();

        if ($form->checkInput()) {
            $ilSetting->set("block_activated_news", $form->getInput("enable_internal_news"));
            $ilSetting->set("block_activated_pdnews", $form->getInput("enable_internal_news"));
            $news_set->set("enable_rss_for_internal", $form->getInput("enable_internal_rss"));
            $news_set->set("max_items", $form->getInput("news_max_items"));
            $news_set->set("acc_cache_mins", $form->getInput("news_acc_cache_mins"));
            $news_set->set("pd_period", $form->getInput("news_pd_period"));
            $news_set->set("default_visibility", $form->getInput("news_default_visibility"));
            $news_set->set("allow_shorter_periods", $form->getInput("allow_shorter_periods"));
            $news_set->set("allow_longer_periods", $form->getInput("allow_longer_periods"));
            $news_set->set("rss_period", $form->getInput("news_rss_period"));
            $news_set->set("rss_title_format", $form->getInput("rss_title_format"));

            if ($form->getInput("enable_internal_rss")) {
                $news_set->set("enable_private_feed", $form->getInput("enable_private_feed"));
            } else {
                $news_set->set("enable_private_feed", '0');
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        }

        $ilCtrl->redirect($this, "view");
    }
}
