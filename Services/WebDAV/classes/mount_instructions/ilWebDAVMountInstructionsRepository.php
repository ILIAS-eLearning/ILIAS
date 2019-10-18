<?php

interface ilWebDAVMountInstructionsRepository
{
    public function createMountInstructionsDocumentEntry(ilWebDAVMountInstructionsDocument $document);
    public function getNextMountInstructionsDocumentId() : int;
    public function getHighestSortingNumber() : int;
    public function getMountInstructionsDocumentById(int $id) : ilWebDAVMountInstructionsDocument;
    public function getMountInstructionsByLanguage(string $language) : ilWebDAVMountInstructionsDocument;
    public function getAllMountInstructions() : array;
    public function doMountInstructionsExistByLanguage(string $language) : bool;
    public function updateMountInstructionsById(ilWebDAVMountInstructionsDocument $document);
    public function updateSortingValueById($id, $a_new_sorting_value);
    public function deleteMountInstructionsById(int $id);

}