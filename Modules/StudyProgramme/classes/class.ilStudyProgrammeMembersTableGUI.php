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

use ILIAS\Data;

class ilStudyProgrammeMembersTableGUI extends ilTable2GUI
{
    private const COLUMNS = [
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

    private const OPTION_ALL = -1;
    private const VALIDITY_OPTION_VALID = 1;
    private const VALIDITY_OPTION_INVALID = 3;

    protected int $prg_obj_id;
    protected int $prg_ref_id;
    protected Data\Factory $data_factory;
    protected bool $prg_has_lp_children;
    protected ilDBInterface $db;
    protected ILIAS\UI\Factory $ui_factory;
    protected ILIAS\UI\Renderer $ui_renderer;
    protected ilStudyProgrammeProgressRepository $sp_user_progress_db;
    protected ilObjStudyProgramme $prg;
    protected ilPRGPermissionsHelper $permissions;
    protected bool $may_edit_anything;
    protected array $user_ids_viewer_may_read_learning_progress_of;

    public function __construct(
        int $prg_obj_id,
        int $prg_ref_id,
        ilObjStudyProgrammeMembersGUI $parent_obj,
        ilStudyProgrammeProgressRepository $sp_user_progress_db,
        ilPRGPermissionsHelper $permissions,
        Data\Factory $data_factory,
        string $parent_cmd = '',
        string $template_context = ''
    ) {
        $this->setId("sp_member_list");
        parent::__construct($parent_obj, $parent_cmd, $template_context);

        $this->prg_obj_id = $prg_obj_id;
        $this->prg_ref_id = $prg_ref_id;
        $this->data_factory = $data_factory;
        $this->permissions = $permissions;
        $this->may_edit_anything = $this->permissions->mayAnyOf([ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN, ilOrgUnitOperation::OP_MANAGE_MEMBERS]);
        $this->user_ids_viewer_may_read_learning_progress_of = $this->permissions->getUserIdsSusceptibleTo(ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS);

        $this->prg = ilObjStudyProgramme::getInstanceByRefId($prg_ref_id);
        $this->prg_has_lp_children = $parent_obj->getStudyProgramme()->hasLPChildren();

        global $DIC;
        $this->db = $DIC['ilDB'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];

        $this->setEnableTitle(true);
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setRowTemplate("tpl.members_table_row.html", "Modules/StudyProgramme");
        $this->setShowRowsSelector(false);
        $this->setFormAction($this->ctrl->getFormAction($parent_obj, "view"));
        $this->addColumn("", "", "1", true);
        $this->setEnableAllCommand(true);
        $this->addMultiCommands();
        $this->setDefaultOrderField('prgrs_id');
        $this->setDefaultOrderDirection('ASC');

        if ($this->may_edit_anything) {
            $this->setSelectAllCheckbox($parent_obj::F_SELECTED_PROGRESS_IDS . '[]');
        }

        $selected = $this->getSelectedColumns();
        foreach (self::COLUMNS as $column) {
            [$col, $lng_var, $optional, $lp, $no_lp] = $column;

            $show_by_lp = ($this->prg_has_lp_children && $lp) || (!$this->prg_has_lp_children && $no_lp);
            $show_optional = !$optional || ($optional && array_key_exists($col, $selected));

            if ($show_by_lp && $show_optional) {
                $this->addColumn($this->lng->txt($lng_var), $col ?? "");
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
            $filter_values
        );

        $progress_ids = array_map(
            static function (array $row): int {
                return (int) $row['prgrs_id'];
            },
            $members_list
        );
        $this->addHiddenInput(
            $parent_obj::F_ALL_PROGRESS_IDS,
            implode(',', $progress_ids)
        );

        $this->setMaxCount($this->countFetchData($prg_obj_id, $filter_values));
        $this->setData(
            $this->postOrder(
                $members_list,
                $this->getOrdering()
            )
        );
    }


    private const ORDER_MAPPING = [
        'prg_status' => 'status',
        'prg_custom_plan' => 'custom_plan',
        'prg_belongs_to' => 'belongs_to',
        'prg_expiry_date' => 'vq_date',
        'prg_orgus' => 'orgus',
        'prg_completion_by' => 'completion_by',
        'prg_completion_date' => 'completion_date',
    ];

    protected function postOrder(array $list, Data\Order $order): array
    {
        [$aspect, $direction] = $order->join('', function ($i, $k, $v) {
            return [$k, $v];
        });

        if (array_key_exists($aspect, self::ORDER_MAPPING)) {
            $aspect = self::ORDER_MAPPING[$aspect];
        }

        usort($list, static function (array $a, array $b) use ($aspect): int {
            if (is_numeric($a[$aspect])) {
                return $a[$aspect] <=> $b[$aspect];
            }
            return strcmp($a[$aspect], $b[$aspect]);
        });

        if ($direction === $order::DESC) {
            $list = array_reverse($list);
        }
        return $list;
    }

    protected function getOrdering(): Data\Order
    {
        $field = $this->getOrderField();
        if (!$field) {
            $field = $this->getDefaultOrderField();
        }
        $direction = $this->getOrderDirection();
        if (!$direction) {
            $direction = $this->getDefaultOrderDirection();
        }

        return $this->data_factory->order($field, strtoupper($direction));
    }

    protected function getUserDateFormat(): string
    {
        return ilCalendarUtil::getUserDateFormat(0, true);
    }

    protected function fillRow(array $a_set): void
    {
        $usr_id = (int) $a_set['usr_id'];
        if ($this->may_edit_anything) {
            $this->tpl->setCurrentBlock("checkb");
            $this->tpl->setVariable("ID", $a_set["prgrs_id"]);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
        $this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
        $this->tpl->setVariable("LOGIN", $a_set["login"]);
        $this->tpl->setVariable(
            "STATUS",
            $this->getValueOrEmptyString(
                in_array($usr_id, $this->user_ids_viewer_may_read_learning_progress_of),
                $this->prg->statusToRepr((int) $a_set["status"])
            )
        );
        $this->tpl->setVariable("ASSIGN_DATE", $a_set["prg_assign_date"]);


        $this->tpl->setVariable("POINTS_REQUIRED", $a_set["points_required"]);

        if (!$this->prg_has_lp_children) {
            $this->tpl->setCurrentBlock("points_current");
            $this->tpl->setVariable(
                "POINTS_CURRENT",
                $this->getValueOrEmptyString(
                    in_array($usr_id, $this->user_ids_viewer_may_read_learning_progress_of),
                    $a_set['points_current']
                )
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
                    if (in_array($usr_id, $this->user_ids_viewer_may_read_learning_progress_of)) {
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
                                in_array($usr_id, $this->user_ids_viewer_may_read_learning_progress_of),
                                $a_set["completion_by"]
                            )
                        );
                    }

                    break;
                case "prg_custom_plan":
                    $individual = $this->lng->txt("no");
                    if ($a_set["individual"]) {
                        $individual = $this->lng->txt("yes");
                    }

                    $this->tpl->setVariable(
                        "CUSTOM_PLAN",
                        $individual
                    );
                    break;

                case "prg_belongs_to":
                    $this->tpl->setVariable("BELONGS_TO", $a_set["belongs_to"]);
                    break;
                case "prg_expiry_date":
                    $this->tpl->setVariable(
                        "EXPIRY_DATE",
                        $this->getValueOrEmptyString(
                            in_array($usr_id, $this->user_ids_viewer_may_read_learning_progress_of),
                            $a_set["vq_date"]
                        )
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
                                in_array($usr_id, $this->user_ids_viewer_may_read_learning_progress_of),
                                $a_set["prg_deadline"]
                            )
                        );
                    }
                    break;
                case "prg_validity":
                    $this->tpl->setVariable(
                        "VALIDITY",
                        $this->getValueOrEmptyString(
                            in_array($usr_id, $this->user_ids_viewer_may_read_learning_progress_of),
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
                (int) $a_set["prgrs_id"],
                (int) $a_set["assignment_id"]
            )
        );
    }

    protected function getValueOrEmptyString(bool $condition, string $value): string
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
        int $ass_id
    ): string {
        $l = new ilAdvancedSelectionListGUI();

        $view_individual_plan = $this->permissions->may(ilOrgUnitOperation::OP_VIEW_INDIVIDUAL_PLAN);
        $edit_individual_plan = $this->permissions->may(ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN);
        $addremove_users = $this->permissions->may(ilOrgUnitOperation::OP_MANAGE_MEMBERS);

        foreach ($actions as $action) {
            switch ($action) {
                case ilObjStudyProgrammeMembersGUI::ACTION_MARK_ACCREDITED:
                case ilObjStudyProgrammeMembersGUI::ACTION_UNMARK_ACCREDITED:
                    if (!$edit_individual_plan) {
                        continue 2;
                    }
                    break;
                case ilObjStudyProgrammeMembersGUI::ACTION_SHOW_INDIVIDUAL_PLAN:
                    if (!$view_individual_plan) {
                        continue 2;
                    }
                    break;

                case ilObjStudyProgrammeMembersGUI::ACTION_REMOVE_USER:
                    if (!$addremove_users) {
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
    protected function getLinkTargetForAction(string $action, int $prgrs_id, int $ass_id): string
    {
        return $this->getParentObject()->getLinkTargetForAction($action, $prgrs_id, $ass_id);
    }

    protected function getCompletionLink(int $target_obj_id, int $target_ref_id): string
    {
        $link = '?';
        if (ilObject::_exists($target_ref_id, true) &&
            is_null(ilObject::_lookupDeletedDate($target_ref_id))
        ) {
            $title = ilObject::_lookupTitle($target_obj_id);
            $url = ilLink::_getStaticLink($target_ref_id, "crs");
            $link = $this->ui_renderer->render($this->ui_factory->link()->standard($title, $url));
        } else {
            $del_data = ilObjectDataDeletionLog::get($target_obj_id);
            if ($del_data) {
                $link = $del_data['title'];
            }
        }
        return $link;
    }

    /**
     * Get data for table
     */
    protected function fetchData(
        int $prg_id,
        int $limit = null,
        int $offset = null,
        array $filter = []
    ): array {
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
            . "prgrs.individual," . PHP_EOL
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
            . "prgrs.individual AS custom_plan" . PHP_EOL
        ;

        $sql .= $this->getFrom();
        $sql .= $this->getWhere($prg_id);
//        $sql .= $this->getFilterWhere($filter);
        $sql .= $this->getOrguValidUsersFilter();

        if ($limit !== null) {
            $this->db->setLimit($limit, $offset ?? 0);
        }

        $res = $this->db->query($sql);
        $now = new DateTimeImmutable();
        $members_list = array();

        while ($rec = $this->db->fetchAssoc($res)) {
            $progress_id = (int) $rec['prgrs_id'];
            $progress = $this->sp_user_progress_db->get($progress_id);

            $rec["actions"] = $this->getPossibleActions(
                $prg_id,
                (int) $rec["root_prg_id"],
                (int) $rec["status"]
            );

            $prg = ilObjStudyProgramme::getInstanceByObjId($progress->getNodeId());

            $rec['points_required'] = number_format($progress->getAmountOfPoints());
            $rec['points_current'] = number_format($progress->getCurrentAmountOfPoints());

            if ((int) $rec["status"] === ilStudyProgrammeProgress::STATUS_COMPLETED) {
                //If the status completed is set by crs reference
                //use crs title
                if ($rec["completion_by_type"] === "crsr") {
                    $completion_id = (int)$rec["completion_by_id"];
                    $obj_id = ilContainerReference::_lookupTargetId($completion_id);
                    $ref_id = ilContainerReference::_lookupTargetRefId($completion_id);
                    $rec["completion_by"] = $this->getCompletionLink($obj_id, $ref_id);
                }

                // If the status completed and there is a non-null completion_by field
                // in the set, this means the completion was achieved by some leaf in
                // the program tree.
                if (!$rec["completion_by"]) {
                    $prgrs = $this->sp_user_progress_db->getByPrgIdAndAssignmentId(
                        $this->prg_obj_id,
                        (int) $rec["assignment_id"]
                    );
                    $prg = ilObjStudyProgramme::getInstanceByObjId($this->prg_obj_id);

                    $links = [];
                    $successful_children = $prg->getIdsOfSuccessfulChildren((int) $rec["assignment_id"]);
                    foreach ($successful_children as $entry) {
                        [$obj_id, $ref_id] = $entry;
                        $links[] = $this->getCompletionLink($obj_id, $ref_id);
                    }
                    $rec["completion_by"] = implode(", ", $links);
                }
                // This case should only occur if the status completed is set
                // by an already deleted crs.
                if (!$rec["completion_by"]) {
                    $title = ilObjectDataDeletionLog::get((int) $rec["completion_by_id"]);
                    if (!is_null($title["title"])) {
                        $rec["completion_by"] = $title["title"];
                    }
                }
            } elseif ((int) $rec["status"] === ilStudyProgrammeProgress::STATUS_ACCREDITED) {
                $rec["completion_by"] = $rec["accredited_by"];
            }

            if (!$rec['completion_date']) {
                $rec['completion_date'] = '';
            }

            $rec['vq_date'] = '';
            if (!is_null($progress->getValidityOfQualification())
            ) {
                $rec['vq_date'] = $progress->getValidityOfQualification()->format($this->getUserDateFormat());
            }

            $rec['prg_validity'] = '-';
            if (!is_null($progress->hasValidQualification($now))) {
                $rec['prg_validity'] = $this->lng->txt('prg_not_valid');
                if ($progress->hasValidQualification($now)) {
                    $rec['prg_validity'] = $this->lng->txt('prg_still_valid');
                }
            }

            $rec['prg_deadline'] = null;
            if ($progress->isSuccessful() === false
                && !is_null($progress->getDeadline())
            ) {
                $rec['prg_deadline'] = $progress->getDeadline()->format($this->getUserDateFormat());
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
    protected function countFetchData(int $prg_id, array $filter = []): int
    {
        // TODO: Reimplement this in terms of ActiveRecord when innerjoin
        // supports the required rename functionality
        $query = "SELECT count(prgrs.id) as cnt" . PHP_EOL;
        $query .= $this->getFrom();
        $query .= $this->getWhere($prg_id);
//        $query .= $this->getFilterWhere($filter);

        $res = $this->db->query($query);
        $rec = $this->db->fetchAssoc($res);

        return (int) $rec["cnt"];
    }

    protected function getFrom(): string
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
    protected function getWhere(int $prg_id): string
    {
        $q = "WHERE prgrs.prg_id = " . $this->db->quote($prg_id, "integer") . PHP_EOL;

        //get all potentially visible users:
        $visible = [];
        foreach ($this->permissions::ORGU_OPERATIONS as $op) {
            $visible = array_merge(
                $visible,
                $this->permissions->getUserIdsSusceptibleTo($op)
            );
        }

        if (count($visible) > 0) {
            $q .= "	AND " . $this->db->in("prgrs.usr_id", $visible, false, "integer") . PHP_EOL;
        } else {
            $q .= " AND FALSE" . PHP_EOL;
        }
        return $q;
    }

    /**
     * Get selectable columns
     */
    public function getSelectableColumns(): array
    {
        $cols = [];
        foreach (self::COLUMNS as $column) {
            [$col, $lng_var, $optional, ,] = $column;
            if ($optional) {
                $cols[$col] = ["txt" => $this->lng->txt($lng_var)];
            }
        }

        return $cols;
    }

    /**
     * Add multicommands to table
     */
    protected function addMultiCommands(): void
    {
        foreach ($this->getMultiCommands() as $cmd => $caption) {
            $this->addMultiCommand($cmd, $caption);
        }
    }

    /**
     * Get possible multicommands
     *
     * @return string[]
     */
    protected function getMultiCommands(): array
    {
        $permissions_for_edit_individual_plan = [
            'updateFromCurrentPlanMulti' => $this->lng->txt('prg_multi_update_from_current_plan'),
            'markRelevantMulti' => $this->lng->txt('prg_multi_mark_relevant'),
            'markNotRelevantMulti' => $this->lng->txt('prg_multi_unmark_relevant'),
            'changeDeadlineMulti' => $this->lng->txt('prg_multi_change_deadline'),
            'changeExpireDateMulti' => $this->lng->txt('prg_multi_change_expire_date'),
            'markAccreditedMulti' => $this->lng->txt('prg_multi_mark_accredited'),
            'unmarkAccreditedMulti' => $this->lng->txt('prg_multi_unmark_accredited')
        ];

        $permissions_for_manage = [
            'removeUserMulti' => $this->lng->txt('prg_multi_remove_user')
        ];

        $perms = [];

        if ($this->permissions->may(ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN)) {
            $perms = array_merge($perms, $permissions_for_edit_individual_plan);
        }

        if ($this->permissions->may(ilOrgUnitOperation::OP_MANAGE_MEMBERS)) {
            $perms = array_merge($perms, $permissions_for_manage);
        }

        return $perms;
    }

    /**
      * Get a list with possible actions on a progress record.
      *
      * @return string[]
      */
    protected function getPossibleActions(
        int $node_id,
        int $root_prg_id,
        int $status
    ): array {
        $actions = array();

        if ($node_id === $root_prg_id) {
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_SHOW_INDIVIDUAL_PLAN;
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_REMOVE_USER;
        }

        if ($status === ilStudyProgrammeProgress::STATUS_ACCREDITED) {
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_UNMARK_ACCREDITED;
        }
        if ($status === ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_MARK_ACCREDITED;
        }

        return $actions;
    }

    /**
     * Get options of filter "validity".
     */
    protected function getValidityOptions(): array
    {
        return [
            self::VALIDITY_OPTION_VALID => $this->lng->txt("prg_still_valid"),
            self::VALIDITY_OPTION_INVALID => $this->lng->txt("prg_not_valid")
        ];
    }

    /**
     * Get options of filter "status".
     */
    protected function getStatusOptions(): array
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
    public function initFilter(): void
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
    protected function getFilterValues(): array
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
    protected function getFilterWhere(array $filter): string
    {
        $buf = [''];

        if (isset($filter['name']) && is_string($filter['name']) && $filter['name'] !== '') {
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

        $filter_success = 'prgrs.status IN ('
            . ilStudyProgrammeProgress::STATUS_COMPLETED
            . ','
            . ilStudyProgrammeProgress::STATUS_ACCREDITED
        . ') ';

        if ($filter['prg_validity'] && (int) $filter['prg_validity'] !== self::OPTION_ALL) {
            $filter_validity = "";
            if ((int) $filter['prg_validity'] === self::VALIDITY_OPTION_VALID) {
                $filter_validity = 'AND (prgrs.vq_date >= NOW() OR prgrs.vq_date IS NULL)';
            }
            if ((int) $filter['prg_validity'] === self::VALIDITY_OPTION_INVALID) {
                $filter_validity = 'AND prgrs.vq_date < NOW()';
            }

            $buf[] = 'AND ('
                . $filter_success
                . $filter_validity
            . ')';
        }

        $exp_from = $filter['prg_expiry_date']['from'];
        if (!is_null($exp_from)) {
            $dat = $exp_from->get(IL_CAL_DATE);
            $buf[] = 'AND ('
                . $filter_success
                . 'AND prgrs.vq_date >= \'' . $dat . ' 00:00:00\''
            . ')';
        }

        $exp_to = $filter['prg_expiry_date']['to'];
        if (!is_null($exp_to)) {
            $dat = $exp_to->get(IL_CAL_DATE);
            $buf[] = 'AND ('
                . $filter_success
                . 'AND prgrs.vq_date <= \'' . $dat . ' 23:59:59\''
            . ')';
        }

        return implode(PHP_EOL, $buf);
    }

    protected function getOrguValidUsersFilter(): string
    {
        if ($this->permissions->may($this->permissions::ROLEPERM_MANAGE_MEMBERS)) {
            return '';
        }
        $valid_user_ids = $this->permissions->getUserIdsSusceptibleTo(ilOrgUnitOperation::OP_VIEW_MEMBERS);

        if (count($valid_user_ids) < 1) {
            return ' AND false';
        }

        $valid_user_ids[] = $this->getParentObject()->user->getId();
        return ' AND pcp.usr_id in ('
            . implode(',', $valid_user_ids)
            . ')';
    }
}
