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
use Sabre\DAV\INode;
use Sabre\DAV\ICollection;
use Psr\Http\Message\RequestInterface;

/**
 * @author Raphael Heer <raphael.heer@hslu.ch>
 */
class ilDAVContainer implements ICollection
{
    use ilWebDAVCheckValidTitleTrait;
    use ilWebDAVAccessChildrenFunctionsTrait;
    use ilWebDAVCommonINodeFunctionsTrait;
    use ilObjFileSecureString;

    public function __construct(
        protected ilObject $obj,
        protected ilObjUser $current_user,
        protected RequestInterface $request,
        protected ilWebDAVObjFactory $dav_factory,
        protected ilWebDAVRepositoryHelper $repository_helper
    ) {
    }

    public function getName(): string
    {
        return $this->obj->getTitle();
    }

    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\ICollection::getChild()
     */
    public function getChild($name): INode
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
    public function getChildren(): array
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
    public function childExists($name): bool
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
    public function setName($name): void
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
    public function createFile($name, $data = null): ?string
    {
        if (!$this->repository_helper->checkCreateAccessForType($this->obj->getRefId(), 'file')) {
            throw new Forbidden('Permission denied');
        }

        $size = 0;
        if ($this->request->hasHeader("Content-Length")) {
            $size = (int) $this->request->getHeader("Content-Length")[0];
        }
        if ($size === 0 && $this->request->hasHeader('X-Expected-Entity-Length')) {
            $size = (int) $this->request->getHeader('X-Expected-Entity-Length')[0];
        }
        if ($size > ilFileUtils::getPhpUploadSizeLimitInBytes()) {
            throw new Forbidden('File is too big');
        }

        $name = $this->ensureSuffix($name, $this->extractSuffixFromFilename($name));

        if ($this->childExists($name)) {
            $file_dav = $this->getChild($name);
        } else {
            try {
                $file_obj = new ilObjFile();
                $file_obj->setTitle($name);

                $file_dav = $this->dav_factory->createDAVObject($file_obj, $this->obj->getRefId());
            } catch (ilWebDAVNotDavableException) {
                throw new Forbidden('Forbidden characters in title');
            }
        }

        try {
            return $file_dav->put($data, $name);
        } catch (Forbidden $f) {
            throw $f;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\ICollection::createDirectory()
     */
    public function createDirectory($name): void
    {
        $new_obj = $this->getChildCollection();

        if (!$this->repository_helper->checkCreateAccessForType($this->obj->getRefId(), $new_obj->getType())) {
            throw new Forbidden('Permission denied');
        }

        try {
            $new_obj->setOwner($this->current_user->getId());
            $new_obj->setTitle($name);
            $this->dav_factory->createDAVObject($new_obj, $this->obj->getRefId());
        } catch (ilWebDAVNotDavableException) {
            throw new Forbidden('Forbidden characters in title');
        }
    }

    public function delete(): void
    {
        $this->repository_helper->deleteObject($this->obj->getRefId());
    }

    public function getLastModified(): ?int
    {
        return $this->retrieveLastModifiedAsIntFromObjectLastUpdateString($this->obj->getLastUpdateDate());
    }

    protected function getChildCollection(): ilContainer
    {
        if ($this->obj::class === 'ilObjCategory') {
            return new ilObjCategory();
        }

        return new ilObjFolder();
    }
}
