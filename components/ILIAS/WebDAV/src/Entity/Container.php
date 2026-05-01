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

namespace ILIAS\WebDAV\Entity;

use Sabre\DAV\ICollection;
use Sabre\DAV\INode;
use ILIAS\WebDAV\Objects\Proxy;
use Sabre\DAV\Exception\NotFound;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Container extends BaseContainer implements ICollection
{
    public function __construct(
        Factory $factory,
        string $path,
        ?Proxy $object_proxy = null,
        protected ?Container $parent = null
    ) {
        parent::__construct($factory, $path, $object_proxy);
    }

    public function getParent(): ?Container
    {
        return $this->parent;
    }

    public function getChild($name): INode
    {
        if ($name === ProblemInfoFile::FILE_NAME) {
            $info_file = $this->factory->getProblemInfoFile($this);
            if ($info_file->hasProblems()) {
                return $info_file;
            }
            throw new NotFound($name);
        }
        return $this->factory->get($name, $this) ?? throw new NotFound($name);
    }

    #[\Override]
    public function getFullPath(): string
    {
        $path = $this->getName();
        $parent = $this->getParent();
        while ($parent !== null) {
            $path = $parent->getName() . '/' . $path;
            $parent = $parent->getParent();
            if ($parent?->getParent() === null) {
                break;
            }
        }
        return $path;
    }

}
