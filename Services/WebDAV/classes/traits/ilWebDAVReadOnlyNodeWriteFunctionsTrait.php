<?php declare(strict_types = 1);

use Sabre\DAV\Exception\Forbidden;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
