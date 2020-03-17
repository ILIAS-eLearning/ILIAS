<?php

/**
 * Class ilOrgUnitOperation
 *
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

    /**
     * @var int
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_sequence   true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $operation_id = 0;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     127
     * @con_index      true
     */
    protected $operation_string = '';
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     512
     */
    protected $description = '';
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_index      true
     */
    protected $list_order = 0;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_index      true
     */
    protected $context_id = 0;


    public function create()
    {
        if (self::where(array(
            'context_id' => $this->getContextId(),
            'operation_string' => $this->getOperationString(),
        ))->hasSets()) {
            throw new ilException('This operation in this context has already been registered.');
        }
        parent::create();
    }


    /**
     * @return int
     */
    public function getOperationId()
    {
        return $this->operation_id;
    }


    /**
     * @param int $operation_id
     */
    public function setOperationId($operation_id)
    {
        $this->operation_id = $operation_id;
    }


    /**
     * @return string
     */
    public function getOperationString()
    {
        return $this->operation_string;
    }


    /**
     * @param string $operation_string
     */
    public function setOperationString($operation_string)
    {
        $this->operation_string = $operation_string;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    /**
     * @return int
     */
    public function getListOrder()
    {
        return $this->list_order;
    }


    /**
     * @param int $list_order
     */
    public function setListOrder($list_order)
    {
        $this->list_order = $list_order;
    }


    /**
     * @return int
     */
    public function getContextId()
    {
        return $this->context_id;
    }


    /**
     * @param int $context_id
     */
    public function setContextId($context_id)
    {
        $this->context_id = $context_id;
    }


    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return 'il_orgu_operations';
    }
}
