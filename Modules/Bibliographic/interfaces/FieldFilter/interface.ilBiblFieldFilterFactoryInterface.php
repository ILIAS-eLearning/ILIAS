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
 * Interface ilBiblFieldFilterFactoryInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFieldFilterFactoryInterface
{
    public function findById(int $id) : \ilBiblFieldFilter;
    
    /**
     * @return ilBiblFieldFilterInterface[]
     */
    public function getAllForObjectId(int $obj_id) : array;
    
    public function filterItemsForTable(int $obj_id, ilBiblTableQueryInfoInterface $info) : array;
    
    public function getByObjectIdAndField(ilBiblFieldInterface $field, int $object_id) : ilBiblFieldFilterInterface;
}
