<?php
/**
 * Class ilBiblOverviewModelFactory
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblOverviewModelFactory implements ilBiblOverviewModelFactoryInterface {

	/**
	 * @deprecated REFACTOR use active record. Create ilBiblOverviewModel AR, Factory and Interface
	 *
	 * @return array
	 */
	public function getAllOverviewModels() {
		$overviewModels = ilBiblOverviewModel::get();
		$overviewModelsArray = array();
		foreach($overviewModels as $model) {
			if($model->getLiteratureType()) {
				$overviewModelsArray[$model->getFileType()][$model->getLiteratureType()] = $model->getPattern();
			} else {
				$overviewModelsArray[$model->getFileType()] = $model->getPattern();
			}
		}
		return $overviewModelsArray;
	}


	/**
	 * @inheritDoc
	 */
	public function getAllOverviewModelsByType(ilBiblTypeInterface $type) {
		$models = $this->getAllOverviewModels();
		return $models[$type->getId()];

	}
}