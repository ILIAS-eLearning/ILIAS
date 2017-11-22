<?php

/**
 * Class ilBiblFieldFilterFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFieldFilterFactory implements ilBiblFieldFilterFactoryInterface {

	/**
	 * @inheritDoc
	 */
	public function findById($id) {
		return ilBiblFieldFilter::findOrFail($id);
	}


	/**
	 * @inheritDoc
	 */
	public function getAllForObjectId($obj_id) {
		return ilBiblFieldFilter::where([ 'object_id' => $obj_id ])->get();
	}


	/**
	 * @inheritDoc
	 */
	public function filterItemsForTable($obj_id, ilBiblTableQueryInfoInterface $info) {
		$list = ilBiblFieldFilter::where([ 'object_id' => $obj_id ])
		                         ->limit($info->getOffset(), $info->getLimit())
		                         ->orderBy($info->getSortingColumn(), $info->getSortingDirection());

		return $list->getArray();
	}
}
