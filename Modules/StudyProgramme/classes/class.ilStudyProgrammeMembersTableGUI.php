<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilObjStudyProgrammeMembersTableGUI
 */
class ilStudyProgrammeMembersTableGUI extends ilTable2GUI
{
    const COLUMNS = [
        //column, langvar, optional, if_lp_children, if_no_lp_children
        ['name', 'name', false, true, true],
        ['login', 'login', false, true, true],
        ['prg_orgus', 'prg_orgus', true, true, true],
        ['prg_status', 'prg_status', false, true, true],
        ['prg_completion_date', 'prg_completion_date', true, true, true],
        ['prg_completion_by', 'prg_completion_by', true, true, true],
        ['points', 'prg_points_reachable', false, true, false],
        ['points', 'prg_points_required', false, false, true],
        ['points_current', 'prg_points_current', false, false, true],
        ['prg_custom_plan', 'prg_custom_plan', true, true, true],
        ['prg_belongs_to', 'prg_belongs_to', true, true, true],
        ['prg_assign_date', 'prg_assign_date', false, true, true],
        ['prg_assigned_by', 'prg_assigned_by', true, true, true],
        ['prg_deadline', 'prg_deadline', true, true, true],
        ['prg_expiry_date', 'prg_expiry_date', true, true, true],
        ['prg_validity', 'prg_validity', true, true, true],
        [null, 'action', false, true, true]
    ];

    const OPTION_ALL = -1;
    const VALIDITY_OPTION_VALID = 1;
    const VALIDITY_OPTION_RENEWAL_REQUIRED = 3;

    /**
     * @var int
     */
    protected $prg_obj_id;

    /**
     * @var int
     */
    protected $prg_ref_id;

    /**
     * @var bool
     */
    protected $prg_has_lp_children;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var mixed
     */
    protected $ui_factory;

    /**
     * @var mixed
     */
    protected $ui_renderer;

    /**
     * @var ilStudyProgrammeUserProgressDB
     */
    protected $sp_user_progress_db;

    /**
     * @var ilObjStudyProgramme
     */
    protected $prg;

    public function __construct(
        int $prg_obj_id,
        int $prg_ref_id,
        ilObjStudyProgrammeMembersGUI $parent_obj,
        string $parent_cmd = '',
        string $template_context = '',
        ilStudyProgrammeUserProgressDB $sp_user_progress_db,
        ilStudyProgrammePositionBasedAccess $position_based_access
    ) {
        $this->setId("sp_member_list");
        parent::__construct($parent_obj, $parent_cmd, $template_context);

        $this->prg_obj_id = $prg_obj_id;
        $this->prg_ref_id = $prg_ref_id;
        $this->position_based_access = $position_based_access;

        $this->prg = ilObjStudyProgramme::getInstanceByRefId($prg_ref_id);
        $this->prg_has_lp_children = $parent_obj->getStudyProgramme()->hasLPChildren();

        global $DIC;
        $this->db = $DIC['ilDB'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];

        $this->setEnableTitle(true);
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        // TODO: switch this to internal sorting/segmentation
        $this->setExternalSorting(false);
        $this->setExternalSegmentation(true);
        $this->setRowTemplate("tpl.members_table_row.html", "Modules/StudyProgramme");
        $this->setShowRowsSelector(false);
        $this->setFormAction($this->ctrl->getFormAction($parent_obj, "view"));
        $this->addColumn("", "", "1", true);
        $this->setSelectAllCheckbox("prgs_ids[]");
        $this->setEnableAllCommand(true);
        $this->addMultiCommands();

        $selected = $this->getSelectedColumns();
        foreach (self::COLUMNS as $column) {
            list($col, $lng_var, $optional, $lp, $no_lp) = $column;

            $show_by_lp = ($this->prg_has_lp_children && $lp) || (!$this->prg_has_lp_children && $no_lp);
            $show_optional = !$optional || ($optional && array_key_exists($col, $selected));

            if ($show_by_lp && $show_optional) {
                $this->addColumn($this->lng->txt($lng_var), $col);
            }
        }

        $this->sp_user_progress_db = $sp_user_progress_db;

        $this->initFilter();
        $filter_values = $this->getFilterValues();

        $this->determineOffsetAndOrder();
        $this->determineLimit();

        $members_list = $this->fetchData(
            $prg_obj_id,
            $this->getLimit(),
            $this->getOffset(),
            $this->getOrderField(),
            $this->getOrderDirection(),
            $filter_values
        );

        $this->setMaxCount($this->countFetchData($prg_obj_id, $filter_values));
        $this->setData($members_list);
    }


    protected function fillRow($a_set) : void
    {
        $usr_id = (int) $a_set['usr_id'];

        $may_read_learning_progress =
            !$this->prg->getAccessControlByOrguPositionsGlobal() ||
            in_array($usr_id, $this->getParentObject()->readLearningProgress())
        ;

        $this->tpl->setCurrentBlock("checkb");
        $this->tpl->setVariable("ID", $a_set["prgrs_id"]);
        $this->tpl->parseCurrentBlock();
        $this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
        $this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
        $this->tpl->setVariable("LOGIN", $a_set["login"]);
        $this->tpl->setVariable(
            "STATUS",
            $this->getValueOrEmptyString(
                $may_read_learning_progress,
                $this->sp_user_progress_db->statusToRepr($a_set["status"])
            )
        );
        $this->tpl->setVariable("POINTS_REQUIRED", $a_set["points"]);
        $this->tpl->setVariable("ASSIGN_DATE", $a_set["prg_assign_date"]);

        if (!$this->prg_has_lp_children) {
            $this->tpl->setCurrentBlock("points_current");
            $this->tpl->setVariable(
                "POINTS_CURRENT",
                $this->getValueOrEmptyString($may_read_learning_progress, $a_set['points_current'])
            );
            $this->tpl->parseCurrentBlock();
        }

        foreach ($this->getSelectedColumns() as $column) {
            switch ($column) {
                case "prg_orgus":
                    $this->tpl->setVariable("ORGUS", $a_set["orgus"]);
                    break;
                case "prg_completion_date":
                    $completion_date = '';
                    if ($may_read_learning_progress) {
                        $completion_date = $a_set["completion_date"];
                    }
                    $this->tpl->setVariable("COMPLETION_DATE", $completion_date);
                    break;
                case "prg_completion_by":
                    if (is_null($a_set["completion_by"])) {
                        $this->tpl->touchBlock("comp_by");
                    } else {
                        $this->tpl->setVariable(
                            "COMPLETION_BY",
                            $this->getValueOrEmptyString(
                                $may_read_learning_progress,
                                $a_set["completion_by"]
                            )
                        );
                    }

                    break;
                case "prg_custom_plan":
                    $has_changers = $this->lng->txt("no");
                    if ($a_set["last_change_by"]) {
                        $has_changers = $this->lng->txt("yes");
                    }

                    $this->tpl->setVariable(
                        "CUSTOM_PLAN",
                        $this->getValueOrEmptyString($may_read_learning_progress, $has_changers)
                    );
                    break;
                case "prg_belongs_to":
                    $this->tpl->setVariable("BELONGS_TO", $a_set["belongs_to"]);
                    break;
                case "prg_expiry_date":
                    $this->tpl->setVariable(
                        "EXPIRY_DATE",
                        $this->getValueOrEmptyString($may_read_learning_progress, $a_set["vq_date"])
                    );
                    break;
                case "prg_assigned_by":
                    $assigned_by = $a_set["prg_assigned_by"];
                    if (is_null($assigned_by)) {
                        $srcs = array_flip(ilStudyProgrammeAutoMembershipSource::SOURCE_MAPPING);
                        $assignment_src = (int) $a_set['prg_assignment_origin'];
                        $assigned_by = $this->lng->txt('prg_autoassignment')
                            . ' ' . $this->lng->txt($srcs[$assignment_src]);
                    }
                    $this->tpl->setVariable("ASSIGNED_BY", $assigned_by);
                    break;
                case "prg_deadline":
                    if (is_null($a_set["prg_deadline"])) {
                        $this->tpl->touchBlock("deadline");
                    } else {
                        $this->tpl->setVariable(
                            "DEADLINE",
                            $this->getValueOrEmptyString(
                                $may_read_learning_progress,
                                $a_set["prg_deadline"]
                            )
                        );
                    }
                    break;
                case "prg_validity":
                    $this->tpl->setVariable(
                        "VALIDITY",
                        $this->getValueOrEmptyString(
                            $may_read_learning_progress,
                            $a_set['prg_validity']
                        )
                    );
                    break;

            }
        }
        $this->tpl->setVariable(
            "ACTIONS",
            $this->buildActionDropDown(
                $a_set["actions"],
                $a_set["prgrs_id"],
                $a_set["assignment_id"],
                $usr_id
            )
        );
    }

    protected function getValueOrEmptyString(string $condition, string $value) : string
    {
        if ($condition) {
            return $value;
        }

        return '';
    }

    /**
     * Builds the action menu for each row of the table
     */
    protected function buildActionDropDown(
        array $actions,
        int $prgrs_id,
        int $ass_id,
        int $usr_id
    ) : string {
        $l = new ilAdvancedSelectionListGUI();

        $access_by_position = $this->isPermissionControlledByOrguPosition();
        $parent = $this->getParentObject();

        $view_individual_plan = $parent->isOperationAllowedForUser(
            $usr_id,
            ilOrgUnitOperation::OP_VIEW_INDIVIDUAL_PLAN
        );

        $edit_individual_plan = $parent->isOperationAllowedForUser(
            $usr_id,
            ilOrgUnitOperation::OP_VIEW_INDIVIDUAL_PLAN
        );


        foreach ($actions as $action) {
            switch ($action) {
                case ilStudyProgrammeUserProgress::ACTION_MARK_ACCREDITED:
                case ilStudyProgrammeUserProgress::ACTION_UNMARK_ACCREDITED:
                    if (!$edit_individual_plan) {
                        continue 2;
                    }
                    break;
                case ilStudyProgrammeUserProgress::ACTION_SHOW_INDIVIDUAL_PLAN:
                    if (!$view_individual_plan) {
                        continue 2;
                    }
                    break;
                case ilStudyProgrammeUserProgress::ACTION_REMOVE_USER:
                    $manage_members =
                        $parent->isOperationAllowedForUser($usr_id, ilOrgUnitOperation::OP_MANAGE_MEMBERS)
                        && in_array($usr_id, $this->getParentObject()->getLocalMembers());

                    if (!$manage_members) {
                        continue 2;
                    }
                    break;
            }
            $target = $this->getLinkTargetForAction($action, $prgrs_id, $ass_id);
            $l->addItem($this->lng->txt("prg_$action"), $action, $target);
        }

        return $l->getHTML();
    }

    /**
     * Get ilias link for action
     */
    protected function getLinkTargetForAction(string $action, int $prgrs_id, int $ass_id) : string
    {
        return $this->getParentObject()->getLinkTargetForAction($action, $prgrs_id, $ass_id);
    }

    /**
     * Get data for table
     */
    protected function fetchData(
        int $prg_id,
        int $limit = null,
        int $offset = null,
        string $order_column = null,
        string $order_direction = null,
        array $filter = []
    ) : array {
        // TODO: Reimplement this in terms of ActiveRecord when innerjoin
        // supports the required rename functionality

        $accredited = $this->db->quote(ilStudyProgrammeProgress::STATUS_ACCREDITED, 'integer');

        $sql =
             "SELECT" . PHP_EOL
            . "prgrs.id AS prgrs_id," . PHP_EOL
            . "pcp.firstname," . PHP_EOL
            . "pcp.lastname," . PHP_EOL
            . "pcp.login," . PHP_EOL
            . "pcp.usr_id," . PHP_EOL
            . "prgrs.usr_id," . PHP_EOL
            . "prgrs.points," . PHP_EOL
            . "prgrs.points_cur * ABS(prgrs.status - $accredited) /" . PHP_EOL
            . "    (GREATEST(ABS(prgrs.status - $accredited),1))" . PHP_EOL
            . "+ prgrs.points * (1 - ABS(prgrs.status - $accredited) /" . PHP_EOL
            . "    (GREATEST(ABS(prgrs.status - $accredited),1))) AS points_current," . PHP_EOL
            . "prgrs.last_change_by," . PHP_EOL
            . "prgrs.status," . PHP_EOL
            . "blngs.title AS belongs_to," . PHP_EOL
            . "cmpl_usr.login AS accredited_by," . PHP_EOL
            . "cmpl_obj.title AS completion_by," . PHP_EOL
            . "cmpl_obj.type AS completion_by_type," . PHP_EOL
            . "prgrs.completion_by AS completion_by_id," . PHP_EOL
            . "prgrs.assignment_id AS assignment_id," . PHP_EOL
            . "prgrs.completion_date," . PHP_EOL
            . "prgrs.vq_date," . PHP_EOL
            . "prgrs.deadline AS prg_deadline," . PHP_EOL
            . "ass.root_prg_id AS root_prg_id," . PHP_EOL
            . "ass.last_change AS prg_assign_date," . PHP_EOL
            . "ass.last_change_by AS prg_assingment_origin," . PHP_EOL
            . "ass_usr.login AS prg_assigned_by," . PHP_EOL
            . "CONCAT(pcp.firstname, pcp.lastname) AS name," . PHP_EOL
            . "(prgrs.last_change_by IS NOT NULL) AS custom_plan" . PHP_EOL
        ;

        $sql .= $this->getFrom();
        $sql .= $this->getWhere($prg_id);
        $sql .= $this->getFilterWhere($filter);
        $sql .= $this->getOrguValidUsersFilter();

        if ($limit !== null) {
            $this->db->setLimit($limit, $offset !== null ? $offset : 0);
        }

        $res = $this->db->query($sql);
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $members_list = array();


        while ($rec = $this->db->fetchAssoc($res)) {
            $rec["actions"] = ilStudyProgrammeUserProgress::getPossibleActions(
                $prg_id,
                $rec["root_prg_id"],
                $rec["status"]
            );

            $rec['points_current'] = number_format($rec['points_current']);
            if ($rec["status"] == ilStudyProgrammeProgress::STATUS_COMPLETED) {
                //If the status completed is set by crs reference
                //use crs title
                if ($rec["completion_by_type"] == "crsr") {
                    $completion_id = $rec["completion_by_id"];
                    $title = ilContainerReference::_lookupTitle($completion_id);
                    $ref_id = ilContainerReference::_lookupTargetRefId($completion_id);
                    if (
                        ilObject::_exists($ref_id, true) &&
                        is_null(ilObject::_lookupDeletedDate($ref_id))
                    ) {
                        $url = ilLink::_getStaticLink($ref_id, "crs");
                        $link = $this->ui_factory->link()->standard($title, $url);
                        $rec["completion_by"] = $this->ui_renderer->render($link);
                    } else {
                        $rec["completion_by"] = $title;
                    }
                }

                // If the status completed and there is a non-null completion_by field
                // in the set, this means the completion was achieved by some leaf in
                // the program tree.
                if (!$rec["completion_by"]) {
                    $prgrs = $this->sp_user_progress_db->getInstanceForAssignment(
                        $this->prg_obj_id,
                        $rec["assignment_id"]
                    );

                    $rec["completion_by"] = implode(
                        ", ",
                        $prgrs->getNamesOfCompletedOrAccreditedChildren()
                    );
                }
                // This case should only occur if the status completed is set
                // by an already deleted crs.
                if (!$rec["completion_by"]) {
                    $title = ilObjectDataDeletionLog::get($rec["completion_by_id"]);
                    if (!is_null($title["title"])) {
                        $rec["completion_by"] = $title["title"];
                    }
                }
            } elseif ($rec["status"] == ilStudyProgrammeProgress::STATUS_ACCREDITED) {
                $rec["completion_by"] = $rec["accredited_by"];
            }

            if (!$rec['completion_date']) {
                $rec['completion_date'] = '';
            }

            if ($rec['vq_date']) {
                $rec['prg_validity'] = $this->lng->txt('prg_not_valid');
                if ($rec["vq_date"] > $now) {
                    $rec['prg_validity'] = $this->lng->txt('prg_still_valid');
                }
            } else {
                $rec['prg_validity'] = '';
                $rec['vq_date'] = '';
            }

            $usr_id = (int) $rec['usr_id'];
            $rec["orgus"] = ilObjUser::lookupOrgUnitsRepresentation($usr_id);
            $members_list[] = $rec;
        }

        return $members_list;
    }

    /**
     * Get maximum number of rows the table could have
     */
    protected function countFetchData(int $prg_id, array $filter = []) : int
    {
        // TODO: Reimplement this in terms of ActiveRecord when innerjoin
        // supports the required rename functionality
        $query = "SELECT count(prgrs.id) as cnt" . PHP_EOL;
        $query .= $this->getFrom();
        $query .= $this->getWhere($prg_id);
        $query .= $this->getFilterWhere($filter);

        $res = $this->db->query($query);
        $rec = $this->db->fetchAssoc($res);

        return $rec["cnt"];
    }

    protected function getFrom() : string
    {
        return
            "FROM " . ilStudyProgrammeProgressDBRepository::TABLE . " prgrs" . PHP_EOL
            . "JOIN usr_data pcp ON pcp.usr_id = prgrs.usr_id" . PHP_EOL
            . "JOIN " . ilStudyProgrammeAssignmentDBRepository::TABLE . " ass" . PHP_EOL
            . "   ON ass.id = prgrs.assignment_id" . PHP_EOL
            . "JOIN object_data blngs ON blngs.obj_id = ass.root_prg_id" . PHP_EOL
            . "LEFT JOIN usr_data ass_usr ON ass_usr.usr_id = ass.last_change_by" . PHP_EOL
            . "LEFT JOIN usr_data cmpl_usr ON cmpl_usr.usr_id = prgrs.completion_by" . PHP_EOL
            . "LEFT JOIN object_data cmpl_obj ON cmpl_obj.obj_id = prgrs.completion_by" . PHP_EOL
        ;
    }

    /**
     * Get the sql part WHERE
     */
    protected function getWhere(int $prg_id) : string
    {
        $q = "WHERE prgrs.prg_id = " . $this->db->quote($prg_id, "integer") . PHP_EOL;

        if ($this->prg->getAccessControlByOrguPositionsGlobal() && !$this->parent_obj->mayManageMembers()) {
            $visible = $this->getParentObject()->visibleUsers();
            if (count($visible) > 0) {
                $q .= "	AND " . $this->db->in("prgrs.usr_id", $visible, false, "integer") . PHP_EOL;
            } else {
                $q .= " AND FALSE" . PHP_EOL;
            }
        }

        return $q;
    }

    /**
     * Get selectable columns
     */
    public function getSelectableColumns() : array
    {
        $cols = [];
        foreach (self::COLUMNS as $column) {
            list($col, $lng_var, $optional, $lp, $no_lp) = $column;
            if ($optional) {
                $cols[$col] = ["txt" => $this->lng->txt($lng_var)];
            }
        }

        return $cols;
    }

    /**
     * Add multicommands to table
     */
    protected function addMultiCommands() : void
    {
        foreach ($this->getMultiCommands() as $cmd => $caption) {
            $this->addMultiCommand($cmd, $caption);
        }
    }

    /**
     * Get possible multicommnds
     *
     * @return string[]
     */
    protected function getMultiCommands() : array
    {
        $access_by_position = $this->isPermissionControlledByOrguPosition();
        if ($access_by_position) {
            $edit_individual_plan = count($this->getParentObject()->editIndividualPlan()) > 0;
            $manage_members = count($this->getParentObject()->manageMembers()) > 0;
        } else {
            $edit_individual_plan = true;
            $manage_members = true;
        }

        $perms = [];
        if ($edit_individual_plan) {
            $perms['markAccreditedMulti'] = $this->lng->txt('prg_multi_mark_accredited');
            $perms['unmarkAccreditedMulti'] = $this->lng->txt('prg_multi_unmark_accredited');
        }

        if ($manage_members) {
            $perms['removeUserMulti'] = $this->lng->txt('prg_multi_remove_user');
        }
        $perms = array_merge(
            $perms,
            [
                'markRelevantMulti' => $this->lng->txt('prg_multi_mark_relevant'),
                'markNotRelevantMulti' => $this->lng->txt('prg_multi_unmark_relevant'),
                'updateFromCurrentPlanMulti' => $this->lng->txt('prg_multi_update_from_current_plan'),
                'changeDeadlineMulti' => $this->lng->txt('prg_multi_change_deadline'),
                'changeExpireDateMulti' => $this->lng->txt('prg_multi_change_expire_date')
            ]
        );

        return $perms;
    }

    /**
     * Get options of filter "validity".
     */
    protected function getValidityOptions() : array
    {
        return [
            self::VALIDITY_OPTION_VALID => $this->lng->txt("prg_still_valid"),
            self::VALIDITY_OPTION_RENEWAL_REQUIRED => $this->lng->txt("prg_not_valid")
        ];
    }

    /**
     * Get options of filter "status".
     */
    protected function getStatusOptions() : array
    {
        return [
            ilStudyProgrammeProgress::STATUS_IN_PROGRESS => $this->lng->txt("prg_status_in_progress"),
            ilStudyProgrammeProgress::STATUS_COMPLETED => $this->lng->txt("prg_status_completed"),
            ilStudyProgrammeProgress::STATUS_ACCREDITED => $this->lng->txt("prg_status_accredited"),
            ilStudyProgrammeProgress::STATUS_NOT_RELEVANT => $this->lng->txt("prg_status_not_relevant"),
            ilStudyProgrammeProgress::STATUS_FAILED => $this->lng->txt("prg_status_failed")
        ];
    }

    /**
     * @inheritdoc
     */
    public function initFilter() : void
    {
        $item = $this->addFilterItemByMetaType('prg_validity', self::FILTER_SELECT);
        $item->setOptions(
            [self::OPTION_ALL => $this->lng->txt("all")] + $this->getValidityOptions()
        );

        $item = $this->addFilterItemByMetaType('prg_status', self::FILTER_SELECT);
        $item->setOptions(
            [self::OPTION_ALL => $this->lng->txt("all")] + $this->getStatusOptions()
        );

        $this->addFilterItemByMetaType('name', self::FILTER_TEXT);
        $this->addFilterItemByMetaType('prg_expiry_date', self::FILTER_DATE_RANGE);
    }

    /**
     * Get filter-values by field id.
     */
    protected function getFilterValues() : array
    {
        $f = [];
        foreach ($this->filters as $item) {
            $f[$item->getFieldId()] = $this->getFilterValue($item);
        }
        return $f;
    }

    /**
     * Get the additional sql WHERE-part for filters.
     */
    protected function getFilterWhere(array $filter) : string
    {
        $buf = [''];

        if (strlen($filter['name']) > 0) {
            $name = substr($this->db->quote($filter['name'], "text"), 1, -1);
            $name_filter =
                'AND (' . PHP_EOL
                . 'pcp.firstname LIKE \'%' . $name . '%\' OR' . PHP_EOL
                . 'pcp.lastname LIKE \'%' . $name . '%\' OR' . PHP_EOL
                . 'pcp.login LIKE \'%' . $name . '%\'' . PHP_EOL
                . ')' . PHP_EOL
            ;

            $buf[] = $name_filter;
        }

        if ($filter['prg_status'] && (int) $filter['prg_status'] !== self::OPTION_ALL) {
            $buf[] = 'AND prgrs.status = ' . $this->db->quote($filter['prg_status'], "integer");
        }

        if ($filter['prg_validity'] && (int) $filter['prg_validity'] !== self::OPTION_ALL) {
            $operator = '<='; //self::VALIDITY_OPTION_RENEWAL_REQUIRED
            if ((int) $filter['prg_validity'] === self::VALIDITY_OPTION_VALID) {
                $operator = '>';
            }
            $buf[] = 'AND prgrs.vq_date ' . $operator . ' NOW()';
        }

        $exp_from = $filter['prg_expiry_date']['from'];
        if (!is_null($exp_from)) {
            $dat = $exp_from->get(IL_CAL_DATE);
            $buf[] = 'AND prgrs.vq_date >= \'' . $dat . ' 00:00:00\'';
        }

        $exp_to = $filter['prg_expiry_date']['to'];
        if (!is_null($exp_to)) {
            $dat = $exp_to->get(IL_CAL_DATE);
            $buf[] = 'AND prgrs.vq_date <= \'' . $dat . ' 23:59:59\'';
        }

        $conditions = implode(PHP_EOL, $buf);

        return $conditions;
    }


    protected function isPermissionControlledByOrguPosition()
    {
        return (
            $this->prg->getAccessControlByOrguPositionsGlobal()
            ||
            $this->prg->getPositionSettingsIsActiveForPrg()
        );
    }

    protected function getOrguValidUsersFilter() : string
    {
        if ($this->getParentObject()->mayManageMembers()) {
            return '';
        }

        $valid_user_ids = $this->position_based_access->getUsersInPrgAccessibleForOperation(
            $this->getParentObject()->object,
            ilOrgUnitOperation::OP_MANAGE_MEMBERS
        );
        if (count($valid_user_ids) < 1) {
            return ' AND false';
        }
        return ' AND pcp.usr_id in ('
            . implode(',', $valid_user_ids)
            . ')';
    }
}
