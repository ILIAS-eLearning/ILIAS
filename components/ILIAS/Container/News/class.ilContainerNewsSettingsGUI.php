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
 *  News settings for containers
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerNewsSettingsGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilSetting $setting;
    protected ilAccessHandler $access;
    protected ilTree $tree;
    protected ilObjectGUI $parent_gui;
    protected ilObject $object;
    protected bool $has_timeline = false;
    protected bool $has_cron_notifications = false;
    protected bool $has_hide_by_date = false;
    protected bool $has_public_notification = false;
    protected bool $has_block_forced = false;

    public function __construct(ilObjectGUI $a_parent_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("news");
        $this->tpl = $DIC["tpl"];
        $this->setting = $DIC["ilSetting"];
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->parent_gui = $a_parent_gui;
        $this->object = $this->parent_gui->getObject();

        $this->initDefaultOptions();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("show");

        switch ($next_class) {
            default:
                if (in_array($cmd, ["show", "save"])) {
                    $this->checkPermission('write');
                    $this->$cmd();
                }
        }
    }

    protected function checkPermission(string $perm): void
    {
        if ($this->access->checkAccess($perm, '', $this->object->getRefId())) {
            return;
        }

        $this->tpl->setOnScreenMessage(
            ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
            $this->lng->txt('msg_no_perm_read'),
            true
        );
        $parent_ref_id = $this->tree->getParentId($this->object->getRefId());
        $this->ctrl->redirectToURL($parent_ref_id > 0 ? ilLink::_getLink($parent_ref_id) : 'login.php?cmd=force_login');
    }

    public function show(): void
    {
        $form = $this->initForm();
        $this->tpl->setContent($form->getHTML());
    }

    public function initForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        //from crs/grp/cat settings - additional feature - news

        if ($this->setting->get('block_activated_news')) {
            $news = new ilCheckboxInputGUI($this->lng->txt('news_news_block'), ilObjectServiceSettingsGUI::NEWS_VISIBILITY);
            $news->setValue('1');
            if ($this->has_block_forced) {
                $news->setChecked(true);
                $news->setDisabled(true);
            } else {
                $news->setChecked($this->object->getNewsBlockActivated());
            }
            $news->setInfo($this->lng->txt('obj_tool_setting_news_info'));
            ilNewsForContextBlockGUI::addToSettingsForm($news);
            $form->addItem($news);
        }

        // Timeline (courses and groups)
        if ($this->has_timeline) {
            // timeline
            $cb = new ilCheckboxInputGUI($this->lng->txt("cont_news_timeline"), "news_timeline");
            $cb->setInfo($this->lng->txt("cont_news_timeline_info"));
            $cb->setChecked($this->object->getNewsTimeline());
            $form->addItem($cb);

            // ...timeline: auto entries
            $cb2 = new ilCheckboxInputGUI($this->lng->txt("cont_news_timeline_auto_entries"), "news_timeline_auto_entries");
            $cb2->setInfo($this->lng->txt("cont_news_timeline_auto_entries_info"));
            $cb2->setChecked($this->object->getNewsTimelineAutoEntries());
            $cb->addSubItem($cb2);

            // ...timeline: landing page
            $cb2 = new ilCheckboxInputGUI($this->lng->txt("cont_news_timeline_landing_page"), "news_timeline_landing_page");
            $cb2->setInfo($this->lng->txt("cont_news_timeline_landing_page_info"));
            $cb2->setChecked($this->object->getNewsTimelineLandingPage());
            $cb->addSubItem($cb2);
        }

        // Cron Notifications (courses and groups)
        if ($this->has_cron_notifications && in_array(ilObject::_lookupType($this->object->getId()), ['crs', 'grp'])) {
            $ref_ids = ilObject::_getAllReferences($this->object->getId());
            $ref_id = array_pop($ref_ids);
            ilMembershipNotifications::addToSettingsForm($ref_id, $form, null);
        }

        $block_id = $this->ctrl->getContextObjId();

        $hide_news_per_date = ilBlockSetting::_lookup(
            ilNewsForContextBlockGUI::$block_type,
            "hide_news_per_date",
            0,
            $block_id
        );
        $hide_news_date = ilBlockSetting::_lookup(
            ilNewsForContextBlockGUI::$block_type,
            "hide_news_date",
            0,
            $block_id
        );
        $hide_news_mode = ilBlockSetting::_lookup(
            ilNewsForContextBlockGUI::$block_type,
            "hide_news_mode",
            0,
            $block_id
        ) ?? ($hide_news_date != "" ? "per_date" : "global");
        $news_co_period = ilBlockSetting::_lookup(
            ilNewsForContextBlockGUI::$block_type,
            "news_co_period",
            0,
            $block_id
        ) ?? "";

        if ($hide_news_date != "") {
            $hide_news_date = explode(" ", $hide_news_date);
        }

        // Hide news: none, per date, or by period (courses, groups and categories)
        if ($this->has_hide_by_date) {
            $radio = new ilRadioGroupInputGUI($this->lng->txt("news_hide_news_mode"), "hide_news_mode");
            $radio->setValue($hide_news_mode);

            $opt_global = new ilRadioOption($this->lng->txt("news_hide_news_global"), "global");
            $radio->addOption($opt_global);

            $opt_none = new ilRadioOption($this->lng->txt("news_hide_news_none"), "none");
            $radio->addOption($opt_none);

            $opt_per_date = new ilRadioOption($this->lng->txt("news_hide_news_per_date"), "per_date");
            $opt_per_date->setInfo($this->lng->txt("news_hide_news_per_date_info"));
            $dt_prop = new ilDateTimeInputGUI($this->lng->txt("news_hide_news_date"), "hide_news_date");
            $dt_prop->setRequired(true);
            if (is_array($hide_news_date) && count($hide_news_date) >= 2) {
                $dt_prop->setDate(new ilDateTime($hide_news_date[0] . ' ' . ($hide_news_date[1] ?? "12:00:00"), IL_CAL_DATETIME));
            }
            $dt_prop->setShowTime(true);
            $opt_per_date->addSubItem($dt_prop);
            $radio->addOption($opt_per_date);

            $opt_by_period = new ilRadioOption($this->lng->txt("news_hide_news_by_period"), "by_period");
            $opt_by_period->setInfo($this->lng->txt("news_hide_news_by_period_info"));
            $per_sel = new ilSelectInputGUI($this->lng->txt("news_co_period"), "news_co_period");
            $per_sel->setRequired(true);
            $per_sel->setInfo($this->lng->txt("news_co_period_info"));
            $per_sel->setOptions([
                7 => "1 {$this->lng->txt("week")}",
                30 => "1 {$this->lng->txt("month")}",
                366 => "1 {$this->lng->txt("year")}"
            ]);
            $per_sel->setValue($news_co_period);
            $opt_by_period->addSubItem($per_sel);
            $radio->addOption($opt_by_period);

            $form->addItem($radio);
        }

        // public notifications (forums)
        if ($this->has_public_notification) {
            $public = ilBlockSetting::_lookup("news", "public_notifications", 0, $block_id);

            $ch = new ilCheckboxInputGUI(
                $this->lng->txt("news_notifications_public"),
                "public_notifications"
            );
            $ch->setInfo($this->lng->txt("news_notifications_public_info"));
            $ch->setChecked((bool) $public);
            $form->addItem($ch);
        }

        $form->setTitle($this->lng->txt("cont_news_settings"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton("save", $this->lng->txt("save"));

        return $form;
    }

    public function save(): void
    {
        $form = $this->initForm();
        if ($form->checkInput()) {
            //non container objects force this news block (forums etc.)
            if (!$this->has_block_forced) {
                $this->object->setNewsBlockActivated($form->getInput(ilObjectServiceSettingsGUI::NEWS_VISIBILITY));
            }
            if ($this->has_timeline) {
                $this->object->setNewsTimeline($form->getInput("news_timeline"));
                $this->object->setNewsTimelineAutoEntries($form->getInput("news_timeline_auto_entries"));
                $this->object->setNewsTimelineLandingPage($form->getInput("news_timeline_landing_page"));
            }
            if ($this->setting->get('block_activated_news')) {
                //save contextblock settings
                $context_block_settings = [
                    "public_feed" => $form->getInput("notifications_public_feed") ?? "",
                    "default_visibility" => $form->getInput("default_visibility")
                ];
                if ($this->has_public_notification) {
                    $context_block_settings["public_notifications"] =
                        $form->getInput('public_notifications');
                }

                if ($this->has_hide_by_date) {
                    $context_block_settings["hide_news_mode"] = $form->getInput("hide_news_mode");
                    switch ($context_block_settings["hide_news_mode"]) {
                        case "per_date":
                            $hd = $form->getItemByPostVar("hide_news_date");
                            if ($hd instanceof ilDateTimeInputGUI) {
                                $hide_date = $hd->getDate();
                                if (!$hide_date instanceof ilDateTime) {
                                    $hd->setAlert($this->lng->txt("msg_input_is_required"));
                                    $form->setValuesByPost();
                                    $this->tpl->setContent($form->getHTML());
                                    return;
                                }
                                $context_block_settings["hide_news_per_date"] = "1";
                                $context_block_settings["hide_news_date"] = $hide_date->get(IL_CAL_DATETIME);
                            }
                            $context_block_settings["news_co_period"] = "";
                            break;
                        case "by_period":
                            $context_block_settings["hide_news_per_date"] = "0";
                            $context_block_settings["hide_news_date"] = "";
                            $context_block_settings["news_co_period"] = $form->getInput("news_co_period");
                            break;
                        case "global":
                        default:
                            $context_block_settings["hide_news_per_date"] = "0";
                            $context_block_settings["hide_news_date"] = "";
                            $context_block_settings["news_co_period"] = "";
                            break;
                    }
                }

                ilNewsForContextBlockGUI::writeSettings($context_block_settings);

                if (in_array(ilObject::_lookupType($this->object->getId()), ['crs', 'grp'])) {
                    $ref_ids = ilObject::_getAllReferences($this->object->getId());
                    $ref_id = array_pop($ref_ids);

                    ilMembershipNotifications::importFromForm($ref_id, $form);
                }
            }

            $this->object->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "");
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }

    // Set all possible news options as false;
    public function initDefaultOptions(): void
    {
        $this->has_timeline = false;
        $this->has_cron_notifications = false;
        $this->has_hide_by_date = false;
        $this->has_block_forced = false;
    }

    public function setTimeline(bool $a_value): void
    {
        $this->has_timeline = $a_value;
    }

    public function getTimeline(): bool
    {
        return $this->has_timeline;
    }

    public function setCronNotifications(bool $a_value): void
    {
        $this->has_cron_notifications = $a_value;
    }

    public function getCronNotifications(): bool
    {
        return $this->has_cron_notifications;
    }

    public function setHideByDate(bool $a_value): void
    {
        $this->has_hide_by_date = $a_value;
    }

    public function getHideByDate(): bool
    {
        return $this->has_hide_by_date;
    }

    public function setPublicNotification(bool $a_value): void
    {
        $this->has_public_notification = $a_value;
    }

    public function getPublicNotification(): bool
    {
        return $this->has_public_notification;
    }

    // Set if the repository object has the news block forced
    public function setNewsBlockForced(bool $a_value): void
    {
        $this->has_block_forced = $a_value;
    }

    public function getNewsBlockForced(): bool
    {
        return $this->has_block_forced;
    }
}
