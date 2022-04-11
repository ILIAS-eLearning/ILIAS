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
 * Class ilBiblFieldFilterFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFieldFilterFactory implements ilBiblFieldFilterFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function findById(int $id) : \ilBiblFieldFilter
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ilBiblFieldFilter::findOrFail($id);
    }


    /**
     * @inheritDoc
     */
    public function findByFieldId(int $id) : ?\ilBiblFieldFilter
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ilBiblFieldFilter::where(['field_id' => $id])->first();
    }


    /**
     * @inheritDoc
     */
    public function getAllForObjectId(int $obj_id) : array
    {
        return ilBiblFieldFilter::where(['object_id' => $obj_id])->get();
    }


    /**
     * @inheritDoc
     */
    public function filterItemsForTable(int $obj_id, ilBiblTableQueryInfoInterface $info) : array
    {
        $list = ilBiblFieldFilter::where(['object_id' => $obj_id])
            ->limit($info->getOffset(), $info->getLimit())
            ->orderBy($info->getSortingColumn(), $info->getSortingDirection());

        return $list->getArray();
    }


    /**
     * @inheritDoc
     */
    public function getByObjectIdAndField(ilBiblFieldInterface $field, int $object_id) : ilBiblFieldFilterInterface
    {
        $list = ilBiblFieldFilter::where([
            'object_id' => $object_id,
            'field_id' => $field->getId(),
        ])->first();
        if ($list === null) {
            throw new LogicException("filter not found");
        }
    
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $list;
    }
}
