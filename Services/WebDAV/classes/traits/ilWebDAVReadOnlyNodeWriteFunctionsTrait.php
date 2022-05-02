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
    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\ICollection::createDirectory()
     */
    public function createDirectory($name) : void
    {
        throw new Forbidden("It is not possible to create a directory here");
    }
    
    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\ICollection::createFile()
     */
    public function createFile($name, $data = null) : ?string
    {
        throw new Forbidden("It is not possible to create a file here");
    }
    
    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\INode::setName()
     */
    public function setName($name) : void
    {
        throw new Forbidden("It is not possible to change the name of the root");
    }
    
    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\INode::delete()
     */
    public function delete() : void
    {
        throw new Forbidden("It is not possible to delete the root");
    }
}
