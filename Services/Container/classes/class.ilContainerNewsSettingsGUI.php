<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  News settings for containers
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesContainer
 */
class ilContainerNewsSettingsGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilSetting
     */
    protected $setting;

    /**
     * @var ilObjectGUI
     */
    protected $parent_gui;

    /**
     * @var ilObject
     */
    protected $object;

    /**
     * @var bool
     */
    protected $has_timeline;

    /**
     * @var bool
     */
    protected $has_cron_notifications;

    /**
     * @var bool
     */
    protected $has_hide_by_date;

    /**
     * @var bool
     */
    protected $has_public_notification;

    /**
     * @var bool
     */
    protected $has_block_forced;

    /**
     * Constructor
     */
    public function __construct(ilObjectGUI $a_parent_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("news");
        $this->tpl = $DIC["tpl"];
        $this->setting = $DIC["ilSetting"];
        $this->parent_gui = $a_parent_gui;
        $this->object = $this->parent_gui->object;

        $this->initDefaultOptions();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("show");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("show", "save"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * Show
     *
     * @param
     * @return
     */
    public function show()
    {
        $form = $this->initForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init settings form.
     */
    public function initForm()
    {
        $form = new ilPropertyFormGUI();

        //from crs/grp/cat settings - additional feature - news

        if ($this->setting->get('block_activated_news')) {
            $news = new ilCheckboxInputGUI($this->lng->txt('news_news_block'), ilObjectServiceSettingsGUI::NEWS_VISIBILITY);
            $news->setValue(1);
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
        if ($this->has_cron_notifications) {
            if (in_array(ilObject::_lookupType($this->object->getId()), array('crs', 'grp'))) {
                $ref_id = array_pop(ilObject::_getAllReferences($this->object->getId()));
                include_once 'Services/Membership/classes/class.ilMembershipNotifications.php';
                ilMembershipNotifications::addToSettingsForm($ref_id, $form, null);
            }
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
            $hnpd->setChecked($hide_news_per_date);

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
            $ch->setChecked($public);
            $form->addItem($ch);
        }

        $form->setTitle($this->lng->txt("cont_news_settings"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton("save", $this->lng->txt("save"));

        return $form;
    }

    /**
     * Save settings form
     */
    public function save()
    {
        $form = $this->initForm();
        if ($form->checkInput()) {
            include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
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
                $context_block_settings = array(
                    "public_feed" => $_POST["notifications_public_feed"],
                    "default_visibility" => $_POST["default_visibility"],
                    "hide_news_per_date" => $_POST["hide_news_per_date"],
                    "hide_news_date" => $_POST["hide_news_date"]
                );
                if ($this->has_public_notification) {
                    $context_block_settings["public_notifications"] = $_POST['public_notifications'];
                }

                ilNewsForContextBlockGUI::writeSettings($context_block_settings);

                if (in_array(ilObject::_lookupType($this->object->getId()), array('crs', 'grp'))) {
                    $ref_id = array_pop(ilObject::_getAllReferences($this->object->getId()));

                    include_once "Services/Membership/classes/class.ilMembershipNotifications.php";
                    ilMembershipNotifications::importFromForm($ref_id, $form);
                }
            }

            $this->object->update();
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "");
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
        }
    }

    /**
     * Set all possible news options as false;
     */
    public function initDefaultOptions()
    {
        $this->has_timeline = false;
        $this->has_cron_notifications = false;
        $this->has_hide_by_date = false;
        $this->has_block_forced = false;
    }

    /**
     * Set if the container has timeline or not
     * @param bool $a_value
     */
    public function setTimeline(bool $a_value)
    {
        $this->has_timeline = $a_value;
    }

    /**
     * Get if the container has timeline or not
     * @return bool
     */
    public function getTimeline() : bool
    {
        return $this->has_timeline;
    }

    /**
     * Set if the container has a configurable cron job to send notifications.
     * @param bool $a_value
     */
    public function setCronNotifications(bool $a_value)
    {
        $this->has_cron_notifications = $a_value;
    }

    /**
     * Get if the container has a configurable cron job to send notifications.
     * @return mixed
     */
    public function getCronNotifications() : bool
    {
        return $this->getCronNotifications();
    }

    /**
     * Set if the container can hide news created before a date
     * @param bool $a_value
     */
    public function setHideByDate(bool $a_value)
    {
        $this->has_hide_by_date = $a_value;
    }

    /**
     * Get if the container can hide news created before a date.
     * @return bool
     */
    public function getHideByDate() : bool
    {
        return $this->has_hide_by_date;
    }

    /**
     * Set if this repository object has public notifications
     * @param bool $a_value
     */
    public function setPublicNotification(bool $a_value)
    {
        $this->has_public_notification = $a_value;
    }

    /**
     * Get if this repository object has public notifications available.
     * @return bool
     */
    public function getPublicNotification()
    {
        return $this->has_public_notification;
    }

    /**
     * Set if the News block is forced
     * @param bool $a_value
     */
    public function setNewsBlockForced(bool $a_value)
    {
        $this->has_block_forced = $a_value;
    }

    /**
     * Get if the repository object has the news block forced
     * @return bool
     */
    public function getNewsBlockForced() : bool
    {
        return $this->has_block_forced;
    }
}
