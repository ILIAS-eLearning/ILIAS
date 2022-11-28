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
 * ilStudyProgrammeUserTable provides a flattened list of progresses at a programme-node.
 */
class ilStudyProgrammeUserTable
{
    public const OPTION_ALL = -1;
    public const VALIDITY_OPTION_VALID = 1;
    public const VALIDITY_OPTION_INVALID = 3;
    public const OPTION_USR_ACTIVE = 1;
    public const OPTION_USR_INACTIVE = 2;

    public const PRG_COLS = [
        ['name', 'name', false, true, true],
        ['login', 'login', false, true, true],
        ['prg_orgus', 'prg_orgus', true, true, true],
        ['prg_status', 'prg_status', false, true, true],
        ['prg_completion_date', 'prg_completion_date', true, true, true],
        ['prg_completion_by', 'prg_completion_by', true, true, true],
        ['points', 'prg_points_reachable', false, true, false],
        ['points_required', 'prg_points_required', false, false, true],
        ['points_current', 'prg_points_current', false, false, true],
        ['prg_custom_plan', 'prg_custom_plan', true, true, true],
        ['prg_belongs_to', 'prg_belongs_to', true, true, true],
        ['prg_assign_date', 'prg_assign_date', false, true, true],
        ['prg_assigned_by', 'prg_assigned_by', true, true, true],
        ['prg_deadline', 'prg_deadline', true, true, true],
        ['prg_expiry_date', 'prg_expiry_date', true, true, true],
        ['prg_validity', 'prg_validity', true, true, true]
    ];

    protected int $prg_obj_id;
    protected ilDBInterface $db;
    protected ilExportFieldsInfo $export_fields_info;
    protected ilLanguage $lng;
    protected ilPRGPermissionsHelper $permissions;
    protected ilObjUser $user;
    protected ILIAS\UI\Factory $ui_factory;
    protected ILIAS\UI\Renderer $ui_renderer;
    protected array $user_ids_viewer_may_read_learning_progress_of;
    protected ilPRGAssignmentDBRepository $assignment_repo;

    public function __construct(
        ilDBInterface $db,
        ilExportFieldsInfo $export_fields_info,
        ilPRGAssignmentDBRepository $assignment_repo,
        ilLanguage $lng,
        ilPRGPermissionsHelper $permissions,
        ilObjUser $user,
        ILIAS\UI\Factory $ui_factory,
        ILIAS\UI\Renderer $ui_renderer
    ) {
        $this->db = $db;
        $this->export_fields_info = $export_fields_info;
        $this->assignment_repo = $assignment_repo;
        $this->lng = $lng;
        $this->permissions = $permissions;
        $this->user = $user;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->user_ids_viewer_may_read_learning_progress_of = $this->permissions->getUserIdsSusceptibleTo(
            ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS
        );

        $this->lng->loadLanguageModule("prg");
    }

    protected function getUserDataColumns(int $prg_id): array
    {
        $cols = [];
        $user_data_cols = $this->export_fields_info->getSelectableFieldsInfo($prg_id);
        foreach ($user_data_cols as $k => $column_definition) {
            $cols[$k] = [$k, $column_definition['txt'], true, true, true];
        }
        return $cols;
    }

    protected function getPrgColumns(): array
    {
        $cols = [];
        foreach (self::PRG_COLS as $k) {
            $k[1] = $this->lng->txt($k[1]);
            $cols[$k[0]] = $k;
        }
        return $cols;
    }

    public function getColumns(int $prg_id, bool $add_active_column = false): array
    {
        $columns = array_merge(
            $this->getPrgColumns(),
            $this->getUserDataColumns($prg_id)
        );

        if ($add_active_column) {
            $columns["active"] = ["active", $this->lng->txt("active"), true, true, true];
        }
        return $columns;
    }


    public function countFetchData(int $prg_id, ?array $valid_user_ids, ilPRGAssignmentFilter $custom_filters): int
    {
        return $this->assignment_repo->countAllForNodeIsContained($prg_id, $valid_user_ids, $custom_filters);
    }

    /**
     * @return ilStudyProgrammeUserTableRow[]
     * @throws ilException
     */
    public function fetchData(
        int $prg_id,
        ?array $valid_user_ids,
        ilPRGAssignmentFilter $custom_filters = null,
        int $limit = null,
        int $offset = null
    ): array {

        //TODO: limit, offset
        $data = $this->assignment_repo->getAllForNodeIsContained($prg_id, $valid_user_ids, $custom_filters);
        $row = array_map(fn ($r) => $this->toRow($r, $prg_id), $data);
        return $row;
    }

    protected $skip_perm_check_on_user = false;
    public function disablePermissionCheck($flag = false): void
    {
        $this->skip_perm_check_on_user = $flag;
    }

    protected function includeLearningProgress(int $usr_id): bool
    {
        return
            in_array($usr_id, $this->user_ids_viewer_may_read_learning_progress_of)
            || $this->skip_perm_check_on_user;
    }

    protected function toRow(ilPRGAssignment $ass, int $node_id): ilStudyProgrammeUserTableRow
    {
        $pgs = $ass->getProgressForNode($node_id);
        $row = new ilStudyProgrammeUserTableRow(
            $ass->getId(),
            $ass->getUserId(),
            $node_id,
            $ass->getRootId() === $node_id
        );

        $show_lp = $this->includeLearningProgress($ass->getUserId());

        $row = $row
            ->withUserActiveRaw($ass->getUserInformation()->isActive())
            ->withUserActive($this->activeToRepresent($ass->getUserInformation()->isActive()))
            ->withFirstname($ass->getUserInformation()->getFirstname())
            ->withLastname($ass->getUserInformation()->getLastname())
            ->withLogin($ass->getUserInformation()->getLogin())
            ->withOrgUs($ass->getUserInformation()->getOrguRepresentation())
            ->withUDF($ass->getUserInformation()->getAllUdf())
            ->withGender($this->lng->txt('gender_' . $ass->getUserInformation()->getUdf('gender')))
            ->withStatus($show_lp ? $this->statusToRepresent($pgs->getStatus()) : '')
            ->withStatusRaw($pgs->getStatus())
            ->withCompletionDate(
                $show_lp && $pgs->getCompletionDate() ? $pgs->getCompletionDate()->format($pgs::DATE_TIME_FORMAT) : ''
            )
            ->withCompletionBy(
                $show_lp && $pgs->getCompletionBy() ? $this::lookupTitle($pgs->getCompletionBy()) : ''
            )
            ->withPointsReachable((string) $pgs->getPossiblePointsOfRelevantChildren())
            ->withPointsRequired((string) $pgs->getAmountOfPoints())
            ->withPointsCurrent($show_lp ? (string) $pgs->getCurrentAmountOfPoints() : '')
            ->withCustomPlan($this->boolToRepresent($pgs->hasIndividualModifications()))
            ->withBelongsTo($this::lookupTitle($ass->getRootId()))
            ->withAssignmentDate($pgs->getAssignmentDate()->format($pgs::DATE_TIME_FORMAT))
            ->withAssignmentBy(
                $this->assignmentSourceToRepresent(
                    $ass->isManuallyAssigned(),
                    $ass->getLastChangeBy()
                )
            )
            ->withDeadline(
                $show_lp && $pgs->getDeadline() ? $pgs->getDeadline()->format($pgs::DATE_FORMAT) : ''
            )
            ->withExpiryDate(
                $show_lp && $pgs->getValidityOfQualification() ? $pgs->getValidityOfQualification()->format($pgs::DATE_FORMAT) : ''
            )
            ->withValidity($show_lp ? $this->validToRepresent(!$pgs->isInvalidated()) : '')
        ;
        return $row;
    }

    /*  TODO: re-implement getCompletionLink
       protected function getCompletionLink(int $target_obj_id, int $target_ref_id) : string
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
    */
    protected function getUserDateFormat(): string
    {
        return ilCalendarUtil::getUserDateFormat(false, true);
    }

    /**
     * @throws ilException
     */
    public function statusToRepresent($a_status): string
    {
        if ($a_status == ilPRGProgress::STATUS_IN_PROGRESS) {
            return $this->lng->txt("prg_status_in_progress");
        }
        if ($a_status == ilPRGProgress::STATUS_COMPLETED) {
            return $this->lng->txt("prg_status_completed");
        }
        if ($a_status == ilPRGProgress::STATUS_ACCREDITED) {
            return $this->lng->txt("prg_status_accredited");
        }
        if ($a_status == ilPRGProgress::STATUS_NOT_RELEVANT) {
            return $this->lng->txt("prg_status_not_relevant");
        }
        if ($a_status == ilPRGProgress::STATUS_FAILED) {
            return $this->lng->txt("prg_status_failed");
        }
        throw new ilException("Unknown status: '$a_status'");
    }

    public function boolToRepresent(bool $value): string
    {
        return ($value) ? $this->lng->txt("yes") : $this->lng->txt("no");
    }
    public function validToRepresent(?bool $value): string
    {
        if (is_null($value)) {
            return '-';
        }
        return ($value) ? $this->lng->txt("prg_still_valid") : $this->lng->txt("prg_not_valid");
    }

    public function activeToRepresent(bool $value): string
    {
        return $value ? $this->lng->txt('active') : $this->lng->txt('inactive');
    }

    public function assignmentSourceToRepresent(bool $manually, int $assignment_src): string
    {
        if ($manually) {
            return $this::lookupTitle($assignment_src);
        }
        $srcs = array_flip(ilStudyProgrammeAutoMembershipSource::SOURCE_MAPPING);
        return implode(' ', [
            $this->lng->txt('prg_autoassignment'),
            $this->lng->txt($srcs[$assignment_src])
        ]);
    }

    public static function lookupTitle(int $obj_id): string
    {
        $type = ilObject::_lookupType($obj_id);
        switch ($type) {
            case 'usr':
            case 'prg':
                return ilObject::_lookupTitle($obj_id);
            case 'crsr':
                return ilContainerReference::_lookupTitle($obj_id);

        }
        throw new Exception("Error Processing Request:" . $obj_id, 1);
    }
}
