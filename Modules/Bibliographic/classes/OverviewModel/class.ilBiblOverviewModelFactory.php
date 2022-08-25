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
 *********************************************************************/

/**
 * Class ilBiblOverviewModelFactory
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
class ilBiblOverviewModelFactory implements ilBiblOverviewModelFactoryInterface
{
    protected static array $models = [];


    /**
     * @deprecated REFACTOR use active record. Create ilBiblOverviewModel AR, Factory and Interface
     * @return mixed[]
     */
    private function getAllOverviewModels(): array
    {
        if (self::$models !== []) {
            return self::$models;
        }
        /**
         * @var ilBiblOverviewModel[] $overviewModels
         */
        $overviewModels = ilBiblOverviewModel::get();
        $overviewModelsArray = array();
        foreach ($overviewModels as $model) {
            if ($model->getLiteratureType()) {
                $overviewModelsArray[$model->getFileTypeId()][$model->getLiteratureType()] = $model->getPattern();
            } else {
                $overviewModelsArray[$model->getFileTypeId()] = $model->getPattern();
            }
        }
        self::$models = $overviewModelsArray;

        return $overviewModelsArray;
    }


    /**
     * @inheritDoc
     */
    public function getAllOverviewModelsByType(ilBiblTypeInterface $type): array
    {
        $models = $this->getAllOverviewModels();

        $id = $type->getId();

        return $models[$id];
    }
}
