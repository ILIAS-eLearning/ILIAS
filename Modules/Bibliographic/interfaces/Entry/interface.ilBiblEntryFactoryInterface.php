<?php declare(strict_types=1);

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
 * Interface ilBiblEntryFactoryInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblEntryFactoryInterface
{
    
    /**
     * @deprecated REFACTOR This has to be refactored to type_id and not type_string
     */
    public function findByIdAndTypeString(int $id, string $type_string) : ilBiblEntryInterface;
    
    public function findOrCreateEntry(int $id, int $bibliographic_obj_id, string $entry_type) : \ilBiblEntryInterface;
    
    public function createEntry(int $bibliographic_obj_id, string $entry_type) : \ilBiblEntryInterface;
    
    public function getEmptyInstance() : \ilBiblEntry;
    
    /**
     * @param \ilBiblTableQueryInfo|null $info
     * @return \ilBiblEntryInterface[]
     */
    public function filterEntriesForTable(int $object_id, ilBiblTableQueryInfo $info = null) : array;
    
    public function filterEntryIdsForTableAsArray(int $object_id, ?ilBiblTableQueryInfo $info = null) : array;
    
    public function deleteEntryById(int $id) : void;
    
    public function deleteEntriesById(int $object_id) : void;
    
    /**
     * Reads all the entrys attributes from database
     */
    public function loadParsedAttributesByEntryId(int $entry_id) : array;
}
