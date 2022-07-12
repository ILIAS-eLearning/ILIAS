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
 
use Sabre\DAV\Exception\NotFound;
use ILIAS\ResourceStorage\Services;
use Sabre\DAV\INode;
use Sabre\DAV\Exception\Forbidden;
use Psr\Http\Message\RequestInterface;

/**
 * @author Stephan Winiker <stephan.winiker@hslu.ch>
 */
class ilWebDAVObjFactory
{
    use ilWebDAVCheckValidTitleTrait;
    
    /**
     * @var string[]
     */
    private array $davable_object_types = [
        'cat',
        'crs',
        'fold',
        'file',
        'grp'
    ];
    
    protected ilWebDAVRepositoryHelper $repository_helper;
    protected ilObjUser $current_user;
    protected Services $resource_storage;
    protected RequestInterface $request;
    protected ilLanguage $language;
    protected string $client_id;
    protected bool $versioning_enabled;
    
    public function __construct(
        ilWebDAVRepositoryHelper $repository_helper,
        ilObjUser $current_user,
        Services $resource_storage,
        RequestInterface $request,
        ilLanguage $language,
        string $client_id,
        bool $versioning_enabled
    ) {
        $this->repository_helper = $repository_helper;
        $this->current_user = $current_user;
        $this->resource_storage = $resource_storage;
        $this->request = $request;
        $this->language = $language;
        $this->client_id = $client_id;
        $this->versioning_enabled = $versioning_enabled;
    }
    
    public function retrieveDAVObjectByRefID(int $ref_id) : INode
    {
        if (!$this->checkReadAndVisibleAccessForObj($ref_id)) {
            throw new Forbidden("No read permission for object with reference ID $ref_id");
        }
        
        $ilias_object = ilObjectFactory::getInstanceByRefId($ref_id);
        
        if (!$ilias_object) {
            throw new NotFound();
        }
        
        $ilias_object_type = $ilias_object->getType();
        
        if (!in_array($ilias_object_type, $this->davable_object_types)) {
            throw new ilWebDAVNotDavableException(ilWebDAVNotDavableException::OBJECT_TYPE_NOT_DAVABLE);
        }
        
        if (!$this->isDAVableObjTitle($ilias_object->getTitle())) {
            throw new ilWebDAVNotDavableException(ilWebDAVNotDavableException::OBJECT_TITLE_NOT_DAVABLE);
        }
        
        if ($ilias_object_type === 'file') {
            if (!$this->hasValidFileExtension($ilias_object->getTitle())) {
                throw new ilWebDAVNotDavableException(ilWebDAVNotDavableException::FILE_EXTENSION_NOT_ALLOWED);
            }
            return new ilDAVFile(
                $ilias_object,
                $this->repository_helper,
                $this->resource_storage,
                $this->request,
                $this,
                $this->versioning_enabled
            );
        }
        
        return new ilDAVContainer(
            $ilias_object,
            $this->current_user,
            $this->request,
            $this,
            $this->repository_helper
        );
    }
    
    public function createDAVObject(ilObject $ilias_object, int $parent_ref_id) : INode
    {
        if (!$this->isDAVableObjTitle($ilias_object->getTitle())) {
            throw new ilWebDAVNotDavableException(ilWebDAVNotDavableException::OBJECT_TITLE_NOT_DAVABLE);
        }
        
        if ($ilias_object->getType() === 'file' &&
            !$this->hasValidFileExtension($ilias_object->getTitle())) {
            throw new ilWebDAVNotDavableException(ilWebDAVNotDavableException::OBJECT_TITLE_NOT_DAVABLE);
        }
        
        $ilias_object->create();
        
        $ilias_object->createReference();
        $ilias_object->putInTree($parent_ref_id);
        $ilias_object->setPermissions($parent_ref_id);
        
        if ($ilias_object->getType() === 'file') {
            return new ilDAVFile(
                $ilias_object,
                $this->repository_helper,
                $this->resource_storage,
                $this->request,
                $this,
                $this->versioning_enabled
            );
        }
        
        return new ilDAVContainer(
            $ilias_object,
            $this->current_user,
            $this->request,
            $this,
            $this->repository_helper
        );
    }
    
    public function getProblemInfoFile(int $container_ref_id) : ilDAVProblemInfoFile
    {
        return new ilDAVProblemInfoFile($container_ref_id, $this->repository_helper, $this, $this->language);
    }
    
    public function getMountPoint() : ilDAVMountPoint
    {
        return new ilDAVMountPoint($this->client_id, $this, $this->repository_helper, $this->current_user);
    }
    
    public function getClientNode(string $name) : ilDAVClientNode
    {
        if ($name !== $this->client_id) {
            throw new NotFound();
        }
        
        return new ilDAVClientNode($this->client_id, $this, $this->repository_helper);
    }
    
    protected function checkReadAndVisibleAccessForObj(int $child_ref) : bool
    {
        return $this->repository_helper->checkAccess("visible", $child_ref) && $this->repository_helper->checkAccess("read", $child_ref);
    }
}
