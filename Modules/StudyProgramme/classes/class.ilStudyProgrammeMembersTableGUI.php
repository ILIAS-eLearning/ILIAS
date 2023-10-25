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

declare(strict_types=1);

use ILIAS\Data;

class ilStudyProgrammeMembersTableGUI extends ilTable2GUI
{
    protected Data\Factory $data_factory;
    protected int $prg_obj_id;
    protected bool $prg_has_lp_children;
    protected ilObjStudyProgramme $prg;
    protected ilPRGPermissionsHelper $permissions;
    protected bool $may_edit_anything;
    protected ilStudyProgrammeUserTable $prg_user_table;
    protected ilObjUser $user;
    protected ilPRGAssignmentFilter $custom_filter;
    protected ILIAS\UI\Factory $ui_factory;
    protected ILIAS\UI\Renderer $ui_renderer;

    public function __construct(
        int $prg_obj_id,
        int $prg_ref_id,
        ilObjStudyProgrammeMembersGUI $parent_obj,
        ilPRGPermissionsHelper $permissions,
        Data\Factory $data_factory,
        ILIAS\UI\Factory $ui_factory,
        ILIAS\UI\Renderer $ui_renderer,
        ilStudyProgrammeUserTable $prg_user_table,
        ilPRGAssignmentFilter $custom_filter,
        ilObjUser $user,
        string $parent_cmd = '',
        string $template_context = ''
    ) {
        $this->setId("sp_member_list");
        $this->prg_obj_id = $prg_obj_id;
        $this->prg_user_table = $prg_user_table;
        $this->custom_filter = $custom_filter;
        parent::__construct($parent_obj, $parent_cmd, $template_context);

        $this->data_factory = $data_factory;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->permissions = $permissions;
        $this->may_edit_anything = $this->permissions->mayAnyOf([ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN, ilOrgUnitOperation::OP_MANAGE_MEMBERS]);
        $this->user = $user;

        $this->prg = ilObjStudyProgramme::getInstanceByRefId($prg_ref_id);
        $this->prg_has_lp_children = $parent_obj->getStudyProgramme()->hasLPChildren();

        $this->setEnableTitle(true);
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setRowTemplate("tpl.members_table_row.html", "Modules/StudyProgramme");
        $this->setShowRowsSelector(true);
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
        foreach ($this->prg_user_table->getColumns($prg_obj_id) as $column) {
            [$col, $lng_var, $optional, $lp, $no_lp] = $column;

            $show_by_lp = ($this->prg_has_lp_children && $lp) || (!$this->prg_has_lp_children && $no_lp);
            $show_optional = !$optional || ($optional && array_key_exists($col, $selected));

            if ($show_by_lp && $show_optional) {
                $this->addColumn($lng_var, $col);
            }
        }

        $this->addColumn($this->lng->txt('action'), '');

        $this->initFilter();
        $this->determineOffsetAndOrder();
        $this->determineLimit();

        $valid_user_ids = $this->getValidUserIds();
        $filter_values = $this->getFilterValues();

        $order = $this->getOrdering();

        $members_list = $this->prg_user_table->fetchData(
            $prg_obj_id,
            $valid_user_ids,
            $order,
            $this->custom_filter->withValues($filter_values),
            $this->getLimit() ? (int) $this->getLimit() : null,
            $this->getOffset()
        );

        $count = $this->prg_user_table->countFetchData(
            $prg_obj_id,
            $valid_user_ids,
            $this->custom_filter->withValues($filter_values)
        );
        $this->setMaxCount($count);


        $progress_ids = array_map(
            function ($row) {
                return (string) $row->getId();
            },
            $members_list
        );
        $this->addHiddenInput(
            $parent_obj::F_ALL_PROGRESS_IDS,
            implode(',', $progress_ids)
        );

        $this->setData($members_list);
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


    protected function fillRow($row): void
    {
        if (!$row instanceof ilStudyProgrammeUserTableRow) {
            throw new \Exception("use ilStudyProgrammeUserTableRow for data output", 1);
        }

        if ($this->may_edit_anything) {
            $this->tpl->setCurrentBlock("checkb");
            $this->tpl->setVariable("ID", (string) $row->getId());
            $this->tpl->parseCurrentBlock();
        }

        if (!$row->isUserActiveRaw()) {
            $this->tpl->setCurrentBlock('access_warning');
            $this->tpl->setVariable('PARENT_ACCESS', $this->lng->txt('usr_account_inactive'));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("FIRSTNAME", $row->getFirstname());
        $this->tpl->setVariable("LASTNAME", $row->getLastname());
        $this->tpl->setVariable("LOGIN", $row->getLogin());
        $this->tpl->setVariable("STATUS", $row->getStatus());
        $this->tpl->setVariable("ASSIGN_DATE", $row->getAssignmentDate());
        $this->tpl->setVariable("POINTS_REQUIRED", $row->getPointsRequired());

        if (!$this->prg_has_lp_children) {
            $this->tpl->setCurrentBlock("points_current");
            $this->tpl->setVariable("POINTS_CURRENT", $row->getPointsCurrent());
            $this->tpl->parseCurrentBlock();
        }

        foreach ($this->getSelectedColumns() as $column) {
            switch ($column) {
                case "prg_orgus":
                    $this->tpl->setVariable("ORGUS", $row->getOrgUs());
                    break;
                case "prg_completion_date":
                    $this->tpl->setVariable("COMPLETION_DATE", $row->getCompletionDate());
                    break;
                case "prg_completion_by":

                    $completion_by = $row->getCompletionBy();
                    if ($completion_by_obj_id = $row->getCompletionByObjId()) {
                        if (ilObject::_lookupType($completion_by_obj_id) === 'crsr') {
                            $completion_by = $this->getCompletionLink($completion_by_obj_id, $completion_by);
                        }
                    }
                    $this->tpl->setVariable("COMPLETION_BY", $completion_by);
                    break;
                case "prg_custom_plan":
                    $this->tpl->setVariable("CUSTOM_PLAN", $row->getCustomPlan());
                    break;
                case "prg_belongs_to":
                    $this->tpl->setVariable("BELONGS_TO", $row->getBelongsTo());
                    break;
                case "prg_expiry_date":
                    $this->tpl->setVariable("EXPIRY_DATE", $row->getExpiryDate());
                    break;
                case "prg_assigned_by":
                    $this->tpl->setVariable("ASSIGNED_BY", $row->getAssignmentBy());
                    break;
                case "prg_deadline":
                    $this->tpl->setVariable("DEADLINE", $row->getDeadline());
                    break;
                case "prg_validity":
                    $this->tpl->setVariable("VALIDITY", $row->getValidity());
                    break;
                case 'org_units':
                    $this->tpl->setCurrentBlock('udf');
                    $this->tpl->setVariable("UDF", $row->getOrgUs());
                    $this->tpl->parseCurrentBlock();
                    break;
                case 'gender':
                    $this->tpl->setCurrentBlock('udf');
                    $this->tpl->setVariable("UDF", $row->getGender());
                    $this->tpl->parseCurrentBlock();
                    break;
                case strpos($column, 'udf_') === 0:
                    $id = str_replace('udf_', 'f_', $column);
                    $this->tpl->setCurrentBlock('udf');
                    $this->tpl->setVariable("UDF", $row->getUDF($id));
                    $this->tpl->parseCurrentBlock();
                    break;
                default:
                    $this->tpl->setCurrentBlock('udf');
                    $this->tpl->setVariable("UDF", $row->getUDF($column));
                    $this->tpl->parseCurrentBlock();
            }
        }
        $actions = $this->getPossibleActions(
            $row->isRootProgress(),
            $row->getStatusRaw()
        );

        $this->tpl->setVariable(
            "ACTIONS",
            $this->buildActionDropDown(
                $actions,
                (string) $row->getId(),
                $row->getAssignmentId()
            )
        );
    }

    protected function buildActionDropDown(
        array $actions,
        string $prgrs_id,
        int $ass_id
    ): string {
        $l = [];

        $view_individual_plan = $this->permissions->may(ilOrgUnitOperation::OP_VIEW_INDIVIDUAL_PLAN);
        $edit_individual_plan = $this->permissions->may(ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN);
        $addremove_users = $this->permissions->may(ilOrgUnitOperation::OP_MANAGE_MEMBERS);

        foreach ($actions as $action) {
            switch ($action) {
                case ilObjStudyProgrammeMembersGUI::ACTION_MARK_ACCREDITED:
                case ilObjStudyProgrammeMembersGUI::ACTION_UNMARK_ACCREDITED:
                case ilObjStudyProgrammeMembersGUI::ACTION_UNMARK_RELEVANT:
                case ilObjStudyProgrammeMembersGUI::ACTION_MARK_RELEVANT:
                case ilObjStudyProgrammeMembersGUI::ACTION_UPDATE_FROM_CURRENT_PLAN:
                case ilObjStudyProgrammeMembersGUI::ACTION_ACKNOWLEDGE_COURSES:
                case ilObjStudyProgrammeMembersGUI::ACTION_CHANGE_DEADLINE:
                case ilObjStudyProgrammeMembersGUI::ACTION_CHANGE_EXPIRE_DATE:
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
            $l[] = $this->ui_factory->button()->shy($this->lng->txt("prg_$action"), $target);
        }
        return $this->ui_renderer->render(
            $this->ui_factory->dropdown()->standard($l)->withLabel($this->lng->txt('actions'))
        );
    }


    protected function getLinkTargetForAction(string $action, string $prgrs_id, int $ass_id): string
    {
        return $this->getParentObject()->getLinkTargetForAction($action, $prgrs_id, $ass_id);
    }

    public function getSelectableColumns(): array
    {
        $cols = [];
        foreach ($this->prg_user_table->getColumns($this->prg_obj_id) as $column) {
            [$col, $lng_var, $optional, $lp, $no_lp] = $column;
            if ($optional) {
                $cols[$col] = ["txt" => $lng_var];
            }
        }

        return $cols;
    }

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
            'acknowledgeCoursesMulti' => $this->lng->txt('prg_acknowledge_completed_courses'),
            'markRelevantMulti' => $this->lng->txt('prg_multi_mark_relevant'),
            'markNotRelevantMulti' => $this->lng->txt('prg_multi_unmark_relevant'),
            'changeDeadlineMulti' => $this->lng->txt('prg_multi_change_deadline'),
            'changeExpireDateMulti' => $this->lng->txt('prg_multi_change_expire_date'),
            'markAccreditedMulti' => $this->lng->txt('prg_multi_mark_accredited'),
            'unmarkAccreditedMulti' => $this->lng->txt('prg_multi_unmark_accredited'),
            'updateCertificateMulti' => $this->lng->txt('prg_multi_update_certificate')
        ];

        $permissions_for_manage = [
            'removeUserMulti' => $this->lng->txt('prg_multi_remove_user'),
            'mailUserMulti' => $this->lng->txt('prg_multi_mail_user')
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
     * @inheritdoc
     */
    public function initFilter(): void
    {
        foreach ($this->custom_filter->getItemConfig() as $conf) {
            [$id, $type, $options, $caption] = $conf;
            $item = $this->addFilterItemByMetaType($id, $type, false, $caption);
            if ($options) {
                $item->setOptions($options);
            }
        }
    }

    /**
     * Get filter-values by field id.
     */
    protected function getFilterValues(): array
    {
        $this->getCurrentState();
        $f = [];
        foreach ($this->filters as $item) {
            $f[$item->getFieldId()] = $this->getFilterValue($item);
        }
        return $f;
    }

    /**
     * Get a list with possible actions on a progress record.
     *
     * @return string[]
     */
    protected function getPossibleActions(
        bool $is_root,
        int $status
    ): array {
        $actions = [];

        if ($is_root) {
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_SHOW_INDIVIDUAL_PLAN;
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_REMOVE_USER;
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_UNMARK_RELEVANT;
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_MARK_RELEVANT;
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_UPDATE_FROM_CURRENT_PLAN;
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_ACKNOWLEDGE_COURSES;
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_CHANGE_DEADLINE;
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_CHANGE_EXPIRE_DATE;
        }

        if ($status == ilPRGProgress::STATUS_ACCREDITED) {
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_UNMARK_ACCREDITED;
        }
        if ($status == ilPRGProgress::STATUS_IN_PROGRESS) {
            $actions[] = ilObjStudyProgrammeMembersGUI::ACTION_MARK_ACCREDITED;
        }

        return $actions;
    }

    protected function getValidUserIds(): ?array
    {
        if ($this->permissions->may($this->permissions::ROLEPERM_MANAGE_MEMBERS)) {
            return null;
        }

        $valid_user_ids = $this->permissions->getUserIdsSusceptibleTo(ilOrgUnitOperation::OP_VIEW_MEMBERS);
        array_unshift($valid_user_ids, $this->user->getId());
        return $valid_user_ids;
    }

    protected function getCompletionLink(int $reference_obj_id, string $title): string
    {
        $link = $title;
        $target_obj_id = ilContainerReference::_lookupTargetId($reference_obj_id);
        $ref_ids = ilObject::_getAllReferences($target_obj_id);
        foreach ($ref_ids as $ref_id) {
            if (!ilObject::_isInTrash($ref_id)) {
                $url = ilLink::_getStaticLink($ref_id, "crs");
                $link = $this->ui_renderer->render($this->ui_factory->link()->standard($title, $url));
            }
        }
        return $link;
    }
}
