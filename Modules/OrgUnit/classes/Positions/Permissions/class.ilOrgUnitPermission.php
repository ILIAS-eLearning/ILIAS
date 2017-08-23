<?php

/**
 * Class ilOrgUnitPermissionGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPermission extends ActiveRecord {

	const PARENT_TEMPLATE = - 1;
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


	public function afterObjectLoad() {
		$this->possible_operations = ilOrgUnitOperation::getOperationsForContextId($this->getContextId());
		$this->operations = is_array($this->operations) ? $this->operations : array();
		foreach ($this->operations as $operation) {
			$this->selected_operation_ids[] = $operation->getOperationId();
		}
		$this->context = ilOrgUnitOperationContext::find($this->getContextId());
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getContextId() {
		return $this->context_id;
	}


	/**
	 * @param int $context_id
	 */
	public function setContextId($context_id) {
		$this->context_id = $context_id;
	}


	/**
	 * @return \ilOrgUnitOperation[]
	 */
	public function getOperations() {
		return $this->operations;
	}


	/**
	 * @param \ilOrgUnitOperation[] $operations
	 */
	public function setOperations($operations) {
		$this->operations = $operations;
	}


	/**
	 * @return int
	 */
	public function getParentId() {
		return $this->parent_id;
	}


	/**
	 * @param int $parent_id
	 */
	public function setParentId($parent_id) {
		$this->parent_id = $parent_id;
	}


	/**
	 * @return \ilOrgUnitOperation[]
	 */
	public function getPossibleOperations() {
		return $this->possible_operations;
	}


	/**
	 * @return int[]
	 */
	public function getSelectedOperationIds() {
		return $this->selected_operation_ids;
	}


	/**
	 * @param $operation_id
	 *
	 * @return bool
	 */
	public function isOperationIdSelected($operation_id) {
		return in_array($operation_id, $this->selected_operation_ids);
	}


	/**
	 * @return \ilOrgUnitOperationContext
	 */
	public function getContext() {
		return $this->context;
	}


	/**
	 * @param \ilOrgUnitOperationContext $context
	 */
	public function setContext($context) {
		$this->context = $context;
	}


	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'il_orgu_permissions';
	}


	/**
	 * @param $context_name
	 *
	 * @param $position_id
	 *
	 * @return \ilOrgUnitPermission
	 * @throws \ilException
	 */
	public static function getTemplateSetForContextName($context_name, $position_id) {
		// TODO write performant query
		$context = ilOrgUnitOperationContext::findByName($context_name);
		if (!$context) {
			throw new ilException('No context found');
		}
		if (!$position_id) {
			throw new ilException('$position_id cannot be null');
		}

		$template_set = self::where([
			'parent_id'   => self::PARENT_TEMPLATE,
			'context_id'  => $context->getId(),
			'position_id' => $position_id,
		])->first();

		if (!$template_set) {
			$template_set = new self();
			$template_set->setParentId(self::PARENT_TEMPLATE);
			$template_set->setContextId($context->getId());
			$template_set->setPositionId($position_id);
			$template_set->create();
			$template_set->afterObjectLoad();
		}

		return $template_set;
	}


	/**
	 * @param $ref_id
	 *
	 * @param $position_id
	 *
	 * @return \ilOrgUnitPermission
	 * @throws \ilException
	 *
	 */
	public static function getSetForRefId($ref_id, $position_id) {
		// TODO write performant query
		$type_context = ilObject2::_lookupType($ref_id, true);
		$context = ilOrgUnitOperationContext::findByName($ref_id);
		if (!$context) {
			throw new ilException('Context not found');
		}

		if (!$position_id) {
			throw new ilException('$position_id cannot be null');
		}
		/**
		 * @var $dedicated_set ilOrgUnitPermission
		 */
		$dedicated_set = self::where([
			'parent_id'   => $ref_id,
			'context_id'  => $context->getId(),
			'position_id' => $position_id,
		])->first();
		if ($dedicated_set) {
			return $dedicated_set;
		}

		return self::getTemplateSetForContextName($type_context, $position_id);
	}


	/**
	 * @param $position_id
	 *
	 * @return \ilOrgUnitPermission[]
	 */
	public static function getAllTemplateSetsForAllActivedContexts($position_id) {
		$activated_components = [];
		foreach (ilOrgUnitGlobalSettings::getInstance()
		                                ->getPositionSettings() as $ilOrgUnitObjectPositionSetting) {
			if ($ilOrgUnitObjectPositionSetting->isActive()) {
				$activated_components[] = $ilOrgUnitObjectPositionSetting->getType();
			}
		}
		$sets = [];
		foreach ($activated_components as $context) {
			$sets[] = self::getTemplateSetForContextName($context, $position_id);
		}

		return $sets;
	}


	/**
	 * @return int
	 */
	public function getPositionId() {
		return $this->position_id;
	}


	/**
	 * @param int $position_id
	 */
	public function setPositionId($position_id) {
		$this->position_id = $position_id;
	}


	/**
	 * @param $field_name
	 *
	 * @return mixed|string
	 */
	public function sleep($field_name) {
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
	public function wakeUp($field_name, $field_value) {
		switch ($field_name) {
			case 'operations':
				$ids = json_decode($field_value);
				$ids = is_array($ids) ? $ids : array();
				$operations = [];
				foreach ($ids as $id) {
					$operations[] = ilOrgUnitOperation::find($id);
				}

				return $operations;
		}

		return parent::wakeUp($field_name, $field_value);
	}
}
