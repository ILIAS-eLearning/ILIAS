<?php
/**
 * Class ilBiblOverviewModelFactory
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblOverviewModelFactory implements ilBiblOverviewModelFactoryInterface
{

    /**
     * @var array
     */
    protected static $models = [];


    /**
     * @deprecated REFACTOR use active record. Create ilBiblOverviewModel AR, Factory and Interface
     *
     * @return array
     */
    private function getAllOverviewModels()
    {
        if (count(self::$models) > 0) {
            return self::$models;
        }
        /**
         * @var $overviewModels ilBiblOverviewModel[]
         */
        $overviewModels = ilBiblOverviewModel::get();
        $overviewModelsArray = array();
        foreach ($overviewModels as $model) {
            if ($model->getLiteratureType()) {
                $overviewModelsArray[(int) $model->getFileTypeId()][$model->getLiteratureType()] = $model->getPattern();
            } else {
                $overviewModelsArray[(int) $model->getFileTypeId()] = $model->getPattern();
            }
        }
        self::$models = $overviewModelsArray;

        return $overviewModelsArray;
    }


    /**
     * @inheritDoc
     */
    public function getAllOverviewModelsByType(ilBiblTypeInterface $type)
    {
        $models = $this->getAllOverviewModels();

        $id = $type->getId();

        return $models[$id];
    }
}
