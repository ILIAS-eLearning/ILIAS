<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for service settings (calendar, notes, comments)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjectServiceSettingsGUI:
 * @ingroup ServicesObject
 */
class ilObjectServiceSettingsGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    // unfortunately the following constants are not stored
    // in a non-GUI class, other classes are currently directly
    // accessing these, see ilObjectDataSet (changes should be
    // made there accordingly)

    const CALENDAR_VISIBILITY = 'cont_show_calendar';
    const NEWS_VISIBILITY = 'cont_show_news';
    const USE_NEWS = 'cont_use_news';
    const AUTO_RATING_NEW_OBJECTS = 'cont_auto_rate_new_obj';
    const INFO_TAB_VISIBILITY = 'cont_show_info_tab';
    const TAXONOMIES = 'cont_taxonomies';
    const TAG_CLOUD = 'cont_tag_cloud';
    const CUSTOM_METADATA = 'cont_custom_md';
    const BADGES = 'cont_badges';
    const ORGU_POSITION_ACCESS = 'obj_orgunit_positions';
    const SKILLS = 'cont_skills';
    
    private $gui = null;
    private $modes = array();
    private $obj_id = 0;
    
    /**
     * Constructor
     * @param type $a_parent_gui
     */
    public function __construct($a_parent_gui, $a_obj_id, $a_modes)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->gui = $a_parent_gui;
        $this->modes = $a_modes;
        $this->obj_id = $a_obj_id;
    }
    
    
    
    /**
     * Control class handling
     * @return
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd('editSettings');
        
        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }
    
    /**
     * Init service settings form
     * @param ilPropertyFormGUI $form
     * @param type $services
     */
    public static function initServiceSettingsForm($a_obj_id, ilPropertyFormGUI $form, $services)
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $lng->loadLanguageModule("obj");

        // info tab
        if (in_array(self::INFO_TAB_VISIBILITY, $services)) {
            $info = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_info_tab'), self::INFO_TAB_VISIBILITY);
            $info->setValue(1);
            $info->setChecked(ilContainer::_lookupContainerSetting(
                $a_obj_id,
                self::INFO_TAB_VISIBILITY,
                true
            ));
            //$info->setOptionTitle($lng->txt('obj_tool_setting_info_tab'));
            $info->setInfo($lng->txt('obj_tool_setting_info_tab_info'));
            $form->addItem($info);
        }
        
        // calendar
        if (in_array(self::CALENDAR_VISIBILITY, $services)) {
            include_once './Services/Calendar/classes/class.ilObjCalendarSettings.php';
            if (ilCalendarSettings::_getInstance()->isEnabled()) {
                // Container tools (calendar, news, ... activation)
                $cal = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_calendar'), self::CALENDAR_VISIBILITY);
                $cal->setValue(1);
                include_once './Services/Calendar/classes/class.ilObjCalendarSettings.php';
                $cal->setChecked(ilCalendarSettings::lookupCalendarActivated($a_obj_id));
                //$cal->setOptionTitle($lng->txt('obj_tool_setting_calendar'));
                $cal->setInfo($lng->txt('obj_tool_setting_calendar_info'));
                $form->addItem($cal);
            }
        }
        
        // news
        if (in_array(self::USE_NEWS, $services)) {
            $news = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_use_news'), self::USE_NEWS);
            $news->setValue(1);
            $checked = ilContainer::_lookupContainerSetting(
                $a_obj_id,
                self::USE_NEWS,
                true
            );
            $news->setChecked($checked);
            $info = $lng->txt('obj_tool_setting_use_news_info');
            if ($checked) {
                $info .= " <a href='" . $ilCtrl->getLinkTargetByClass("ilcontainernewssettingsgui", "") .
                    "'>» " . $lng->txt('obj_tool_setting_use_news_open_settings') . "</a>";
            }
            $news->setInfo($info);
            $form->addItem($news);
        }
        if (in_array(self::NEWS_VISIBILITY, $services)) {
            if ($ilSetting->get('block_activated_news')) {
                // Container tools (calendar, news, ... activation)
                $news = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_news'), self::NEWS_VISIBILITY);
                $news->setValue(1);
                $news->setChecked(ilContainer::_lookupContainerSetting(
                    $a_obj_id,
                    self::NEWS_VISIBILITY,
                    $ilSetting->get('block_activated_news', true)
                ));
                //$news->setOptionTitle($lng->txt('obj_tool_setting_news'));
                $news->setInfo($lng->txt('obj_tool_setting_news_info'));
                $form->addItem($news);
                
                if (in_array(ilObject::_lookupType($a_obj_id), array('crs', 'grp'))) {
                    $ref_id = array_pop(ilObject::_getAllReferences($a_obj_id));
                    
                    include_once 'Services/Membership/classes/class.ilMembershipNotifications.php';
                    ilMembershipNotifications::addToSettingsForm($ref_id, null, $news);
                }
            }
        }
        
        // (local) custom metadata
        if (in_array(self::CUSTOM_METADATA, $services)) {
            $md = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_custom_metadata'), self::CUSTOM_METADATA);
            $md->setInfo($lng->txt('obj_tool_setting_custom_metadata_info'));
            $md->setValue(1);
            $md->setChecked(ilContainer::_lookupContainerSetting(
                $a_obj_id,
                self::CUSTOM_METADATA,
                false
            ));
            $form->addItem($md);
        }
                
        // tag cloud
        if (in_array(self::TAG_CLOUD, $services)) {
            $tags_active = new ilSetting("tags");
            if ($tags_active->get("enable", false)) {
                $tag = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_tag_cloud'), self::TAG_CLOUD);
                $tag->setInfo($lng->txt('obj_tool_setting_tag_cloud_info'));
                $tag->setValue(1);
                $tag->setChecked(ilContainer::_lookupContainerSetting(
                    $a_obj_id,
                    self::TAG_CLOUD,
                    false
                ));
                $form->addItem($tag);
            }
        }

        // taxonomies
        if (in_array(self::TAXONOMIES, $services)) {
            $tax = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_taxonomies'), self::TAXONOMIES);
            $tax->setValue(1);
            $tax->setChecked(ilContainer::_lookupContainerSetting(
                $a_obj_id,
                self::TAXONOMIES,
                false
            ));
            $form->addItem($tax);
        }
        
        // auto rating
        if (in_array(self::AUTO_RATING_NEW_OBJECTS, $services)) {
            $lng->loadLanguageModule("rating");
            
            // auto rating for new objects
            $rate = new ilCheckboxInputGUI($lng->txt('rating_new_objects_auto'), self::AUTO_RATING_NEW_OBJECTS);
            $rate->setValue(1);
            //$rate->setOptionTitle($lng->txt('rating_new_objects_auto'));
            $rate->setInfo($lng->txt('rating_new_objects_auto_info'));
            $rate->setChecked(ilContainer::_lookupContainerSetting(
                $a_obj_id,
                self::AUTO_RATING_NEW_OBJECTS,
                false
            ));
            $form->addItem($rate);
        }
        
        // badges
        if (in_array(self::BADGES, $services)) {
            include_once 'Services/Badge/classes/class.ilBadgeHandler.php';
            if (ilBadgeHandler::getInstance()->isActive()) {
                $bdg = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_badges'), self::BADGES);
                $bdg->setInfo($lng->txt('obj_tool_setting_badges_info'));
                $bdg->setValue(1);
                $bdg->setChecked(ilContainer::_lookupContainerSetting(
                    $a_obj_id,
                    self::BADGES,
                    false
                ));
                $form->addItem($bdg);
            }
        }
        if (in_array(self::ORGU_POSITION_ACCESS, $services)) {
            $position_settings = ilOrgUnitGlobalSettings::getInstance()->getObjectPositionSettingsByType(
                ilObject::_lookupType($a_obj_id)
            );
            if (
                $position_settings->isActive()
            ) {
                $lia = new ilCheckboxInputGUI(
                    $GLOBALS['DIC']->language()->txt('obj_orgunit_positions'),
                    self::ORGU_POSITION_ACCESS
                );
                $lia->setInfo($GLOBALS['DIC']->language()->txt('obj_orgunit_positions_info'));
                $lia->setValue(1);
                $lia->setChecked(
                    (bool) ilOrgUnitGlobalSettings::getInstance()->isPositionAccessActiveForObject($a_obj_id)
                );
                if (!$position_settings->isChangeableForObject()) {
                    $lia->setDisabled(true);
                }
                $form->addItem($lia);
            }
        }

        // skills
        if (in_array(self::SKILLS, $services)) {
            $skmg_set = new ilSetting("skmg");
            if ($skmg_set->get("enable_skmg")) {
                $skill = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_skills'), self::SKILLS);
                $skill->setInfo($lng->txt('obj_tool_setting_skills_info'));
                $skill->setValue(1);
                $skill->setChecked(ilContainer::_lookupContainerSetting(
                    $a_obj_id,
                    self::SKILLS,
                    false
                ));
                $form->addItem($skill);
            }
        }

        return $form;
    }


    /**
     * Update service settings
     *
     * @param int               $a_obj_id
     * @param ilPropertyFormGUI $form
     * @param string[]          $services
     *
     * @return bool
     */
    public static function updateServiceSettingsForm($a_obj_id, ilPropertyFormGUI $form, $services)
    {
        // info
        if (in_array(self::INFO_TAB_VISIBILITY, $services)) {
            include_once './Services/Container/classes/class.ilContainer.php';
            ilContainer::_writeContainerSetting($a_obj_id, self::INFO_TAB_VISIBILITY, (int) $form->getInput(self::INFO_TAB_VISIBILITY));
        }
        
        // calendar
        if (in_array(self::CALENDAR_VISIBILITY, $services)) {
            include_once './Services/Calendar/classes/class.ilCalendarSettings.php';
            if (ilCalendarSettings::_getInstance()->isEnabled()) {
                include_once './Services/Container/classes/class.ilContainer.php';
                ilContainer::_writeContainerSetting($a_obj_id, self::CALENDAR_VISIBILITY, (int) $form->getInput(self::CALENDAR_VISIBILITY));
            }
        }
        
        // news
        if (in_array(self::USE_NEWS, $services)) {
            include_once './Services/Container/classes/class.ilContainer.php';
            ilContainer::_writeContainerSetting($a_obj_id, self::USE_NEWS, (int) $form->getInput(self::USE_NEWS));
        }
        if (in_array(self::NEWS_VISIBILITY, $services)) {
            include_once './Services/Container/classes/class.ilContainer.php';
            ilContainer::_writeContainerSetting($a_obj_id, self::NEWS_VISIBILITY, (int) $form->getInput(self::NEWS_VISIBILITY));
            
            if (in_array(ilObject::_lookupType($a_obj_id), array('crs', 'grp'))) {
                $ref_id = array_pop(ilObject::_getAllReferences($a_obj_id));
                    
                include_once "Services/Membership/classes/class.ilMembershipNotifications.php";
                ilMembershipNotifications::importFromForm($ref_id, $form);
            }
        }
        
        // rating
        if (in_array(self::AUTO_RATING_NEW_OBJECTS, $services)) {
            include_once './Services/Container/classes/class.ilContainer.php';
            ilContainer::_writeContainerSetting($a_obj_id, self::AUTO_RATING_NEW_OBJECTS, (int) $form->getInput(self::AUTO_RATING_NEW_OBJECTS));
        }

        // taxonomies
        if (in_array(self::TAXONOMIES, $services)) {
            include_once './Services/Container/classes/class.ilContainer.php';
            ilContainer::_writeContainerSetting($a_obj_id, self::TAXONOMIES, (int) $form->getInput(self::TAXONOMIES));
        }

        // tag cloud
        if (in_array(self::TAG_CLOUD, $services)) {
            include_once './Services/Container/classes/class.ilContainer.php';
            ilContainer::_writeContainerSetting($a_obj_id, self::TAG_CLOUD, (int) $form->getInput(self::TAG_CLOUD));
        }
        
        // (local) custom metadata
        if (in_array(self::CUSTOM_METADATA, $services)) {
            include_once './Services/Container/classes/class.ilContainer.php';
            ilContainer::_writeContainerSetting($a_obj_id, self::CUSTOM_METADATA, (int) $form->getInput(self::CUSTOM_METADATA));
        }
        
        // badges
        if (in_array(self::BADGES, $services)) {
            include_once 'Services/Badge/classes/class.ilBadgeHandler.php';
            if (ilBadgeHandler::getInstance()->isActive()) {
                include_once './Services/Container/classes/class.ilContainer.php';
                ilContainer::_writeContainerSetting($a_obj_id, self::BADGES, (int) $form->getInput(self::BADGES));
            }
        }
        
        // extended user access
        if (in_array(self::ORGU_POSITION_ACCESS, $services)) {
            $position_settings = ilOrgUnitGlobalSettings::getInstance()->getObjectPositionSettingsByType(
                ilObject::_lookupType($a_obj_id)
            );
            
            if ($position_settings->isActive() && $position_settings->isChangeableForObject()) {
                $orgu_object_settings = new ilOrgUnitObjectPositionSetting($a_obj_id);
                $orgu_object_settings->setActive(
                    (int) $form->getInput(self::ORGU_POSITION_ACCESS)
                );
                $orgu_object_settings->update();
            }
        }

        // skills
        if (in_array(self::SKILLS, $services)) {
            $skmg_set = new ilSetting("skmg");
            if ($skmg_set->get("enable_skmg")) {
                include_once './Services/Container/classes/class.ilContainer.php';
                ilContainer::_writeContainerSetting($a_obj_id, self::SKILLS, (int) $form->getInput(self::SKILLS));
            }
        }

        return true;
    }

    
    /**
     * Get active modes
     * @return bool
     */
    public function getModes()
    {
        return $this->modes;
    }
    
    /**
     * Get obj id
     * @return type
     */
    public function getObjId()
    {
        return $this->obj_id;
    }
    
    protected function cancel()
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->returnToParent($this);
    }
    
    /**
     * Edit tool settings (calendar, news, comments, ...)
     * @param ilPropertyFormGUI $form
     */
    protected function editSettings(ilPropertyFormGUI $form = null)
    {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSettingsForm();
        }
        $tpl->setContent($form->getHTML());
    }
    
    
    /**
     * Update settings
     */
    protected function updateToolSettings()
    {
        global $DIC;

        $lng = $DIC->language();
        $ctrl = $this->ctrl;

        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            include_once './Services/Calendar/classes/class.ilCalendarSettings.php';
            if (ilCalendarSettings::_getInstance()->isEnabled()) {
                if ($this->isModeActive(self::CALENDAR_VISIBILITY)) {
                    ilContainer::_writeContainerSetting($this->getObjId(), 'show_calendar', (int) $form->getInput('calendar'));
                }
            }
            ilUtil::sendSuccess($lng->txt('settings_saved'), true);
            $ctrl->redirect($this);
        }
        
        ilUtil::sendFailure($lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->editSettings($form);
    }
    
    /**
     * Check if specific mode is active
     * @param type $a_mode
     * @return type
     */
    protected function isModeActive($a_mode)
    {
        return in_array($a_mode, $this->getModes());
    }
}
