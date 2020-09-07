<?php

/**
 * Class ilOrgUnitPermissionGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPermission extends ActiveRecord
{
    const PARENT_TEMPLATE = -1;
    const TABLE_NAME = 'il_orgu_permissions';
    /**
     * @var int
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_sequence   true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $id = 0;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $context_id = 0;
    /**
     * @var \ilOrgUnitOperation[]
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     2048
     */
    protected $operations = [];
    /**
     * @var \ilOrgUnitOperation[]
     */
    protected $possible_operations = [];
    /**
     * @var int[]
     */
    protected $selected_operation_ids = [];
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $parent_id = self::PARENT_TEMPLATE;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected $position_id = 0;
    /**
     * @var \ilOrgUnitOperationContext
     */
    protected $context;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $protected = false;
    /**
     * @var bool
     */
    protected $newly_created = false;


    public function update()
    {
        if ($this->isProtected()) {
            throw new ilException('Cannot modify a protected ilOrgUnitPermission');
        }
        parent::update();
    }


    public function create()
    {
        if ($this->isProtected()) {
            throw new ilException('Cannot modify a protected ilOrgUnitPermission');
        }
        parent::create();
    }


    public function delete()
    {
        if ($this->isProtected()) {
            throw new ilException('Cannot modify a protected ilOrgUnitPermission');
        }
        parent::delete();
    }


    public function afterObjectLoad()
    {
        $this->possible_operations = ilOrgUnitOperationQueries::getOperationsForContextId($this->getContextId());
        $this->operations = is_array($this->operations) ? $this->operations : array();
        foreach ($this->operations as $operation) {
            $this->selected_operation_ids[] = $operation->getOperationId();
        }
        $this->context = ilOrgUnitOperationContextQueries::findById($this->getContextId());
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return \ilOrgUnitOperation[]
     */
    public function getOperations()
    {
        return $this->operations;
    }


    /**
     * @param \ilOrgUnitOperation[] $operations
     */
    public function setOperations($operations)
    {
        $this->operations = $operations;
    }


    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }


    /**
     * @param int $parent_id
     */
    public function setParentId($parent_id)
    {
        $this->parent_id = $parent_id;
    }


    /**
     * @return \ilOrgUnitOperation[]
     */
    public function getPossibleOperations()
    {
        return $this->possible_operations;
    }


    /**
     * @return int[]
     */
    public function getSelectedOperationIds()
    {
        return $this->selected_operation_ids;
    }


    /**
     * @param $operation_id
     *
     * @return bool
     */
    public function isOperationIdSelected($operation_id)
    {
        return in_array($operation_id, $this->selected_operation_ids);
    }


    /**
     * @return \ilOrgUnitOperationContext
     */
    public function getContext()
    {
        return $this->context;
    }


    /**
     * @param \ilOrgUnitOperationContext $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }


    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return self::TABLE_NAME;
    }


    /**
     * @return int
     */
    public function getPositionId()
    {
        return $this->position_id;
    }


    /**
     * @param int $position_id
     */
    public function setPositionId($position_id)
    {
        $this->position_id = $position_id;
    }


    /**
     * @return bool
     */
    public function isTemplate()
    {
        return ($this->getParentId() == self::PARENT_TEMPLATE);
    }


    /**
     * @return bool
     */
    public function isDedicated()
    {
        return ($this->getParentId() != self::PARENT_TEMPLATE);
    }


    /**
     * @return bool
     */
    public function isProtected()
    {
        return $this->protected;
    }


    /**
     * @param bool $protected
     */
    public function setProtected($protected)
    {
        $this->protected = $protected;
    }


    /**
     * @return bool
     */
    public function isNewlyCreated()
    {
        return $this->newly_created;
    }


    /**
     * @param bool $newly_created
     */
    public function setNewlyCreated($newly_created)
    {
        $this->newly_created = $newly_created;
    }


    /**
     * @param $field_name
     *
     * @return mixed|string
     */
    public function sleep($field_name)
    {
        switch ($field_name) {
            case 'operations':
                $ids = [];
                foreach ($this->operations as $operation) {
                    $ids[] = $operation->getOperationId();
                }

                return json_encode($ids);
        }

        return parent::sleep($field_name);
    }


    /**
     * @param $field_name
     * @param $field_value
     *
     * @return mixed
     */
    public function wakeUp($field_name, $field_value)
    {
        switch ($field_name) {
            case 'operations':
                $ids = json_decode($field_value);
                $ids = is_array($ids) ? $ids : array();
                $operations = [];
                foreach ($ids as $id) {
                    $ilOrgUnitOperation = ilOrgUnitOperationQueries::findById($id);
                    if ($ilOrgUnitOperation) {
                        $operations[] = $ilOrgUnitOperation;
                    }
                }

                return $operations;
        }

        return parent::wakeUp($field_name, $field_value);
    }
}
