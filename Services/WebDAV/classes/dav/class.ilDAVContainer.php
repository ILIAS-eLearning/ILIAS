<?php declare(strict_types = 1);

use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\INode;
use Sabre\DAV\ICollection;
use Psr\Http\Message\RequestInterface;

/**
 * @author Raphael Heer <raphael.heer@hslu.ch>
 */
class ilDAVContainer implements ICollection
{
    use ilWebDAVCheckValidTitleTrait, ilWebDAVAccessChildrenFunctionsTrait;
    
    protected ilObjUser $current_user;
    protected ilObject $obj;
    protected RequestInterface $request;
    protected ilWebDAVObjFactory $dav_factory;
    protected ilWebDAVRepositoryHelper $repository_helper;
    
    public function __construct(
        ilContainer $a_obj,
        ilObjUser $current_user,
        RequestInterface $request,
        ilWebDAVObjFactory $dav_factory,
        ilWebDAVRepositoryHelper $repository_helper
    ) {
        $this->obj = $a_obj;
        $this->current_user = $current_user;
        $this->request = $request;
        $this->dav_factory = $dav_factory;
        $this->repository_helper = $repository_helper;
    }
    
    public function getName() : string
    {
        return $this->obj->getTitle();
    }
    
    public function getChild($name) : INode
    {
        return $this->getChildByParentRefId(
            $this->repository_helper,
            $this->dav_factory,
            $this->obj->getRefId(),
            $name
        );
    }
    
    /**
     * @return ilObject[]
     */
    public function getChildren() : array
    {
        return $this->getChildrenByParentRefId(
            $this->repository_helper,
            $this->dav_factory,
            $this->obj->getRefId()
        );
    }
    
    public function childExists($name) : bool
    {
        return $this->checkIfChildExistsByParentRefId(
            $this->repository_helper,
            $this->dav_factory,
            $this->obj->getRefId(),
            $name
        );
    }
    
    public function setName($name)
    {
        if (!$this->repository_helper->checkAccess("write", $this->obj->getRefId())) {
            throw new Forbidden('Permission denied');
        }
        
        if ($this->isDAVableObjTitle($name)) {
            $this->obj->setTitle($name);
            $this->obj->update();
        } else {
            throw new Forbidden('Forbidden characters in title');
        }
    }
    
    /**
     * @param resource|string $data Initial payload
     */
    public function createFile($name, $data = null)
    {
        if (!$this->repository_helper->checkCreateAccessForType($this->obj->getRefId(), 'file')) {
            throw new Forbidden('Permission denied');
        }
        
        $size = $this->request->getHeader("Content-Length")[0];
        if ($size > ilFileUploadUtil::getMaxFileSize()) {
            throw new Forbidden('File is too big');
        }
        
        if ($this->childExists($name)) {
            $file_dav = $this->getChild($name);
        } else {
            try {
                $file_obj = new ilObjFile();
                $file_obj->setTitle($name);
                $file_obj->setFileName($name);
                
                $file_dav = $this->dav_factory->createDAVObject($file_obj, $this->obj->getRefId());
            } catch (ilWebDAVNotDavableException $e) {
                throw new Forbidden('Forbidden characters in title');
            }
        }
        
        return $file_dav->put($data);
    }

    public function createDirectory($name)
    {
        $new_obj = $this->getChildCollection();
        
        if (!$this->repository_helper->checkCreateAccessForType($this->obj->getRefId(), $new_obj->getType())) {
            throw new Forbidden('Permission denied');
        }
        
        try {
            $new_obj->setOwner($this->current_user->getId());
            $new_obj->setTitle($name);
            $this->dav_factory->createDAVObject($new_obj, $this->obj->getRefId());
        } catch (ilWebDAVNotDavableException $e) {
            throw new Forbidden('Forbidden characters in title');
        }
    }
    
    public function delete()
    {
        $this->repository_helper->deleteObject($this->obj->getRefId());
    }
    
    public function getLastModified() : ?int
    {
        return ($this->obj == null) ? null : strtotime($this->obj->getLastUpdateDate());
    }
    
    protected function getChildCollection() : ilContainer
    {
        if (get_class($this->obj) === 'cat') {
            return new ilObjCategory();
        }
        
        return new ilObjFolder();
    }
}
