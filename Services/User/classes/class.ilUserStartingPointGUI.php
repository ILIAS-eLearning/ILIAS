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
 * Class ilUserStartingPointGUI
 *
 * @author Jesús López <lopez@leifos.com>
 * @ilCtrl_Calls ilUserStartingPointGUI:
 */
class ilUserStartingPointGUI
{
    protected ilCtrl $ctrl;
    protected ilToolbarGUI $toolbar;
    protected \ILIAS\User\StandardGUIRequest $user_request;
    protected ilLogger $log;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected int $parent_ref_id;

    public function __construct(int $a_parent_ref_id)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->log = ilLoggerFactory::getLogger("user");
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->toolbar = $ilToolbar;
        $this->ctrl = $ilCtrl;
        $this->parent_ref_id = $a_parent_ref_id;
        $this->lng->loadLanguageModule("administration");
        $this->lng->loadLanguageModule("dateplaner");
        $this->user_request = new \ILIAS\User\StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    public function executeCommand() : void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $cmd = $ilCtrl->getCmd();
        if ($cmd == "roleStartingPointform" || !$cmd) {
            $cmd = "initRoleStartingPointForm";
        }

        $this->$cmd();
    }

    /**
     * table form to set up starting points depends of user roles
     */
    public function startingPoints() : void
    {
        $roles_without_point = ilStartingPoint::getGlobalRolesWithoutStartingPoint();

        if (!empty($roles_without_point)) {
            $this->toolbar->addButton(
                $this->lng->txt('create_starting_point'),
                $this->ctrl->getLinkTarget($this, "roleStartingPointform")
            );
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("all_roles_has_starting_point"));
        }


        $tbl = new ilUserRoleStartingPointTableGUI($this);

        $this->tpl->setContent($tbl->getHTML());
    }

    public function initUserStartingPointForm(ilPropertyFormGUI $form = null) : void
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getUserStartingPointForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function initRoleStartingPointForm(ilPropertyFormGUI $form = null) : void
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getRoleStartingPointForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function getUserStartingPointForm() : ilPropertyFormGUI
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $form = new ilPropertyFormGUI();

        // starting point: personal
        $startp = new ilCheckboxInputGUI($this->lng->txt("user_chooses_starting_page"), "usr_start_pers");
        $startp->setInfo($this->lng->txt("adm_user_starting_point_personal_info"));
        $startp->setChecked(ilUserUtil::hasPersonalStartingPoint());

        $form->addItem($startp);

        $form->addCommandButton("saveUserStartingPoint", $this->lng->txt("save"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getRoleStartingPointForm() : ilPropertyFormGUI
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $options = [];
        $starting_point = 0;
        $st_point = null;

        if (!$rbacsystem->checkAccess("write", $this->parent_ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->FATAL);
        }
        $form = new ilPropertyFormGUI();
        $ilCtrl->saveParameter($this, array("spid"));

        $spoint_id = $this->user_request->getStartingPointId();
        $req_role_id = $this->user_request->getRoleId();

        //edit no default
        if ($spoint_id > 0 && $spoint_id != 'default') {
            $st_point = new ilStartingPoint($spoint_id);

            //starting point role based
            if ($st_point->getRuleType() == ilStartingPoint::ROLE_BASED && $req_role_id) {
                $rolid = $req_role_id;
                if ($role = new ilObjRole($rolid)) {
                    $options[$rolid] = $role->getTitle();
                    $starting_point = $st_point->getStartingPoint();

                    // role title, non editable
                    $ne = new ilNonEditableValueGUI($this->lng->txt("editing_this_role"), 'role_disabled');
                    $ne->setValue($role->getTitle());
                    $form->addItem($ne);

                    $hi = new ilHiddenInputGUI("role");
                    $hi->setValue($rolid);
                    $form->addItem($hi);

                    $hidde_sp_id = new ilHiddenInputGUI("start_point_id");
                    $hidde_sp_id->setValue($spoint_id);
                    $form->addItem($hidde_sp_id);
                }
            }
        }
        //create
        elseif (!$spoint_id || $spoint_id != 'default') {
            //starting point role based
            if (ilStartingPoint::ROLE_BASED) {
                $roles = ilStartingPoint::getGlobalRolesWithoutStartingPoint();

                // role type
                $radg = new ilRadioGroupInputGUI($this->lng->txt("role"), "role_type");
                $radg->setValue(0);
                $op1 = new ilRadioOption($this->lng->txt("user_global_role"), 0);
                $radg->addOption($op1);
                $op2 = new ilRadioOption($this->lng->txt("user_local_role"), 1);
                $radg->addOption($op2);
                $form->addItem($radg);

                foreach ($roles as $role) {
                    $options[$role['id']] = $role['title'];
                }
                $si_roles = new ilSelectInputGUI($this->lng->txt("roles_without_starting_point"), 'role');
                $si_roles->setOptions($options);
                $op1->addSubItem($si_roles);

                // local role
                $role_search = new ilRoleAutoCompleteInputGUI('', 'role_search', $this, 'addRoleAutoCompleteObject');
                $role_search->setSize(40);
                $op2->addSubItem($role_search);
            }
        } else {
            $starting_point = ilUserUtil::getStartingPoint();
        }

        // starting point

        $si = new ilRadioGroupInputGUI($this->lng->txt("adm_user_starting_point"), "start_point");
        $si->setRequired(true);
        $si->setInfo($this->lng->txt("adm_user_starting_point_info"));
        $valid = array_keys(ilUserUtil::getPossibleStartingPoints());
        foreach (ilUserUtil::getPossibleStartingPoints(true) as $value => $caption) {
            $opt = new ilRadioOption($caption, $value);

            if ($value === ilUserUtil::START_PD_CALENDAR) {
                $default_cal_view = new ilRadioGroupInputGUI($this->lng->txt('cal_def_view'), 'user_calendar_view');
                $default_cal_view->setRequired(true);

                $option = new ilRadioOption($this->lng->txt("day"), ilCalendarSettings::DEFAULT_CAL_DAY);
                $default_cal_view->addOption($option);
                $option = new ilRadioOption($this->lng->txt("week"), ilCalendarSettings::DEFAULT_CAL_WEEK);
                $default_cal_view->addOption($option);
                $option = new ilRadioOption($this->lng->txt("month"), ilCalendarSettings::DEFAULT_CAL_MONTH);
                $default_cal_view->addOption($option);

                $option = new ilRadioOption($this->lng->txt("cal_list"), ilCalendarSettings::DEFAULT_CAL_LIST);

                $cal_periods = new ilSelectInputGUI($this->lng->txt("cal_list"), "user_cal_period");
                $cal_periods->setOptions([
                    ilCalendarAgendaListGUI::PERIOD_DAY => "1 " . $this->lng->txt("day"),
                    ilCalendarAgendaListGUI::PERIOD_WEEK => "1 " . $this->lng->txt("week"),
                    ilCalendarAgendaListGUI::PERIOD_MONTH => "1 " . $this->lng->txt("month"),
                    ilCalendarAgendaListGUI::PERIOD_HALF_YEAR => "6 " . $this->lng->txt("months")
                ]);
                $cal_periods->setRequired(true);


                if (isset($st_point)) {
                    $default_cal_view->setValue($st_point->getCalendarView());
                    $cal_periods->setValue($st_point->getCalendarPeriod());
                } else {
                    $default_cal_view->setValue(ilUserUtil::getCalendarView());
                    $cal_periods->setValue(ilUserUtil::getCalendarPeriod());
                }
                $option->addSubItem($cal_periods);
                $default_cal_view->addOption($option);

                $opt->addSubItem($default_cal_view);
            }

            $si->addOption($opt);

            if (!in_array($value, $valid)) {
                $opt->setInfo($this->lng->txt("adm_user_starting_point_invalid_info"));
            }
        }
        $si->setValue($starting_point);
        $form->addItem($si);

        // starting point: repository object
        $repobj = new ilRadioOption($this->lng->txt("adm_user_starting_point_object"), ilUserUtil::START_REPOSITORY_OBJ);
        $repobj_id = new ilTextInputGUI($this->lng->txt("adm_user_starting_point_ref_id"), "start_object");
        $repobj_id->setRequired(true);
        $repobj_id->setSize(5);
        //$i has the starting_point value, so we are here only when edit one role or setting the default role.
        if ($si->getValue() == ilUserUtil::START_REPOSITORY_OBJ) {
            if ($st_point) {
                $start_ref_id = $st_point->getStartingObject();
            } else {
                $start_ref_id = ilUserUtil::getStartingObject();
            }

            $repobj_id->setValue($start_ref_id);
            if ($start_ref_id) {
                $start_obj_id = ilObject::_lookupObjId($start_ref_id);
                if ($start_obj_id) {
                    $repobj_id->setInfo($this->lng->txt("obj_" . ilObject::_lookupType($start_obj_id)) .
                        ": " . ilObject::_lookupTitle($start_obj_id));
                }
            }
        }
        $repobj->addSubItem($repobj_id);
        $si->addOption($repobj);

        // save and cancel commands
        $form->addCommandButton("saveStartingPoint", $this->lng->txt("save"));
        $form->addCommandButton("startingPoints", $this->lng->txt("cancel"));

        $form->setTitle($this->lng->txt("starting_point_settings"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    public function addRoleAutoCompleteObject() : void
    {
        ilRoleAutoCompleteInputGUI::echoAutoCompleteList();
    }

    protected function saveUserStartingPoint() : void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];

        if (!$rbacsystem->checkAccess("write", $this->parent_ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->FATAL);
        }

        $form = $this->getUserStartingPointForm();

        if ($form->checkInput()) {
            ilUserUtil::togglePersonalStartingPoint($form->getInput('usr_start_pers'));
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "startingPoints");
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_error"), true);
        $ilCtrl->redirect($this, "startingPoints");
    }

    /**
     * store starting point from the form
     */
    protected function saveStartingPoint() : void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $tree = $DIC['tree'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $tpl = $DIC['tpl'];
        $start_point_id = 0;

        if (!$rbacsystem->checkAccess("write", $this->parent_ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->FATAL);
        }

        if ($this->user_request->getStartingPointId() > 0) {
            $start_point_id = $this->user_request->getStartingPointId();
        }

        //add from form
        $form = $this->getRoleStartingPointForm();
        if ($form->checkInput()) {
            //if role
            if ($form->getInput('role')) {

                // check if we have a locale role
                if ($form->getInput('role_type') == 1) {
                    if ($this->user_request->getRoleId() > 0) {
                        $role_id = $this->user_request->getRoleId();     // id from role selection
                    } else {
                        $parser = new ilQueryParser('"' . $form->getInput('role_search') . '"');

                        // TODO: Handle minWordLength
                        $parser->setMinWordLength(1);
                        $parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
                        $parser->parse();

                        $object_search = new ilLikeObjectSearch($parser);
                        $object_search->setFilter(array('role'));
                        $res = $object_search->performSearch();

                        $entries = $res->getEntries();
                        if (count($entries) == 1) {         // role name only finds one match -> use it
                            $role = current($entries);
                            $role_id = $role['obj_id'];
                        } elseif (count($entries) > 1) {    // multiple matches -> show selection
                            $this->showRoleSelection(
                                $form->getInput('role'),
                                $form->getInput('role_search'),
                                $form->getInput('start_point'),
                                $form->getInput('start_object')
                            );
                            return;
                        }
                    }
                } else {
                    $role_id = $form->getInput('role');
                }

                //create starting point
                if ($start_point_id) {
                    $starting_point = new ilStartingPoint($start_point_id);
                } else { //edit
                    $starting_point = new ilStartingPoint();
                }
                $starting_point->setRuleType(ilStartingPoint::ROLE_BASED);
                $starting_point->setStartingPoint((int) $form->getInput("start_point"));
                $rules = array("role_id" => $role_id);
                $starting_point->setRuleOptions(serialize($rules));

                $obj_id = $form->getInput('start_object');
                $cal_view = $form->getInput("user_calendar_view");
                $cal_period = $form->getInput("user_cal_period");
                if ($obj_id && ($starting_point->getStartingPoint() == ilUserUtil::START_REPOSITORY_OBJ)) {
                    if (ilObject::_lookupObjId($obj_id) && !$tree->isDeleted($obj_id)) {
                        $starting_point->setStartingObject($obj_id);
                        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                    } else {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("obj_ref_id_not_exist"), true);
                    }
                } else {
                    $starting_point->setStartingObject(0);
                }

                if (!empty($cal_view) && !empty($cal_period) && ($starting_point->getStartingPoint() == ilUserUtil::START_PD_CALENDAR)) {
                    $starting_point->setCalendarView($cal_view);
                    $starting_point->setCalendarPeriod($cal_period);
                } else {
                    $starting_point->setCalendarView(0);
                    $starting_point->setCalendarPeriod(0);
                }

                if ($start_point_id) {
                    $starting_point->update();
                } else {
                    $starting_point->save();
                }
            } elseif (!empty($form->getInput("user_calendar_view")) && !empty($form->getInput("user_cal_period"))) {
                $calendar_info = [
                    "user_calendar_view" => $form->getInput("user_calendar_view"),
                    "user_cal_period" => $form->getInput("user_cal_period")
                ];
                ilUserUtil::setStartingPoint($form->getInput('start_point'), (int) $form->getInput('start_object'), $calendar_info);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            } else {  //default
                ilUserUtil::setStartingPoint($form->getInput('start_point'), (int) $form->getInput('start_object'));
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            }

            $ilCtrl->redirect($this, "startingPoints");
        }
        $form->setValuesByPost();
        $tpl->setContent($form->getHTML());
    }

    protected function showRoleSelection(
        string $role,
        string $role_search,
        string $start_point,
        string $start_object
    ) : void {
        $parser = new ilQueryParser($role_search);
        $parser->setMinWordLength(1);
        $parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
        $parser->parse();

        $object_search = new ilLikeObjectSearch($parser);
        $object_search->setFilter(array('role'));
        $res = $object_search->performSearch();

        $entries = $res->getEntries();

        $table = new ilRoleSelectionTableGUI($this, 'saveStartingPoint');
        $table->setLimit(9999);
        $table->disable("sort");
        $table->addHiddenInput("role_search", $role_search);
        $table->addHiddenInput("start_point", $start_point);
        $table->addHiddenInput("start_object", $start_object);
        $table->addHiddenInput("role", $role);
        $table->addHiddenInput("role_type", 1);
        $table->setTitle($this->lng->txt('user_role_selection'));
        $table->addMultiCommand('saveStartingPoint', $this->lng->txt('user_choose_role'));
        $table->parse($entries);

        $this->tpl->setContent($table->getHTML());
    }

    public function saveOrder() : void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess("write", $this->parent_ref_id)) {
            throw new ilPermissionException($this->lng->txt("msg_no_perm_read"));
        }

        $positions = $this->user_request->getPositions();
        if (count($positions) > 0) {
            $sp = new ilStartingPoint();
            $sp->saveOrder($positions);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "startingPoints");
    }

    /**
     * Confirm delete starting point
     */
    public function confirmDeleteStartingPoint() : void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt('back_to_starting_points_list'), $ilCtrl->getLinkTarget($this, 'startingPoints'));

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($ilCtrl->getFormAction($this));
        $conf->setHeaderText($lng->txt('confirm_delete_starting_point'));

        $req_role_id = $this->user_request->getRoleId();
        $req_sp_id = $this->user_request->getStartingPointId();

        //if type role based
        if ($req_role_id && $req_sp_id) {
            $rolid = $req_role_id;
            $spid = $req_sp_id;

            $role = new ilObjRole($rolid);

            $conf->addItem('rolid', $rolid, $role->getTitle());
            $conf->addItem('spid', $spid, "");
        }

        $conf->setConfirm($lng->txt('delete'), 'deleteStartingPoint');
        $conf->setCancel($lng->txt('cancel'), 'startingPoints');

        $tpl->setContent($conf->getHTML());
    }

    /**
     * Set to 0 the starting point values
     */
    protected function deleteStartingPoint() : void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $rbacsystem = $DIC['rbacsystem'];
        $spid = 0;

        if (!$rbacsystem->checkAccess("write", $this->parent_ref_id)) {
            throw new ilPermissionException($this->lng->txt("msg_no_perm_read"));
        }

        $spoint_id = $this->user_request->getStartingPointId();
        $req_role_id = $this->user_request->getRoleId();

        if ($req_role_id && $spid = $spoint_id) {
            $sp = new ilStartingPoint($spid);
            $sp->delete();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_spoint_not_modified"), true);
        }
        $ilCtrl->redirect($this, "startingPoints");
    }
}
