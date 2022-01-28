<?php declare(strict_types = 1);

interface ilWebDAVMountInstructionsRepository
{
    public function createMountInstructionsDocumentEntry(ilWebDAVMountInstructionsDocument $document) : void;
    
    public function getNextMountInstructionsDocumentId() : int;
    
    public function getHighestSortingNumber() : int;
    
    public function getMountInstructionsDocumentById(int $id) : ilWebDAVMountInstructionsDocument;
    
    public function getMountInstructionsByLanguage(string $language) : ilWebDAVMountInstructionsDocument;
    
    public function getAllMountInstructions() : array;
    
    public function doMountInstructionsExistByLanguage(string $language) : int;
    
    public function updateMountInstructions(ilWebDAVMountInstructionsDocument $document);
    
    public function updateSortingValueById(int $id, int $a_new_sorting_value);
    
    public function deleteMountInstructionsById(int $id);
}
