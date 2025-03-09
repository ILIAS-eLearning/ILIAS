<?php

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

declare(strict_types=1);

use Sabre\DAV\Exception\Forbidden;
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
    use ilObjFileSecureString;
    use ilObjFileNews;
    use ilWebDAVCheckValidTitleTrait;
    use ilWebDAVCommonINodeFunctionsTrait;

    protected Manager $resource_manager;
    protected Consumers $resource_consumer;

    protected bool $needs_size_check = true;

    protected ?int $temporary_size = null;

    public function __construct(
        protected ilObjFile $obj,
        protected ilWebDAVRepositoryHelper $repo_helper,
        Services $resource_storage,
        protected RequestInterface $request,
        protected ilWebDAVObjFactory $dav_factory,
        protected bool $versioning_enabled
    ) {
        $this->resource_manager = $resource_storage->manage();
        $this->resource_consumer = $resource_storage->consume();
    }

    protected function clearLocks(): void
    {
        $this->repo_helper->locks()->purgeExpiredLocksFromDB();
        $lock = $this->repo_helper->locks()->getLockObjectWithObjIdFromDB($this->obj->getId());
        if ($lock !== null) {
            $this->repo_helper->locks()->removeLockWithTokenFromDB($lock->getToken());
        }
    }

    /**
     * @param string|resource $data
     */
    public function put($data, ?string $name = null): ?string
    {
        if (!$this->repo_helper->checkAccess('write', $this->obj->getRefId())) {
            throw new Forbidden("Permission denied. No write access for this file");
        }
        if ($name === null) {
            $name = $this->getName();
        }
        $size = 0;
        $name ??= $this->getName();
        $name = $this->ensureSuffix($name, $this->extractSuffixFromFilename($name));

        $stream = is_resource($data) ? Streams::ofResource($data) : Streams::ofString($data);
        $stream_size = $stream->getSize();

        if ($this->request->hasHeader("Content-Length")) {
            $size = (int) $this->request->getHeader("Content-Length")[0];
        }
        if ($size === 0 && $this->request->hasHeader('X-Expected-Entity-Length')) {
            $size = (int) $this->request->getHeader('X-Expected-Entity-Length')[0];
        }

        if ($size > ilFileUtils::getPhpUploadSizeLimitInBytes()) {
             $this->delete();
            throw new Forbidden('File is too big');
        }

        if ($this->needs_size_check && $this->getSize() === 0) {
            $parent_ref_id = $this->repo_helper->getParentOfRefId($this->obj->getRefId());
            $file_dav = $this->dav_factory->createDAVObject($this->obj, $parent_ref_id);
            $file_dav->noSizeCheckNeeded();

            return $file_dav->put($data);
        }

        $resource = $stream->detach();
        if ($resource === null) {
            return null;
        }
        $stream = Streams::ofResource($resource);

        $version = $this->obj->getVersion(true);
        if ($this->versioning_enabled || $version === 0) {
            // stream may be a temp-file (due to chunked upload). we must impoort it directly
            $uri = $stream->getMetadata('uri');
            if ($uri === 'php://temp') {
                $version = $this->obj->appendStream($stream, $name);
            } elseif (($stream_content = (string) $stream) !== '') { // this may be a problem with large files
                $version = $this->obj->appendStream(
                    Streams::ofString($stream_content),
                    $name
                );
            }
        } else {
            $version = $this->obj->replaceWithStream($stream, $name);
        }

        $stream->close();
        $this->clearLocks();

        if ($version > 0) {
            // $this->obj->publish();
            return $this->getETag();
        }
        return null;
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
            return $this->resource_consumer->stream($identification)->getStream()->detach();
        }
        return '';
    }

    public function getName(): string
    {
        $title = $this->obj->getTitle();
        $suffix = empty($this->obj->getFileExtension())
            ? $this->extractSuffixFromFilename($title)
            : $this->obj->getFileExtension();

        $return_title = $this->ensureSuffix(
            $title,
            $suffix
        );

        return $return_title;
    }

    public function getContentType(): ?string
    {
        return $this->obj->getFileType();
    }

    public function getETag(): ?string
    {
        if ($this->getSize() > 0) {
            return '"'
                . sha1(
                    (string) $this->getSize() .
                    $this->getName() .
                    $this->obj->getCreateDate()
                )
                . '"';
        }

        return null;
    }

    public function getSize(): int
    {
        try {
            return $this->obj->getFileSize();
        } catch (Throwable) {
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

        if ($this->isDAVableObjTitle($name) &&
            $name === $this->obj->checkFileExtension($this->getName(), $name)) {
            $this->obj->setTitle($this->ensureSuffix($name, $this->extractSuffixFromFilename($name)));
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
