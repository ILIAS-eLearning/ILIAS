<?php

interface ilWebDAVMountInstructionsRepository
{
    public function createMountInstructionsDocumentEntry(ilWebDAVMountInstructionsDocument $document);
    public function getNextMountInstructionsDocumentId() : int;
    public function getHighestSortingNumber() : int;
    public function getMountInstructionsDocumentById(int $id) : ilWebDAVMountInstructionsDocument;
    public function getMountInstructionsByLanguage(string $language) : ilWebDAVMountInstructionsDocument;
    public function getAllMountInstructions() : array;
    public function updateMountInstructionsById(ilWebDAVMountInstructionsDocument $document);
    public function deleteMountInstructionsById(int $id);

}