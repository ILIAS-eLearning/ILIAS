<?php declare(strict_types = 1);

use Sabre\DAV\Exception\Forbidden;

trait ilWebDAVReadOnlyNodeWriteFunctionsTrait
{
    public function createDirectory($name)
    {
        throw new Forbidden("It is not possible to create a directory here");
    }
    
    public function createFile($name, $data = null)
    {
        throw new Forbidden("It is not possible to create a file here");
    }
    
    public function setName($name)
    {
        throw new Forbidden("It is not possible to change the name of the root");
    }
    
    public function delete()
    {
        throw new Forbidden("It is not possible to delete the root");
    }
}
