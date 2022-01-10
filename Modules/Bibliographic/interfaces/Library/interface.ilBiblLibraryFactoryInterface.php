<?php

/**
 * Interface ilBiblLibraryFactoryInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblLibraryFactoryInterface
{

    public function getAll() : array;

    public function findById(int $id) : \ilBiblLibraryInterface;

    public function getEmptyInstance() : \ilBiblLibraryInterface;
}
