<?php

declare(strict_types=1);

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
    // unfortunately the following constants are not stored
    // in a non-GUI class, other classes are currently directly
    // accessing these, see ilObjectDataSet (changes should be
    // made there accordingly)

    public const CALENDAR_CONFIGURATION = 'cont_cal_configuration';
    public const CALENDAR_VISIBILITY = 'cont_show_calendar';
    public const CALENDAR_ACTIVATION = 'cont_activation_calendar';

    public const NEWS_VISIBILITY = 'cont_show_news';
    public const USE_NEWS = 'cont_use_news';
    public const AUTO_RATING_NEW_OBJECTS = 'cont_auto_rate_new_obj';
    public const INFO_TAB_VISIBILITY = 'cont_show_info_tab';
    public const TAXONOMIES = 'cont_taxonomies';
    public const TAG_CLOUD = 'cont_tag_cloud';
    public const CUSTOM_METADATA = 'cont_custom_md';
    public const BADGES = 'cont_badges';
    public const ORGU_POSITION_ACCESS = 'obj_orgunit_positions';
    public const SKILLS = 'cont_skills';
    public const FILTER = 'filter';
    public const BOOKING = 'cont_bookings';
    public const EXTERNAL_MAIL_PREFIX = 'mail_external_prefix';

    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilLanguage $lng;

    protected ?ilObjectGUI $gui = null;
    protected array $modes = [];
    protected int $obj_id = 0;

    /**
     * Constructor
     */
    public function __construct(ilObjectGUI $parent_gui, int $obj_id, array $modes)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC["lng"];

        $this->gui = $parent_gui;
        $this->modes = $DIC->refinery()->to()->listOf(
            $DIC->refinery()->kindlyTo()->string()
        )->transform($modes);

        $this->obj_id = $obj_id;
    }

    public function executeCommand(): void
    {
        $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd('editSettings');
        $this->$cmd();
    }

    public static function initServiceSettingsForm(
        int $obj_id,
        ilPropertyFormGUI $form,
        array $services
    ): ilPropertyFormGUI {
        global $DIC;

        $ilSetting = $DIC->settings();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $lng->loadLanguageModule("obj");

        // info tab
        if (in_array(self::INFO_TAB_VISIBILITY, $services)) {
            $info = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_info_tab'), self::INFO_TAB_VISIBILITY);
            $info->setValue("1");
            $info->setChecked((bool) ilContainer::_lookupContainerSetting(
                $obj_id,
                self::INFO_TAB_VISIBILITY
            ));
            //$info->setOptionTitle($lng->txt('obj_tool_setting_info_tab'));
            $info->setInfo($lng->txt('obj_tool_setting_info_tab_info'));
            $form->addItem($info);
        }

        // calendar
        if (in_array(self::CALENDAR_CONFIGURATION, $services)) {
            $settings = ilCalendarSettings::_getInstance();
            if ($settings->isEnabled()) {
                $active = new ilCheckboxInputGUI(
                    $lng->txt('obj_tool_setting_calendar_active'),
                    self::CALENDAR_ACTIVATION
                );
                $active->setValue("1");
                $active->setChecked(ilCalendarSettings::lookupCalendarActivated($obj_id));
                $active->setInfo($lng->txt('obj_tool_setting_calendar_active_info'));

                $visible = new ilCheckboxInputGUI(
                    $lng->txt('obj_tool_setting_calendar'),
                    self::CALENDAR_VISIBILITY
                );
                $visible->setValue("1");
                $visible->setChecked(ilCalendarSettings::lookupCalendarContentPresentationEnabled($obj_id));
                $visible->setInfo($lng->txt('obj_tool_setting_calendar_info'));
                $active->addSubItem($visible);

                $form->addItem($active);
            }
        }

        // news
        if (in_array(self::USE_NEWS, $services)) {
            $news = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_use_news'), self::USE_NEWS);
            $news->setValue("1");
            $checked = (bool) ilContainer::_lookupContainerSetting(
                $obj_id,
                self::USE_NEWS
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
                $news->setValue("1");
                $news->setChecked((bool) ilContainer::_lookupContainerSetting(
                    $obj_id,
                    self::NEWS_VISIBILITY,
                    $ilSetting->get('block_activated_news')
                ));
                //$news->setOptionTitle($lng->txt('obj_tool_setting_news'));
                $news->setInfo($lng->txt('obj_tool_setting_news_info'));
                $form->addItem($news);

                if (in_array(ilObject::_lookupType($obj_id), array('crs', 'grp'))) {
                    $refs = ilObject::_getAllReferences($obj_id);
                    $ref_id = array_pop($refs);

                    ilMembershipNotifications::addToSettingsForm($ref_id, null, $news);
                }
            }
        }

        // (local) custom metadata
        if (in_array(self::CUSTOM_METADATA, $services)) {
            $md = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_custom_metadata'), self::CUSTOM_METADATA);
            $md->setInfo($lng->txt('obj_tool_setting_custom_metadata_info'));
            $md->setValue("1");
            $md->setChecked((bool) ilContainer::_lookupContainerSetting(
                $obj_id,
                self::CUSTOM_METADATA
            ));
            $form->addItem($md);
        }

        // tag cloud
        if (in_array(self::TAG_CLOUD, $services)) {
            $tags_active = new ilSetting("tags");
            if ($tags_active->get("enable")) {
                $tag = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_tag_cloud'), self::TAG_CLOUD);
                $tag->setInfo($lng->txt('obj_tool_setting_tag_cloud_info'));
                $tag->setValue("1");
                $tag->setChecked((bool) ilContainer::_lookupContainerSetting(
                    $obj_id,
                    self::TAG_CLOUD
                ));
                $form->addItem($tag);
            }
        }

        // taxonomies
        if (in_array(self::TAXONOMIES, $services)) {
            $tax = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_taxonomies'), self::TAXONOMIES);
            $tax->setValue("1");
            $tax->setChecked((bool) ilContainer::_lookupContainerSetting(
                $obj_id,
                self::TAXONOMIES
            ));
            $form->addItem($tax);
        }

        // auto rating
        if (in_array(self::AUTO_RATING_NEW_OBJECTS, $services)) {
            $lng->loadLanguageModule("rating");

            // auto rating for new objects
            $rate = new ilCheckboxInputGUI($lng->txt('rating_new_objects_auto'), self::AUTO_RATING_NEW_OBJECTS);
            $rate->setValue("1");
            //$rate->setOptionTitle($lng->txt('rating_new_objects_auto'));
            $rate->setInfo($lng->txt('rating_new_objects_auto_info'));
            $rate->setChecked((bool) ilContainer::_lookupContainerSetting(
                $obj_id,
                self::AUTO_RATING_NEW_OBJECTS
            ));
            $form->addItem($rate);
        }

        // badges
        if (in_array(self::BADGES, $services)) {
            if (ilBadgeHandler::getInstance()->isActive()) {
                $bdg = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_badges'), self::BADGES);
                $bdg->setInfo($lng->txt('obj_tool_setting_badges_info'));
                $bdg->setValue("1");
                $bdg->setChecked((bool) ilContainer::_lookupContainerSetting(
                    $obj_id,
                    self::BADGES
                ));
                $form->addItem($bdg);
            }
        }
        if (in_array(self::ORGU_POSITION_ACCESS, $services)) {
            $position_settings = ilOrgUnitGlobalSettings::getInstance()->getObjectPositionSettingsByType(
                ilObject::_lookupType($obj_id)
            );
            if (
                $position_settings->isActive()
            ) {
                $lia = new ilCheckboxInputGUI(
                    $GLOBALS['DIC']->language()->txt('obj_orgunit_positions'),
                    self::ORGU_POSITION_ACCESS
                );
                $lia->setInfo($GLOBALS['DIC']->language()->txt('obj_orgunit_positions_info'));
                $lia->setValue("1");
                $lia->setChecked(
                    ilOrgUnitGlobalSettings::getInstance()->isPositionAccessActiveForObject($obj_id)
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
                $skill->setValue("1");
                $skill->setChecked((bool) ilContainer::_lookupContainerSetting(
                    $obj_id,
                    self::SKILLS
                ));
                $form->addItem($skill);
            }
        }

        // filter
        if (in_array(self::FILTER, $services)) {
            $filter = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_filter'), self::FILTER);
            $filter->setInfo($lng->txt('obj_tool_setting_filter_info'));
            $filter->setValue("1");
            $filter->setChecked((bool) ilContainer::_lookupContainerSetting(
                $obj_id,
                self::FILTER
            ));
            $form->addItem($filter);

            $filter_show_empty = new ilCheckboxInputGUI($lng->txt('obj_tool_setting_filter_empty'), "filter_show_empty");
            $filter_show_empty->setInfo($lng->txt('obj_tool_setting_filter_empty_info'));
            $filter_show_empty->setValue("1");
            $filter_show_empty->setChecked((bool) ilContainer::_lookupContainerSetting(
                $obj_id,
                "filter_show_empty"
            ));
            $filter->addSubItem($filter_show_empty);
        }
        // booking tool
        if (in_array(self::BOOKING, $services)) {
            $book = new ilCheckboxInputGUI($lng->txt('obj_tool_booking'), self::BOOKING);
            $book->setInfo($lng->txt('obj_tool_booking_info'));
            $book->setValue("1");
            $book->setChecked((bool) ilContainer::_lookupContainerSetting(
                $obj_id,
                self::BOOKING
            ));
            $form->addItem($book);
        }

        if (in_array(self::EXTERNAL_MAIL_PREFIX, $services)) {
            $externalMailPrefix = new ilTextInputGUI($lng->txt('obj_tool_ext_mail_subject_prefix'), self::EXTERNAL_MAIL_PREFIX);
            $externalMailPrefix->setMaxLength(255);
            $externalMailPrefix->setInfo($lng->txt('obj_tool_ext_mail_subject_prefix_info'));
            $externalMailPrefix->setValue(ilContainer::_lookupContainerSetting($obj_id, self::EXTERNAL_MAIL_PREFIX, ''));
            $form->addItem($externalMailPrefix);
        }

        return $form;
    }

    /**
     * @param string[] $services
     */
    public static function updateServiceSettingsForm(int $obj_id, ilPropertyFormGUI $form, array $services): bool
    {
        // info
        if (in_array(self::INFO_TAB_VISIBILITY, $services)) {
            ilContainer::_writeContainerSetting(
                $obj_id,
                self::INFO_TAB_VISIBILITY,
                $form->getInput(self::INFO_TAB_VISIBILITY)
            );
        }

        // calendar
        if (in_array(self::CALENDAR_CONFIGURATION, $services)) {
            if (ilCalendarSettings::_getInstance()->isEnabled()) {
                $active = $form->getInput(self::CALENDAR_ACTIVATION);
                $visible = $form->getInput(self::CALENDAR_VISIBILITY);
                ilContainer::_writeContainerSetting(
                    $obj_id,
                    self::CALENDAR_ACTIVATION,
                    $active
                );
                ilContainer::_writeContainerSetting(
                    $obj_id,
                    self::CALENDAR_VISIBILITY,
                    $active ? $visible : ""
                );
            }
        }
        // news
        if (in_array(self::USE_NEWS, $services)) {
            ilContainer::_writeContainerSetting($obj_id, self::USE_NEWS, $form->getInput(self::USE_NEWS));
        }
        if (in_array(self::NEWS_VISIBILITY, $services)) {
            ilContainer::_writeContainerSetting($obj_id, self::NEWS_VISIBILITY, $form->getInput(self::NEWS_VISIBILITY));

            if (in_array(ilObject::_lookupType($obj_id), array('crs', 'grp'))) {
                $refs = ilObject::_getAllReferences($obj_id);
                $ref_id = array_pop($refs);

                ilMembershipNotifications::importFromForm($ref_id, $form);
            }
        }

        // rating
        if (in_array(self::AUTO_RATING_NEW_OBJECTS, $services)) {
            ilContainer::_writeContainerSetting(
                $obj_id,
                self::AUTO_RATING_NEW_OBJECTS,
                $form->getInput(self::AUTO_RATING_NEW_OBJECTS)
            );
        }

        // taxonomies
        if (in_array(self::TAXONOMIES, $services)) {
            ilContainer::_writeContainerSetting($obj_id, self::TAXONOMIES, $form->getInput(self::TAXONOMIES));
        }

        // tag cloud
        if (in_array(self::TAG_CLOUD, $services)) {
            ilContainer::_writeContainerSetting($obj_id, self::TAG_CLOUD, $form->getInput(self::TAG_CLOUD));
        }

        // (local) custom metadata
        if (in_array(self::CUSTOM_METADATA, $services)) {
            ilContainer::_writeContainerSetting($obj_id, self::CUSTOM_METADATA, $form->getInput(self::CUSTOM_METADATA));
        }

        // badges
        if (in_array(self::BADGES, $services)) {
            if (ilBadgeHandler::getInstance()->isActive()) {
                ilContainer::_writeContainerSetting($obj_id, self::BADGES, $form->getInput(self::BADGES));
            }
        }

        // booking
        if (in_array(self::BOOKING, $services)) {
            ilContainer::_writeContainerSetting($obj_id, self::BOOKING, $form->getInput(self::BOOKING));
        }

        // extended user access
        if (in_array(self::ORGU_POSITION_ACCESS, $services)) {
            $position_settings = ilOrgUnitGlobalSettings::getInstance()->getObjectPositionSettingsByType(
                ilObject::_lookupType($obj_id)
            );

            if ($position_settings->isActive() && $position_settings->isChangeableForObject()) {
                $orgu_object_settings = new ilOrgUnitObjectPositionSetting($obj_id);
                $orgu_object_settings->setActive(
                    (bool) $form->getInput(self::ORGU_POSITION_ACCESS)
                );
                $orgu_object_settings->update();
            }
        }

        // skills
        if (in_array(self::SKILLS, $services)) {
            $skmg_set = new ilSetting("skmg");
            if ($skmg_set->get("enable_skmg")) {
                ilContainer::_writeContainerSetting($obj_id, self::SKILLS, $form->getInput(self::SKILLS));
            }
        }

        // filter
        if (in_array(self::FILTER, $services)) {
            ilContainer::_writeContainerSetting($obj_id, self::FILTER, $form->getInput(self::FILTER));
            ilContainer::_writeContainerSetting($obj_id, "filter_show_empty", $form->getInput("filter_show_empty"));
        }

        if (in_array(self::EXTERNAL_MAIL_PREFIX, $services)) {
            ilContainer::_writeContainerSetting($obj_id, self::EXTERNAL_MAIL_PREFIX, $form->getInput(self::EXTERNAL_MAIL_PREFIX));
        }

        return true;
    }


    /**
     * Get active modes
     * @return string[]
     */
    public function getModes(): array
    {
        return $this->modes;
    }

    /**
     * Get obj id
     */
    public function getObjId(): int
    {
        return $this->obj_id;
    }

    protected function cancel(): void
    {
        $this->ctrl->returnToParent($this);
    }

    /**
     * Edit tool settings (calendar, news, comments, ...)
     * @param ilPropertyFormGUI $form
     */
    protected function editSettings(ilPropertyFormGUI $form = null): void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            // TODO: cant find initSettingsForm, is editSettings ever called?
            $form = $this->initSettingsForm();
        }
        $this->main_tpl->setContent($form->getHTML());
    }


    /**
     * Update settings
     */
    protected function updateToolSettings(): void
    {
        // TODO: cant find initSettingsForm, is updateToolSettings ever called?
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            if (ilCalendarSettings::_getInstance()->isEnabled()) {
                if ($this->isModeActive(self::CALENDAR_VISIBILITY)) {
                    ilContainer::_writeContainerSetting($this->getObjId(), 'show_calendar', $form->getInput('calendar'));
                }
            }
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this);
        }

        $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $form->setValuesByPost();
        $this->editSettings($form);
    }

    /**
     * Check if specific mode is active
     */
    protected function isModeActive(string $mode): bool
    {
        return in_array($mode, $this->getModes(), true);
    }
}
