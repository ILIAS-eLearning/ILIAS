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
 
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\INode;
use Sabre\DAV\ICollection;
use Psr\Http\Message\RequestInterface;

/**
 * @author Raphael Heer <raphael.heer@hslu.ch>
 */
class ilDAVContainer implements ICollection
{
    use ilWebDAVCheckValidTitleTrait, ilWebDAVAccessChildrenFunctionsTrait, ilWebDAVCommonINodeFunctionsTrait;
    
    protected ilObjUser $current_user;
    protected ilObject $obj;
    protected RequestInterface $request;
    protected ilWebDAVObjFactory $dav_factory;
    protected ilWebDAVRepositoryHelper $repository_helper;
    
    public function __construct(
        ilContainer $obj,
        ilObjUser $current_user,
        RequestInterface $request,
        ilWebDAVObjFactory $dav_factory,
        ilWebDAVRepositoryHelper $repository_helper
    ) {
        $this->obj = $obj;
        $this->current_user = $current_user;
        $this->request = $request;
        $this->dav_factory = $dav_factory;
        $this->repository_helper = $repository_helper;
    }
    
    public function getName() : string
    {
        return $this->obj->getTitle();
    }
    
    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\ICollection::getChild()
     */
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
    
    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\ICollection::childExists()
     */
    public function childExists($name) : bool
    {
        return $this->checkIfChildExistsByParentRefId(
            $this->repository_helper,
            $this->dav_factory,
            $this->obj->getRefId(),
            $name
        );
    }
    
    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\INode::setName()
     */
    public function setName($name) : void
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
     * {@inheritDoc}
     * @see \Sabre\DAV\ICollection::createFile()
     */
    public function createFile($name, $data = null) : ?string
    {
        if (!$this->repository_helper->checkCreateAccessForType($this->obj->getRefId(), 'file')) {
            throw new Forbidden('Permission denied');
        }
        
        $size = $this->request->getHeader("Content-Length")[0];
        if ($size === 0 && $this->request->hasHeader('X-Expected-Entity-Length')) {
            $size = $this->request->getHeader('X-Expected-Entity-Length')[0];
        }
        
        if ($size > ilFileUtils::getUploadSizeLimitBytes()) {
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

    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\ICollection::createDirectory()
     */
    public function createDirectory($name) : void
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
    
    public function delete() : void
    {
        $this->repository_helper->deleteObject($this->obj->getRefId());
    }
    
    public function getLastModified() : ?int
    {
        return $this->retrieveLastModifiedAsIntFromObjectLastUpdateString($this->obj->getLastUpdateDate());
    }
    
    protected function getChildCollection() : ilContainer
    {
        if (get_class($this->obj) === 'ilObjCategory') {
            return new ilObjCategory();
        }
        
        return new ilObjFolder();
    }
}
