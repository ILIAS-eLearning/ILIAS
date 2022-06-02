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
 ********************************************************************
 */

/**
 * Class ilOrgUnitOperation
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitOperation extends ActiveRecord
{
    const OP_READ_LEARNING_PROGRESS = 'read_learning_progress';
    const OP_WRITE_LEARNING_PROGRESS = 'write_learning_progress';
    const OP_EDIT_SUBMISSION_GRADES = 'edit_submissions_grades';
    const OP_ACCESS_RESULTS = 'access_results';
    const OP_MANAGE_MEMBERS = 'manage_members';
    const OP_ACCESS_ENROLMENTS = 'access_enrolments';
    const OP_MANAGE_PARTICIPANTS = 'manage_participants';
    const OP_SCORE_PARTICIPANTS = 'score_participants';
    const OP_VIEW_CERTIFICATES = 'view_certificates';
    const OP_VIEW_COMPETENCES = 'view_competences';
    const OP_EDIT_USER_ACCOUNTS = 'edit_user_accounts';
    const OP_VIEW_MEMBERS = 'view_members';
    const OP_VIEW_INDIVIDUAL_PLAN = 'view_individual_plan';
    const OP_EDIT_INDIVIDUAL_PLAN = 'edit_individual_plan';
    const OP_READ_EMPLOYEE_TALK = 'read_employee_talk';
    const OP_CREATE_EMPLOYEE_TALK = 'create_employee_talk';
    const OP_EDIT_EMPLOYEE_TALK = 'edit_employee_talk';

    /**
     * @var int
     * @con_is_primary true
     * @con_is_unique  true
     * @con_sequence   true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?int $operation_id = 0;
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     127
     * @con_index      true
     */
    protected string $operation_string = '';
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     512
     */
    protected string $description = '';
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_index      true
     */
    protected int $list_order = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_index      true
     */
    protected int $context_id = 0;

    public function create() : void
    {
        if (self::where(array(
            'context_id' => $this->getContextId(),
            'operation_string' => $this->getOperationString(),
        ))->hasSets()
        ) {
            throw new ilException('This operation in this context has already been registered.');
        }
        parent::create();
    }

    public function getOperationId() : ?int
    {
        return $this->operation_id;
    }

    public function setOperationId(int $operation_id) : void
    {
        $this->operation_id = $operation_id;
    }

    public function getOperationString() : string
    {
        return $this->operation_string;
    }

    public function setOperationString(string $operation_string) : void
    {
        $this->operation_string = $operation_string;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }

    public function getListOrder() : int
    {
        return $this->list_order;
    }

    public function setListOrder(int $list_order) : void
    {
        $this->list_order = $list_order;
    }

    public function getContextId() : int
    {
        return $this->context_id;
    }

    public function setContextId(int $context_id) : void
    {
        $this->context_id = $context_id;
    }

    public static function returnDbTableName() : string
    {
        return 'il_orgu_operations';
    }
}
