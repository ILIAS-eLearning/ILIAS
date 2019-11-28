<?php

/**
 * Interface ilWebDAVMountInstructionsRepository
 */
interface ilWebDAVMountInstructionsRepository
{
    /**
     * Create new database entry for given mount instructions document
     *
     * @param ilWebDAVMountInstructionsDocument $document
     * @return void
     */
    public function createMountInstructionsDocumentEntry(ilWebDAVMountInstructionsDocument $document);

    /**
     * Return next free ID for mount instructions document
     *
     * @return int $nextId
     */
    public function getNextMountInstructionsDocumentId() : int;

    /**
     * Get currently highest sorting number for mount instructions document
     *
     * @return int
     */
    public function getHighestSortingNumber() : int;

    /**
     * Gets mount instructions document by id
     *
     * @param int $id
     * @return ilWebDAVMountInstructionsDocument $document
     * @throws InvalidArgumentException
     */
    public function getMountInstructionsDocumentById(int $id) : ilWebDAVMountInstructionsDocument;

    /**
     * Gets mount instructions document by language -> language is two letters like "en" for English etc.
     *
     * @param string $language
     * @return ilWebDAVMountInstructionsDocument $document
     * @throws InvalidArgumentException
     */
    public function getMountInstructionsByLanguage(string $language) : ilWebDAVMountInstructionsDocument;

    /**
     * Returns an array with all existing mount instructions documents
     *
     * @return array
     */
    public function getAllMountInstructions() : array;

    /**
     * Check if mount instructions for language exists. Language is two letters like "en" for English etc.
     * Returns id of mount instructions, if found, or 0.
     *
     * @param string $language
     * @return int
     */
    public function doMountInstructionsExistByLanguage(string $language) : int;

    /**
     * Update existing mount instructions document
     *
     * @param ilWebDAVMountInstructionsDocument $document
     * @return void
     */
    public function updateMountInstructions(ilWebDAVMountInstructionsDocument $document);

    /**
     * Update sorting value of a mount instructions document
     *
     * @param int $id
     * @param int $a_new_sorting_value
     * @return void
     */
    public function updateSortingValueById(int $id, int $a_new_sorting_value);

    /**
     * Delete mount instructions document by id
     *
     * @param int $id
     * @return void
     */
    public function deleteMountInstructionsById(int $id);
}