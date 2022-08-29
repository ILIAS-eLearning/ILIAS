<?php

declare(strict_types=1);

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
use Sabre\DAV\Exception\NotFound;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Services;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Consumer\Consumers;
use Sabre\DAV\IFile;
use Psr\Http\Message\RequestInterface;

/**
 * @author Raphael Heer <raphael.heer@hslu.ch>
 */
class ilDAVFile implements IFile
{
    use ilObjFileNews;
    use ilWebDAVCheckValidTitleTrait;
    use ilWebDAVCommonINodeFunctionsTrait;

    protected ilObjFile $obj;
    protected ilWebDAVRepositoryHelper $repo_helper;
    protected Manager $resource_manager;
    protected Consumers $resource_consumer;
    protected RequestInterface $request;
    protected ilWebDAVObjFactory $dav_factory;

    protected bool $needs_size_check = true;
    protected bool $versioning_enabled;

    public function __construct(
        ilObjFile $obj,
        ilWebDAVRepositoryHelper $repo_helper,
        Services $resource_storage,
        RequestInterface $request,
        ilWebDAVObjFactory $dav_factory,
        bool $versioning_enabled
    ) {
        $this->obj = $obj;
        $this->repo_helper = $repo_helper;
        $this->resource_manager = $resource_storage->manage();
        $this->resource_consumer = $resource_storage->consume();
        $this->request = $request;
        $this->dav_factory = $dav_factory;
        $this->versioning_enabled = $versioning_enabled;
    }

    /**
     * @param string|resource $data
     */
    public function put($data): ?string
    {
        if (!$this->repo_helper->checkAccess('write', $this->obj->getRefId())) {
            throw new Forbidden("Permission denied. No write access for this file");
        }

        $size = 0;

        if ($this->request->hasHeader("Content-Length")) {
            $size = (int) $this->request->getHeader("Content-Length")[0];
        }
        if ($size === 0 && $this->request->hasHeader('X-Expected-Entity-Length')) {
            $size = (int) $this->request->getHeader('X-Expected-Entity-Length')[0];
        }

        if ($size > ilFileUtils::getUploadSizeLimitBytes()) {
            throw new Forbidden('File is too big');
        }

        if ($this->needs_size_check && $this->getSize() === 0) {
            $parent_ref_id = $this->repo_helper->getParentOfRefId($this->obj->getRefId());
            $obj_id = $this->obj->getId();
            $this->repo_helper->deleteObject($this->obj->getRefId());
            $file_obj = new ilObjFile();
            $file_obj->setTitle($this->getName());
            $file_obj->setFileName($this->getName());

            $file_dav = $this->dav_factory->createDAVObject($file_obj, $parent_ref_id);
            $file_dav->noSizeCheckNeeded();
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

    public function getName(): string
    {
        return ilFileUtils::getValidFilename($this->obj->getTitle());
    }

    public function getContentType(): ?string
    {
        return  $this->obj->getFileType();
    }

    public function getETag(): ?string
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

    public function getSize(): int
    {
        try {
            return $this->obj->getFileSize();
        } catch (Error $e) {
            return -1;
        }
    }

    public function noSizeCheckNeeded(): void
    {
        $this->needs_size_check = false;
    }

    public function setName($name): void
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

    public function delete(): void
    {
        $this->repo_helper->deleteObject($this->obj->getRefId());
    }

    public function getLastModified(): ?int
    {
        return $this->retrieveLastModifiedAsIntFromObjectLastUpdateString($this->obj->getLastUpdateDate());
    }
}
