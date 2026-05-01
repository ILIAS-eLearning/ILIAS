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

use Sabre\DAV\INode;
use ILIAS\WebDAV\Objects\Proxy;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class BaseEntity implements INode, Entity
{
    public function __construct(
        protected Factory $factory,
        protected string $path,
        protected ?Proxy $object_proxy = null
    ) {
    }

    public function delete(): void
    {
        $this->factory->delete($this);
    }

    public function getName(): string
    {
        return $this->object_proxy?->getName() ?? '';
    }

    public function setName($name): string
    {
        $this->object_proxy?->setName($name);
        $this->factory->rename($this);
        return $name;
    }

    public function getLastModified(): ?int
    {
        return $this->object_proxy?->getLastModified();
    }

    public function getObjectProxy(): ?Proxy
    {
        return $this->object_proxy;
    }

    public function getPath(): string
    {
        return $this->path;
    }

}
