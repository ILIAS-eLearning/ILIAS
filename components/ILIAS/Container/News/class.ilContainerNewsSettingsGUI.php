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
                    $this->$cmd();
                }
        }
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

        // Visibility by date
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

        if ($hide_news_date != "") {
            $hide_news_date = explode(" ", $hide_news_date);
        }

        // Hide news before a date (courses, groups and categories)
        if ($this->has_hide_by_date) {
            //Hide news per date
            $hnpd = new ilCheckboxInputGUI(
                $this->lng->txt("news_hide_news_per_date"),
                "hide_news_per_date"
            );
            $hnpd->setInfo($this->lng->txt("news_hide_news_per_date_info"));
            $hnpd->setChecked((bool) $hide_news_per_date);

            $dt_prop = new ilDateTimeInputGUI($this->lng->txt("news_hide_news_date"), "hide_news_date");
            $dt_prop->setRequired(true);

            if ($hide_news_date != "") {
                $dt_prop->setDate(new ilDateTime($hide_news_date[0] . ' ' . $hide_news_date[1], IL_CAL_DATETIME));
            }

            $dt_prop->setShowTime(true);

            $hnpd->addSubItem($dt_prop);

            $form->addItem($hnpd);
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
                    "default_visibility" => $form->getInput("default_visibility"),
                    "hide_news_per_date" => $form->getInput("hide_news_per_date"),
                    "hide_news_date" => $form->getInput("hide_news_date")
                ];
                if ($this->has_public_notification) {
                    $context_block_settings["public_notifications"] =
                        $form->getInput('public_notifications');
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
