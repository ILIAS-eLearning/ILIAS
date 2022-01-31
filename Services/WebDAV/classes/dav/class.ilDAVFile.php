<?php declare(strict_types = 1);

use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Services;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Consumer\Consumers;
use Sabre\DAV\IFile;

/**
 * @author Raphael Heer <raphael.heer@hslu.ch>
 */
class ilDAVFile implements IFile
{
    use ilObjFileNews, ilWebDAVCheckValidTitleTrait;
    
    protected ilObjFile $obj;
    protected ilWebDAVRepositoryHelper $repo_helper;
    protected Manager $resource_manager;
    protected Consumers $resource_consumer;
    protected ilWebDAVObjFactory $dav_factory;

    protected bool $versioning_enabled;

    public function __construct(
        ilObjFile $obj,
        ilWebDAVRepositoryHelper $repo_helper,
        Services $resource_storage,
        ilWebDAVObjFactory $dav_factory,
        bool $versioning_enabled
    ) {
        $this->obj = $obj;
        $this->repo_helper = $repo_helper;
        $this->resource_manager = $resource_storage->manage();
        $this->resource_consumer = $resource_storage->consume();
        $this->dav_factory = $dav_factory;
        $this->versioning_enabled = $versioning_enabled;
    }

    /**
     * @param string|resource $data
     */
    public function put($data) : ?string
    {
        if (!$this->repo_helper->checkAccess('write', $this->obj->getRefId())) {
            throw new Forbidden("Permission denied. No write access for this file");
        }
        
        if ($this->getSize() === 0) {
            $parent_ref_id = $this->repo_helper->getParentOfRefId($this->obj->getRefId());
            $obj_id = $this->obj->getId();
            $this->repo_helper->deleteObject($this->obj->getRefId());
            $file_obj = new ilObjFile();
            $file_obj->setTitle($this->getName());
            $file_obj->setFileName($this->getName());
            
            $file_dav = $this->dav_factory->createDAVObject($file_obj, $parent_ref_id);
            $this->repo_helper->updateLocksAfterResettingObject($obj_id, $file_obj->getId());
            return $file_dav->put($data);
        }

        $stream = Streams::ofResource($data);
        
        if ($this->versioning_enabled === true ||
            $this->obj->getVersion() === 0 && $this->obj->getMaxVersion() === 0) {
            $this->obj->appendStream($stream, $this->obj->getTitle());
        } else {
            $this->obj->replaceWithStream($stream, $this->obj->getTitle());
        }
        
        $stream->close();
        
        return $this->getETag();
    }

    /**
     * @return string|resource
     */
    public function get()
    {
        if (!$this->repo_helper->checkAccess("read", $this->obj->getRefId())) {
            throw new Forbidden("Permission denied. No read access for this file");
        }
        
        if (($r_id = $this->obj->getResourceId()) &&
            ($identification = $this->resource_manager->find($r_id))) {
            return $this->resource_consumer->stream($identification)->getStream()->getContents();
        }
        
        throw new NotFound("File not found");
    }

    public function getName() : string
    {
        return ilFileUtils::getValidFilename($this->obj->getTitle());
    }

    public function getContentType() : ?string
    {
        return  $this->obj->getFileType();
    }

    public function getETag() : ?string
    {
        if ($this->getSize() > 0) {
            return '"' . sha1(
                $this->getSize() .
                $this->getName() .
                $this->obj->getCreateDate()
            ) . '"';
        }

        return null;
    }

    public function getSize() : int
    {
        try {
            return (int) $this->obj->getFileSize();
        } catch (Error $e) {
            return -1;
        }
    }

    public function setName($name)
    {
        if (!$this->repo_helper->checkAccess("write", $this->obj->getRefId())) {
            throw new Forbidden('Permission denied');
        }
        
        if ($this->isDAVableObjTitle($name) && $this->hasValidFileExtension($name)) {
            $this->obj->setTitle($name);
            $this->obj->update();
        } else {
            throw new ilWebDAVNotDavableException(ilWebDAVNotDavableException::OBJECT_TITLE_NOT_DAVABLE);
        }
    }
    
    public function delete()
    {
        $this->repo_helper->deleteObject($this->obj->getRefId());
    }
    
    public function getLastModified() : ?int
    {
        return ($this->obj === null) ? null : strtotime($this->obj->getLastUpdateDate());
    }
}
