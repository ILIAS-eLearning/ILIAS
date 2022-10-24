<?php

declare(strict_types=0);

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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * Class ilCourseContentGUI
 * @author       Stefan Meyer <meyer@leifos.com>
 * @extends      ilObjectGUI
 * @ilCtrl_Calls ilCourseContentGUI: ilColumnGUI, ilObjectCopyGUI
 */
class ilCourseContentGUI
{
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilContainerGUI $container_gui;
    protected ilContainer $container_obj;
    protected ilObjCourse $course_obj;

    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilFavouritesManager $fav_manager;
    protected ilAccessHandler $access;
    protected ilErrorHandling $error;
    protected ilObjUser $user;
    protected ilObjectDataCache $objectDataCache;
    protected ilTree $tree;
    protected GlobalHttpState $http;
    protected Factory $refinery;

    public function __construct(ilContainerGUI $container_gui_obj)
    {
        global $DIC;

        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->fav_manager = new ilFavouritesManager();
        $this->access = $DIC->access();
        $this->error = $DIC['ilErr'];
        $this->user = $DIC->user();
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->tree = $DIC->repositoryTree();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->container_gui = $container_gui_obj;
        $this->container_obj = $this->container_gui->getObject();
        $this->initCourseObject();
    }

    public function executeCommand(): void
    {
        if (!$this->access->checkAccess('read', '', $this->container_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->WARNING);
        }

        $this->__setSubTabs();
        $this->tabs->setTabActive('view_content');
        $cmd = $this->ctrl->getCmd();

        switch ($this->ctrl->getNextClass($this)) {
            default:
                $start = $this->initStartObjects();
                if ($start instanceof ilCourseStart) {
                    $this->showStartObjects($start);
                    break;
                }
                if (!$cmd) {
                    throw new RuntimeException('Missing ctrl command.');
                }
                $this->$cmd();
                break;
        }
    }

    protected function initMemberIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('member_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'member_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    public function getContainerObject(): ilContainer
    {
        return $this->container_obj;
    }

    public function initStartObjects(): ?ilCourseStart
    {
        if ($this->access->checkAccess('write', '', $this->course_obj->getRefId())) {
            return null;
        }
        $start_obj = new ilCourseStart($this->course_obj->getRefId(), $this->course_obj->getId());
        if (count($start_obj->getStartObjects()) && !$start_obj->allFullfilled($this->user->getId())) {
            return $start_obj;
        }
        return null;
    }

    public function showStartObjects(ilCourseStart $start_obj): void
    {
        $this->tabs->setSubTabActive('crs_content');

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_start_view.html", 'Modules/Course');
        $this->tpl->setVariable("INFO_STRING", $this->lng->txt('crs_info_start'));
        $this->tpl->setVariable("TBL_TITLE_START", $this->lng->txt('crs_table_start_objects'));
        $this->tpl->setVariable("HEADER_NR", $this->lng->txt('crs_nr'));
        $this->tpl->setVariable("HEADER_DESC", $this->lng->txt('description'));
        $this->tpl->setVariable("HEADER_EDITED", $this->lng->txt('crs_objective_accomplished'));

        $lm_continue = new ilCourseLMHistory($this->course_obj->getRefId(), $this->user->getId());
        $continue_data = $lm_continue->getLMHistory();

        $counter = 0;
        foreach ($start_obj->getStartObjects() as $start) {
            $obj_id = $this->objectDataCache->lookupObjId((int) $start['item_ref_id']);
            $ref_id = $start['item_ref_id'];
            $type = $this->objectDataCache->lookupType($obj_id);

            $conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($ref_id, $obj_id);

            $obj_link = ilLink::_getLink($ref_id, $type);
            $obj_frame = '';

            // Tmp fix for tests
            $obj_frame = $type == 'tst' ? '' : $obj_frame;

            if ($this->access->checkAccess('read', '', $ref_id)) {
                $this->tpl->setCurrentBlock("start_read");
                $this->tpl->setVariable("READ_TITLE_START", $this->objectDataCache->lookupTitle($obj_id));
                $this->tpl->setVariable("READ_TARGET_START", $obj_frame);
                $this->tpl->setVariable(
                    "READ_LINK_START",
                    $obj_link . '&crs_show_result=' . $this->course_obj->getRefId()
                );
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setCurrentBlock("start_visible");
                $this->tpl->setVariable("VISIBLE_LINK_START", $this->objectDataCache->lookupTitle($obj_id));
                $this->tpl->parseCurrentBlock();
            }

            // CONTINUE LINK
            if (isset($continue_data[$ref_id])) {
                $this->tpl->setCurrentBlock("link");
                $this->tpl->setVariable("LINK_HREF", ilLink::_getLink($ref_id, '', array('obj_id',
                                                                                         $continue_data[$ref_id]['lm_page_id']
                )));
                #$this->tpl->setVariable("CONTINUE_LINK_TARGET",$target);
                $this->tpl->setVariable("LINK_NAME", $this->lng->txt('continue_work'));
                $this->tpl->parseCurrentBlock();
            }

            // add to desktop link
            if ($this->course_obj->getAboStatus()) {
                if (!$this->fav_manager->ifIsFavourite($this->user->getId(), $ref_id)) {
                    if ($this->access->checkAccess('read', '', $ref_id)) {
                        $this->tpl->setCurrentBlock("link");
                        $this->ctrl->setParameterByClass(get_class($this->container_gui), 'item_ref_id', $ref_id);
                        $this->ctrl->setParameterByClass(get_class($this->container_gui), 'item_id', $ref_id);
                        $this->ctrl->setParameterByClass(get_class($this->container_gui), 'type', $type);

                        $this->tpl->setVariable(
                            "LINK_HREF",
                            $this->ctrl->getLinkTarget($this->container_gui, 'addToDesk')
                        );
                        $this->tpl->setVariable("LINK_NAME", $this->lng->txt("rep_add_to_favourites"));
                        $this->tpl->parseCurrentBlock();
                    }
                } else {
                    $this->tpl->setCurrentBlock("link");
                    $this->ctrl->setParameterByClass(get_class($this->container_gui), 'item_ref_id', $ref_id);
                    $this->ctrl->setParameterByClass(get_class($this->container_gui), 'item_id', $ref_id);
                    $this->ctrl->setParameterByClass(get_class($this->container_gui), 'type', $type);

                    $this->tpl->setVariable(
                        "LINK_HREF",
                        $this->ctrl->getLinkTarget($this->container_gui, 'removeFromDesk')
                    );
                    $this->tpl->setVariable("LINK_NAME", $this->lng->txt("rep_remove_from_favourites"));
                    $this->tpl->parseCurrentBlock();
                }
            }

            // Description
            if (strlen($this->objectDataCache->lookupDescription($obj_id))) {
                $this->tpl->setCurrentBlock("start_description");
                $this->tpl->setVariable("DESCRIPTION_START", $this->objectDataCache->lookupDescription($obj_id));
                $this->tpl->parseCurrentBlock();
            }

            if ($start_obj->isFullfilled($this->user->getId(), $ref_id)) {
                $accomplished = 'accomplished';
                $icon = ilUtil::getImagePath("icon_ok.svg");
            } else {
                $accomplished = 'not_accomplished';
                $icon = ilUtil::getImagePath("icon_not_ok.svg");
            }
            $this->tpl->setCurrentBlock("start_row");
            $this->tpl->setVariable("EDITED_IMG", $icon);
            $this->tpl->setVariable("EDITED_ALT", $this->lng->txt('crs_objective_' . $accomplished));
            $this->tpl->setVariable("ROW_CLASS", 'option_value');
            $this->tpl->setVariable("ROW_CLASS_CENTER", 'option_value_center');
            $this->tpl->setVariable("OBJ_NR_START", ++$counter . '.');
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * Manage timings
     */
    protected function manageTimings(array $failed_items = []): void
    {
        if (!$this->access->checkAccess('write', '', $this->container_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_write'), $this->error->WARNING);
        }
        $this->tabs->setTabActive('timings_timings');
        $this->tabs->clearSubTabs();

        $table = new ilTimingsManageTableGUI(
            $this,
            'manageTimings',
            $this->getContainerObject(),
            $this->course_obj
        );
        if ($failed_items !== []) {
            $table->setFailureStatus(true);
        }
        $table->init();
        $table->parse(
            ilObjectActivation::getTimingsAdministrationItems($this->getContainerObject()->getRefId()),
            $failed_items
        );
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Manage personal timings
     */
    protected function managePersonalTimings(array $failed = []): void
    {
        global $ilErr, $ilAccess;

        if (!$this->access->checkAccess('read', '', $this->container_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->WARNING);
        }
        $this->tabs->setTabActive('timings_timings');
        $this->tabs->clearSubTabs();

        $table = new ilTimingsPersonalTableGUI(
            $this,
            'managePersonalTimings',
            $this->getContainerObject(),
            $this->course_obj
        );
        $table->setFailureStatus((bool) count($failed));
        $table->setUserId($this->user->getId());
        $table->init();
        $table->parse(
            ilObjectActivation::getItems(
                $this->getContainerObject()->getRefId(),
                false
            ),
            $failed
        );
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Update personal timings
     */
    protected function updatePersonalTimings(): bool
    {
        if (!$this->access->checkAccess('read', '', $this->container_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_write'), $this->error->WARNING);
        }
        $this->tabs->clearSubTabs();
        $failed = array();

        $post_item = (array) ($this->http->request()->getParsedBody()['item']) ?? [];
        foreach ($post_item as $ref_id => $data) {
            $sug_start_dt = ilCalendarUtil::parseIncomingDate($data['sug_start']);
            $sug_end_dt = ilCalendarUtil::parseIncomingDate($data['sug_end']);

            if ($sug_start_dt instanceof ilDate && $sug_end_dt instanceof ilDate) {
                if (ilDateTime::_after($sug_start_dt, $sug_end_dt)) {
                    $failed[$ref_id] = 'crs_timing_err_start_end';
                    continue;
                }
                // update user date
                $tu = new ilTimingUser($ref_id, $GLOBALS['ilUser']->getId());
                $tu->getStart()->setDate($sug_start_dt->get(IL_CAL_UNIX), IL_CAL_UNIX);
                $tu->getEnd()->setDate($sug_end_dt->get(IL_CAL_UNIX), IL_CAL_UNIX);
                $tu->update();
            } else {
                $failed['ref_id'] = 'crs_timing_err_valid_dates';
            }
        }
        if ($failed === []) {
            $this->main_tpl->setOnScreenMessage('success', $GLOBALS['lng']->txt('settings_saved'));
            $this->managePersonalTimings();
            return true;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->managePersonalTimings($failed);
            return true;
        }
    }

    public function returnToMembers(): void
    {
        $this->ctrl->returnToParent($this);
    }

    /**
     * @deprecated
     * @todo
     */
    public function showUserTimings(): void
    {
        $this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.crs_user_timings.html', 'Modules/Course');
        $this->tabs->clearSubTabs();
        $this->tabs->setTabActive('members');

        if (!$this->initMemberIdFromQuery()) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->returnToParent($this);
        }

        // Back button
        $this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
        $this->tpl->setCurrentBlock("btn_cell");
        $this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, 'returnToMembers'));
        $this->tpl->setVariable("BTN_TXT", $this->lng->txt("back"));
        $this->tpl->parseCurrentBlock();

        $this->tpl->setVariable("HEADER_IMG", ilUtil::getImagePath('icon_usr.svg'));
        $this->tpl->setVariable("HEADER_ALT", $this->lng->txt('obj_usr'));
        $this->tpl->setVariable("TABLE_HEADER", $this->lng->txt('timings_of'));
        $name = ilObjUser::_lookupName($this->initMemberIdFromQuery());
        $this->tpl->setVariable("USER_NAME", $name['lastname'] . ', ' . $name['firstname']);

        $this->tpl->setVariable("TXT_TITLE", $this->lng->txt('title'));
        $this->tpl->setVariable("TXT_START_END", $this->lng->txt('crs_timings_short_start_end'));
        $this->tpl->setVariable("TXT_INFO_START_END", $this->lng->txt('crs_timings_start_end_info'));
        $this->tpl->setVariable("TXT_CHANGED", $this->lng->txt('crs_timings_changed'));
        $this->tpl->setVariable("TXT_OWN_PRESETTING", $this->lng->txt('crs_timings_planed_start'));
        $this->tpl->setVariable("TXT_INFO_OWN_PRESETTING", $this->lng->txt('crs_timings_from_until'));

        $items = ilObjectActivation::getTimingsAdministrationItems($this->course_obj->getRefId());
        foreach ($items as $item) {
            if (($item['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) or
                ilObjectActivation::hasChangeableTimings($item['ref_id'])) {
                $this->__renderUserItem($item, 0);
            }
        }
    }

    /**
     * @deprecated
     * @todo
     */
    public function __renderUserItem(array $item, int $level): void
    {
        $this->lng->loadLanguageModule('meta');

        $usr_planed = new ilTimingUser($item['ref_id'], $this->initMemberIdFromQuery());

        for ($i = 0; $i < $level; $i++) {
            $this->tpl->touchBlock('start_indent');
            $this->tpl->touchBlock('end_indent');
        }
        if (strlen($item['description'])) {
            $this->tpl->setCurrentBlock("item_description");
            $this->tpl->setVariable("DESC", $item['description']);
            $this->tpl->parseCurrentBlock();
        }
        if ($tlt = ilMDEducational::_getTypicalLearningTimeSeconds($item['obj_id'])) {
            $this->tpl->setCurrentBlock("tlt");
            $this->tpl->setVariable("TXT_TLT", $this->lng->txt('meta_typical_learning_time'));
            $this->tpl->setVariable("TLT_VAL", ilDatePresentation::secondsToString($tlt));
            $this->tpl->parseCurrentBlock();
        }

        if (!$item['title'] &&
            $item['type'] == 'sess') {
            $app_info = ilSessionAppointment::_lookupAppointment(ilObject::_lookupObjId($item["ref_id"]));
            $item['title'] = ilSessionAppointment::_appointmentToString(
                $app_info['start'],
                $app_info['end'],
                (bool) $app_info['fullday']
            );
        }

        $this->tpl->setCurrentBlock("title_plain");
        $this->tpl->setVariable("TITLE", $item['title']);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("container_standard_row");

        $this->tpl->setVariable('TYPE_IMG', ilObject::_getIcon($item['obj_id'], 'tiny', $item['type']));
        $this->tpl->setVariable("TYPE_ALT_IMG", $this->lng->txt('obj_' . $item['type']));

        if ($item['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) {
            if ($usr_planed->getStart()->get(IL_CAL_UNIX)) {
                $this->tpl->setVariable('SUG_START', $usr_planed->getStart()->get(IL_CAL_DATE));
            }
            if ($usr_planed->getEnd()->get(IL_CAL_UNIX)) {
                $this->tpl->setVariable('SUG_END', $usr_planed->getEnd()->get(IL_CAL_DATE));
            }
        }
        $this->tpl->parseCurrentBlock();
        foreach (ilObjectActivation::getTimingsAdministrationItems($item['ref_id']) as $item_data) {
            if (($item_data['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) or
                ilObjectActivation::hasChangeableTimings($item_data['ref_id'])) {
                $this->__renderUserItem($item_data, $level + 1);
            }
        }
    }

    protected function updateManagedTimings(): bool
    {
        if (!$this->access->checkAccess('write', '', $this->container_obj->getRefId())) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_write'), $this->error->WARNING);
        }

        $this->tabs->clearSubTabs();

        $failed = array();
        $post_item = (array) ($this->http->request()->getParsedBody()['item']) ?? [];
        foreach ($post_item as $ref_id => $data) {
            $item_obj = new ilObjectActivation();
            $item_obj->read($ref_id);

            $data['active'] = $data['active'] ?? 0;
            $item_obj->setTimingType($data['active'] ? ilObjectActivation::TIMINGS_PRESETTING : ilObjectActivation::TIMINGS_DEACTIVATED);
            $item_obj->toggleChangeable((bool) ($data['change'] ?? false));

            if ($this->course_obj->getTimingMode() == ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE) {
                $sug_start_dt = ilCalendarUtil::parseIncomingDate($data['sug_start'] ?? '');
                $sug_end_dt = ilCalendarUtil::parseIncomingDate($data['sug_end'] ?? '');

                if ($sug_start_dt instanceof ilDate && $sug_end_dt instanceof ilDate) {
                    if (ilDateTime::_after($sug_start_dt, $sug_end_dt)) {
                        $failed[$ref_id] = 'crs_timing_err_start_end';
                        continue;
                    }
                    $item_obj->setSuggestionStart($sug_start_dt->get(IL_CAL_UNIX));
                    $item_obj->setSuggestionEnd($sug_end_dt->get(IL_CAL_UNIX));
                } else {
                    $failed['ref_id'] = 'crs_timing_err_valid_dates';
                    continue;
                }
            } else {
                if (
                    (int) $data['sug_start_rel'] < 0 || (int) $data['duration_a'] < 0
                ) {
                    $failed[$ref_id] = 'crs_timing_err_start_dur_rel';
                    continue;
                }
                $item_obj->setSuggestionStartRelative($data['sug_start_rel']);
                $item_obj->setSuggestionEndRelative($data['sug_start_rel'] + $data['duration_a']);

                // add default values for start/end (relative to now)
                $start = new ilDate(time(), IL_CAL_UNIX);
                $start->increment(IL_CAL_DAY, $data['sug_start_rel']);
                $item_obj->setSuggestionStart($start->get(IL_CAL_UNIX));

                $start->increment(IL_CAL_DAY, $data['duration_a']);
                $item_obj->setSuggestionEnd($start->get(IL_CAL_UNIX));
            }

            $item_obj->update($ref_id);
        }
        if ($failed === []) {
            // update course => create calendar entries
            $this->course_obj->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
            $this->manageTimings();
            return true;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->manageTimings($failed);
            return true;
        }
    }

    public function __setSubTabs(): void
    {
        if ($this->container_obj->getType() == 'crs') {
            $this->container_gui->setContentSubTabs();
        }
    }

    public function initCourseObject(): bool
    {
        if ($this->container_obj instanceof ilObjCourse) {
            $this->course_obj = $this->container_obj;
        } else {
            $course_ref_id = $this->tree->checkForParentType($this->container_obj->getRefId(), 'crs');
            $course = ilObjectFactory::getInstanceByRefId($course_ref_id, false);
            if ($course instanceof ilObjCourse) {
                $this->course_obj = $course;
            }
        }
        return true;
    }
} // END class.ilCourseContentGUI
