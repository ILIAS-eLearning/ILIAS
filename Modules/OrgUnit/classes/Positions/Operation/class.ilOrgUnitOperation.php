<?php

/**
 * Class ilOrgUnitOperation
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitOperation extends ActiveRecord {

	const OPERATION_VIEW_LEARNING_PROGRESS = 'viewlp';
	const OPERATION_VIEW_TEST_RESULTS = 'viewtstr';
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
	 * @con_length     16
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


	/**
	 * @param        $operation_name
	 * @param        $description
	 * @param string $context ilOrgUnitOperationContext::CONTEXT_OBJECT will provide this new
	 *                        operation to all contexts such as
	 *                        ilOrgUnitOperationContext::CONTEXT_GRP or
	 *                        ilOrgUnitOperationContext::CONTEXT_CRS
	 *                        use a more specific for your object type but the related context must
	 *                        exist. Register a new context using
	 *                        ilOrgUnitOperationContext::registerNewContext() for plugins
	 *
	 * @throws \ilException
	 */
	public static function registerNewOperation($operation_name, $description, $context = ilOrgUnitOperationContext::CONTEXT_OBJECT) {
		$contextList = ilOrgUnitOperationContext::where(array( 'context' => $context ));
		if (!$contextList->hasSets()) {
			throw new ilException('Context does not exist! register context first using ilOrgUnitOperationContext::registerNewContext()');
		}
		/**
		 * @var $ilOrgUnitOperationContext \ilOrgUnitOperationContext
		 */
		$ilOrgUnitOperationContext = $contextList->first();

		if (self::where(array(
			'context_id'       => $ilOrgUnitOperationContext->getId(),
			'operation_string' => $operation_name,
		))->hasSets()) {
			throw new ilException('This operation in this context has already been registered.');
		}
		$operation = new self();
		$operation->setOperationString($operation_name);
		$operation->setContextId($ilOrgUnitOperationContext->getId());
		$operation->setDescription($description);
		$operation->create();
	}


	/**
	 * @param       $operation_name
	 * @param       $description
	 * @param array $contexts
	 *
	 * @see registerNewOperation
	 */
	public static function registerNewOperationForMultipleContexts($operation_name, $description, array $contexts) {
		foreach ($contexts as $context) {
			self::registerNewOperation($operation_name, $description, $context);
		}
	}


	public function create() {
		if (self::where(array(
			'context_id'       => $this->getContextId(),
			'operation_string' => $this->getOperationString(),
		))->hasSets()) {
			throw new ilException('This operation in this context has already been registered.');
		}
		parent::create();
	}


	/**
	 * @param $context_name
	 *
	 * @return ilOrgUnitOperation[]
	 */
	public static function getOperationsForContextName($context_name) {
		$context = ilOrgUnitOperationContext::findByName($context_name);

		return self::where(array( 'context_id' => $context->getPopulatedContextIds() ))->get();
	}


	/**
	 * @param $context_id
	 *
	 * @return \ilOrgUnitOperation[]
	 */
	public static function getOperationsForContextId($context_id) {
		/**
		 * @var $context ilOrgUnitOperationContext
		 */
		$context = ilOrgUnitOperationContext::find($context_id);

		return self::where(array( 'context_id' => $context->getPopulatedContextIds() ))->get();
	}


	/**
	 * @return int
	 */
	public function getOperationId() {
		return $this->operation_id;
	}


	/**
	 * @param int $operation_id
	 */
	public function setOperationId($operation_id) {
		$this->operation_id = $operation_id;
	}


	/**
	 * @return string
	 */
	public function getOperationString() {
		return $this->operation_string;
	}


	/**
	 * @param string $operation_string
	 */
	public function setOperationString($operation_string) {
		$this->operation_string = $operation_string;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return int
	 */
	public function getListOrder() {
		return $this->list_order;
	}


	/**
	 * @param int $list_order
	 */
	public function setListOrder($list_order) {
		$this->list_order = $list_order;
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
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'il_orgu_operations';
	}
}
