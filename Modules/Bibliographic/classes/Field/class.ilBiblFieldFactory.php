<?php

/**
 * Class ilBiblFieldFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFieldFactory implements ilBiblFieldFactoryInterface {

	/**
	 * @var \ilBiblTypeInterface
	 */
	protected $type;


	/**
	 * ilBiblFieldFactory constructor.
	 *
	 * @param \ilBiblTypeInterface $type
	 */
	public function __construct(\ilBiblTypeInterface $type) { $this->type = $type; }


	/**
	 * @return \ilBiblTypeInterface
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * @inheritDoc
	 */
	public function findById($id) {
		/**
		 * @var $inst ilBiblField
		 */
		$inst = ilBiblField::findOrFail($id);
		if ($this->type->isStandardField($inst->getIdentifier()) != $inst->getisStandardField()) {
			$inst->setIsStandardField($this->type->isStandardField($inst->getIdentifier()));
			$inst->update();
		}

		return $inst;
	}


	/**
	 * @inheritdoc
	 */
	public function getFieldByTypeAndIdentifier($type, $identifier) {
		$this->checkType($type);
		$inst = $this->getARInstance($type, $identifier);
		if (!$inst) {
			throw new ilException("bibliografic identifier {$identifier} not found");
		}
		return $inst;
	}


	/**
	 * @inheritdoc
	 */
	public function findOrCreateFieldByTypeAndIdentifier($type, $identifier) {
		$this->checkType($type);
		$inst = $this->getARInstance($type, $identifier);
		if (!$inst) {
			$inst = new ilBiblField();
			$inst->setIdentifier($identifier);
			$inst->setDataType($type);
			$inst->setIsStandardField((bool)$this->getType()->isStandardField($identifier));
			$inst->create();
		}
		$inst->setDataType($type);
		$inst->setIdentifier($identifier);
		$inst->setIsStandardField((bool)$this->getType()->isStandardField($identifier));
		$inst->update();

		return $inst;
	}


	/**
	 * @inheritDoc
	 */
	public function getAvailableFieldsForObjId($obj_id) {
		global $DIC;
		$sql = "SELECT DISTINCT(il_bibl_attribute.name), il_bibl_data.file_type FROM il_bibl_data 
					JOIN il_bibl_entry ON il_bibl_entry.data_id = il_bibl_data.id
					JOIN il_bibl_attribute ON il_bibl_attribute.entry_id = il_bibl_entry.id
				WHERE il_bibl_data.id = %s;";

		$result = $DIC->database()->queryF($sql, [ 'integer' ], [ $obj_id ]);

		$data = [];
		while ($d = $DIC->database()->fetchObject($result)) {
			$data[] = $this->findOrCreateFieldByTypeAndIdentifier($d->file_type, $d->name);
		}

		return $data;
	}


	/**
	 * @inheritDoc
	 */
	public function filterAllFieldsForType(ilBiblTypeInterface $type, ilBiblTableQueryInfoInterface $queryInfo = null) {
		return $this->getCollectionForFilter($type, $queryInfo)->get();
	}


	/**
	 * @inheritDoc
	 */
	public function filterAllFieldsForTypeAsArray(ilBiblTypeInterface $type, ilBiblTableQueryInfoInterface $queryInfo = null) {
		return $this->getCollectionForFilter($type, $queryInfo)->getArray();
	}

	/**
	 * @inheritDoc
	 */
	public function findOrCreateFieldOfAttribute(ilBiblAttributeInterface $ilBiblAttribute) {
		$field = ilBiblField::where([ 'identifier' => $ilBiblAttribute->getName() ])->first();
		if ($field === null) {
			$field = new ilBiblField();
			$field->setIdentifier($ilBiblAttribute->getName());
			$field->setDataType($this->type->getId());
			$field->setIsStandardField($this->type->isStandardField($ilBiblAttribute->getName()));
			$field->create();
		} else {
			$field->setDataType($this->type->getId());
			$field->update();
		}
		return $field;
	}


	// Internal Methods


	/**
	 * @param int    $type
	 * @param string $identifier
	 *
	 * @return \ilBiblField
	 */
	private function getARInstance($type, $identifier) {
		return ilBiblField::where([ "identifier" => $identifier, "data_type" => $type ])->first();
	}


	/**
	 * @param $type
	 *
	 * @throws \ilException
	 */
	private function checkType($type) {
		switch ($type) {
			case ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX:
			case ilBiblTypeFactoryInterface::DATA_TYPE_RIS:
				break;
			default:
				throw new ilException("bibliografic type not found");
		}
	}


	/**
	 * @param \ilBiblTypeInterface           $type
	 * @param \ilBiblTableQueryInfoInterface $queryInfo
	 *
	 * @return \ActiveRecordList
	 */
	private function getCollectionForFilter(ilBiblTypeInterface $type, ilBiblTableQueryInfoInterface $queryInfo = null) {
		$collection = ilBiblField::getCollection();

		$collection->where(array( 'data_type' => $type->getId() ));

		if ($queryInfo) {
			$sorting_column = $queryInfo->getSortingColumn() ? $queryInfo->getSortingColumn() : null;
			$offset = $queryInfo->getOffset() ? $queryInfo->getOffset() : 0;
			$sorting_direction = $queryInfo->getSortingDirection();
			$limit = $queryInfo->getLimit();
			if ($sorting_column) {
				$collection->orderBy($sorting_column, $sorting_direction);
			}
			$collection->limit($offset, $limit);

			foreach ($queryInfo->getFilters() as $queryFilter) {
				switch ($queryFilter->getFieldName()) {
					default:
						$collection->where(array( $queryFilter->getFieldName() => $queryFilter->getFieldValue() ), $queryFilter->getOperator());
						break;
				}
			}
		}

		return $collection;
	}
}

