<?php declare(strict_types=0);
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
 * Class ilObjUserTrackingGUI
 * @author       Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilLearningProgressGUI: ilLPListOfObjectsGUI, ilLPListOfSettingsGUI, ilLPListOfProgressGUI
 * @ilCtrl_Calls ilLearningProgressGUI: ilLPObjectStatisticsGUI
 */
class ilLearningProgressGUI extends ilLearningProgressBaseGUI
{
    /**
     * execute command
     */
    public function executeCommand()
    {
        $this->ctrl->setReturn($this, "");

        // E.g personal desktop mode needs locator header icon ...
        $this->__buildHeader();
        switch ($this->__getNextClass()) {
            case 'illplistofprogressgui':

                $this->help->setScreenIdComponent(
                    "lp_" . ilObject::_lookupType($this->getRefId(), true)
                );

                $this->__setSubTabs(self::LP_ACTIVE_PROGRESS);
                $this->__setCmdClass('illplistofprogressgui');
                $lop_gui = new ilLPListOfProgressGUI(
                    $this->getMode(),
                    $this->getRefId(),
                    $this->getUserId()
                );
                $this->ctrl->forwardCommand($lop_gui);
                break;

            case 'illplistofobjectsgui':
                if ($this->getRefId() &&
                    !ilLearningProgressAccess::checkPermission(
                        'read_learning_progress',
                        $this->getRefId()
                    )) {
                    return;
                }

                if (stristr($this->ctrl->getCmd(), "matrix")) {
                    $this->__setSubTabs(self::LP_ACTIVE_MATRIX);
                } elseif (stristr($this->ctrl->getCmd(), "summary")) {
                    $this->__setSubTabs(self::LP_ACTIVE_SUMMARY);
                } else {
                    $this->__setSubTabs(self::LP_ACTIVE_OBJECTS);
                }
                $loo_gui = new ilLPListOfObjectsGUI(
                    $this->getMode(),
                    $this->getRefId()
                );
                $this->__setCmdClass('illplistofobjectsgui');
                $this->ctrl->forwardCommand($loo_gui);
                break;

            case 'illplistofsettingsgui':
                if ($this->getRefId() &&
                    !ilLearningProgressAccess::checkPermission(
                        'edit_learning_progress',
                        $this->getRefId()
                    )) {
                    return;
                }

                $this->__setSubTabs(self::LP_ACTIVE_SETTINGS);
                $los_gui = new ilLPListOfSettingsGUI(
                    $this->getMode(),
                    $this->getRefId()
                );
                $this->__setCmdClass('illplistofsettingsgui');
                $this->ctrl->forwardCommand($los_gui);
                break;

            case 'illpobjectstatisticsgui':
                if (stristr($this->ctrl->getCmd(), "access")) {
                    $this->__setSubTabs(self::LP_ACTIVE_OBJSTATACCESS);
                } elseif (stristr($this->ctrl->getCmd(), "types")) {
                    $this->__setSubTabs(self::LP_ACTIVE_OBJSTATTYPES);
                } elseif (stristr($this->ctrl->getCmd(), "daily")) {
                    $this->__setSubTabs(self::LP_ACTIVE_OBJSTATDAILY);
                } else {
                    $this->__setSubTabs(self::LP_ACTIVE_OBJSTATADMIN);
                }
                $this->__setCmdClass('illpobjectstatisticsgui');
                $ost_gui = new ilLPObjectStatisticsGUI(
                    $this->getMode(),
                    $this->getRefId()
                );
                $this->ctrl->forwardCommand($ost_gui);
                break;

            default:
                $cmd = $this->ctrl->getCmd();
                if (!$cmd) {
                    return;
                }
                $this->$cmd();
                $this->tpl->printToStdout();
                break;
        }

        // E.G personal desktop mode needs $tpl->show();
        $this->__buildFooter();
    }

    public function __setCmdClass(string $a_class) : void
    {
        // If cmd class == 'illearningprogressgui' the cmd class is set to the the new forwarded class
        // otherwise e.g illplistofprogressgui tries to forward (back) to illearningprogressgui.
        if ($this->ctrl->getCmdClass() == strtolower(get_class($this))) {
            $this->ctrl->setCmdClass(strtolower($a_class));
        }
    }

    public function __getNextClass() : string
    {
        // #9857
        if (!ilObjUserTracking::_enabledLearningProgress()) {
            return '';
        }

        if (strlen($next_class = $this->ctrl->getNextClass())) {
            if ($this->getMode() == self::LP_CONTEXT_PERSONAL_DESKTOP) {
                ilSession::set('il_lp_history', $next_class);
            }
            return $next_class;
        }
        switch ($this->getMode()) {
            case self::LP_CONTEXT_ADMINISTRATION:
                return 'illplistofobjectsgui';

            case self::LP_CONTEXT_REPOSITORY:
                $cmd = $this->ctrl->getCmd();
                if (in_array(
                    $cmd,
                    array("editManual", "updatemanual", "showtlt")
                )) {
                    return "";
                }

                // #12771
                $olp = ilObjectLP::getInstance(
                    ilObject::_lookupObjId($this->getRefId())
                );
                if (!$olp->isActive()) {
                    if (!($olp instanceof ilPluginLP) &&
                        ilLearningProgressAccess::checkPermission(
                            'edit_learning_progress',
                            $this->getRefId()
                        )) {
                        return 'illplistofsettingsgui';
                    } else {
                        return '';
                    }
                }

                if (!$this->anonymized &&
                    ilLearningProgressAccess::checkPermission(
                        'read_learning_progress',
                        $this->getRefId()
                    )) {
                    return 'illplistofobjectsgui';
                }
                if (
                ilLearningProgressAccess::checkPermission(
                    'edit_learning_progress',
                    $this->getRefId()
                )) {
                    return 'illplistofsettingsgui';
                }
                return 'illplistofprogressgui';

            case self::LP_CONTEXT_PERSONAL_DESKTOP:

                $has_edit = ilObjUserTracking::_hasLearningProgressOtherUsers();
                $has_personal = ilObjUserTracking::_hasLearningProgressLearner(
                );

                if ($has_edit || $has_personal) {
                    // default (#10928)
                    $tgt = null;
                    if ($has_personal) {
                        $tgt = 'illplistofprogressgui';
                    } elseif ($has_edit) {
                        $tgt = 'illplistofobjectsgui';
                    }

                    // validate session
                    switch (ilSession::get('il_lp_history')) {
                        case 'illplistofobjectsgui':
                            if (!$has_edit) {
                                ilSession::clear('il_lp_history');
                            }
                            break;

                        case 'illplistofprogressgui':
                            if (!$has_personal) {
                                ilSession::clear('il_lp_history');
                            }
                            break;
                    }

                    if (ilSession::get('il_lp_history')) {
                        return ilSession::get('il_lp_history');
                    } elseif ($tgt) {
                        return $tgt;
                    }
                }

                // should not happen
                ilUtil::redirect("ilias.php?baseClass=ilDashboardGUI");

            // no break
            case self::LP_CONTEXT_USER_FOLDER:
            case self::LP_CONTEXT_ORG_UNIT:
                if (ilObjUserTracking::_enabledUserRelatedData()) {
                    return 'illplistofprogressgui';
                }
                break;
        }
        return '';
    }

    /**
     * Show progress screen for "edit manual"
     */
    protected function editManual() : void
    {
        if (ilLearningProgressAccess::checkAccess($this->getRefId())) {
            $olp = ilObjectLP::getInstance(
                ilObject::_lookupObjId($this->getRefId())
            );
            if ($olp->getCurrentMode(
                ) == ilLPObjSettings::LP_MODE_COLLECTION_MANUAL) {
                $form = $this->initCollectionManualForm();
                $this->tpl->setContent($form->getHTML());
            }
        }
    }

    protected function initCollectionManualForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "updatemanual"));
        $form->setTitle($this->lng->txt("learning_progress"));
        $form->setDescription(
            $this->lng->txt("trac_collection_manual_learner_info")
        );

        $coll_items = array();

        $olp = ilObjectLP::getInstance($this->getObjId());
        $collection = $olp->getCollectionInstance();
        $subitem_info = '';
        $subitem_title = '';
        $possible_items = [];
        if ($collection) {
            $coll_items = $collection->getItems();
            $possible_items = $collection->getPossibleItems(
                $this->getRefId()
            ); // for titles

            switch (ilObject::_lookupType($this->getObjId())) {
                case "lm":
                    $subitem_title = $this->lng->txt("objs_st");
                    $subitem_info = $this->lng->txt(
                        "trac_collection_manual_learner_lm_info"
                    );
                    break;
            }
        }

        $class = ilLPStatusFactory::_getClassById(
            $this->getObjId(),
            ilLPObjSettings::LP_MODE_COLLECTION_MANUAL
        );
        $lp_data = $class::_getObjectStatus($this->getObjId(), $this->usr_id);

        $icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG);

        $grp = new ilCheckboxGroupInputGUI($subitem_title, "sids");
        $grp->setInfo($subitem_info);
        $form->addItem($grp);

        // #14994 - using possible items for proper sorting

        $completed = array();
        foreach (array_keys($possible_items) as $item_id) {
            if (!in_array($item_id, $coll_items)) {
                continue;
            }

            $info = null;
            $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;

            if (isset($lp_data[$item_id])) {
                $changed = new ilDateTime($lp_data[$item_id][1], IL_CAL_UNIX);
                $info = $this->lng->txt(
                    "trac_collection_manual_learner_changed_ts"
                ) . ": " .
                    ilDatePresentation::formatDate($changed);

                if ($lp_data[$item_id][0]) {
                    $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                    $completed[] = $item_id;
                }
            }

            $icon = $icons->renderIconForStatus($status);

            $opt = new ilCheckboxOption(
                $icon . " " . $possible_items[$item_id]["title"],
                $item_id
            );
            if ($info) {
                $opt->setInfo($info);
            }
            $grp->addOption($opt);
        }

        if ($completed) {
            $grp->setValue($completed);
        }

        $form->addCommandButton("updatemanual", $this->lng->txt("save"));

        return $form;
    }

    protected function updateManual() : void
    {
        if (ilLearningProgressAccess::checkAccess($this->getRefId())) {
            $olp = ilObjectLP::getInstance(
                ilObject::_lookupObjId($this->getRefId())
            );
            if ($olp->getCurrentMode(
                ) == ilLPObjSettings::LP_MODE_COLLECTION_MANUAL) {
                $form = $this->initCollectionManualForm();
                if ($form->checkInput()) {
                    $class = ilLPStatusFactory::_getClassById(
                        $this->getObjId(),
                        ilLPObjSettings::LP_MODE_COLLECTION_MANUAL
                    );
                    $class::_setObjectStatus(
                        $this->getObjId(),
                        $this->usr_id,
                        $form->getInput("sids")
                    );

                    $this->tpl->setOnScreenMessage(
                        'success',
                        $this->lng->txt(
                            "settings_saved"
                        ),
                        true
                    );
                }

                $this->ctrl->redirect($this, "editManual");
            }
        }
    }

    protected function showtlt()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "showtlt"));
        $form->setTitle($this->lng->txt("learning_progress"));
        $form->setDescription(
            $this->lng->txt("trac_collection_tlt_learner_info")
        );

        $coll_items = array();

        $olp = ilObjectLP::getInstance($this->getObjId());
        $collection = $olp->getCollectionInstance();
        $possible_items = [];
        if ($collection) {
            $coll_items = $collection->getItems();
            $possible_items = $collection->getPossibleItems(
                $this->getRefId()
            ); // for titles
        }

        $class = ilLPStatusFactory::_getClassById(
            $this->getObjId(),
            ilLPObjSettings::LP_MODE_COLLECTION_TLT
        );
        $info = $class::_getStatusInfo($this->getObjId(), true);

        $icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG);

        foreach ($coll_items as $item_id) {
            // #16599 - deleted items should not be displayed
            if (!array_key_exists($item_id, $possible_items)) {
                continue;
            }

            $field = new ilCustomInputGUI($possible_items[$item_id]["title"]);

            // lp status
            $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
            if (isset($info["completed"][$item_id]) &&
                in_array($this->user->getId(), $info["completed"][$item_id])) {
                $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
            } elseif (isset($info["in_progress"][$item_id]) &&
                in_array(
                    $this->user->getId(),
                    $info["in_progress"][$item_id]
                )) {
                $status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
            }
            $field->setHtml($icons->renderIconForStatus($status));

            // stats
            $spent = 0;
            if (isset($info["tlt_users"][$item_id][$this->user->getId()])) {
                $spent = $info["tlt_users"][$item_id][$this->user->getId()];
            }
            $needed = $info["tlt"][$item_id];
            if ($needed) {
                $field->setInfo(
                    sprintf(
                        $this->lng->txt("trac_collection_tlt_learner_subitem"),
                        ilDatePresentation::secondsToString($spent),
                        ilDatePresentation::secondsToString($needed),
                        min(100, round(abs($spent) / $needed * 100))
                    )
                );
            }

            $form->addItem($field);
        }

        $this->tpl->setContent($form->getHTML());
    }
}
