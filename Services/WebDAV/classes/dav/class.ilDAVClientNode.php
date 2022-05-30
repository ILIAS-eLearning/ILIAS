<?php declare(strict_types = 1);

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
 
use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\ICollection;
use Sabre\DAV\INode;

/**
 * @author Raphael Heer <raphael.heer@hslu.ch>
 */
class ilDAVClientNode implements ICollection
{
    use ilWebDAVReadOnlyNodeWriteFunctionsTrait, ilWebDAVAccessChildrenFunctionsTrait;
    
    protected ilWebDAVObjFactory $dav_factory;
    protected ilWebDAVRepositoryHelper $repository_helper;
    protected string $client_name;
    protected string $name_of_repository_root;
    
    public function __construct(
        string $client_name,
        ilWebDAVObjFactory $dav_factory,
        ilWebDAVRepositoryHelper $repository_helper
    ) {
        $this->dav_factory = $dav_factory;
        $this->repository_helper = $repository_helper;
        $this->client_name = $client_name;
        $this->name_of_repository_root = 'ILIAS';
    }
    
    public function getName() : string
    {
        return $this->client_name;
    }
    
    public function getChild($name) : INode
    {
        try {
            $ref_id = $this->getRefIdFromName($name);
            
            return $this->dav_factory->retrieveDAVObjectByRefID($ref_id);
        } catch (NotFound $e) {
        }
        
        return $this->getChildByParentRefId($this->repository_helper, $this->dav_factory, ROOT_FOLDER_ID, $name);
    }

    /**
     * @return ilObject[]
     */
    public function getChildren() : array
    {
        return $this->getChildrenByParentRefId($this->repository_helper, $this->dav_factory, ROOT_FOLDER_ID);
    }
    
    public function childExists($name) : bool
    {
        try {
            $ref_id = $this->getRefIdFromName($name);
            return $this->repository_helper->objectWithRefIdExists($ref_id) && $this->repository_helper->checkAccess('read', $ref_id);
        } catch (BadRequest $e) {
        }
        
        return $this->checkIfChildExistsByParentRefId($this->repository_helper, $this->dav_factory, ROOT_FOLDER_ID, $name);
    }

    public function getLastModified() : int
    {
        return strtotime('2000-01-01');
    }

    protected function getRefIdFromName(string $name) : int
    {
        $ref_parts = explode('_', $name);
        if (count($ref_parts) == 2 && ($ref_id = (int) $ref_parts[1]) > 0) {
            return $ref_id;
        }
        
        throw new NotFound("No id found for $name");
    }
}
