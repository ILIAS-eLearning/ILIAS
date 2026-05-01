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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class BaseContainer extends BaseEntity implements ICollection, Entity
{
    public function createFile($name, $data = null): ?string
    {
        return $this->factory->createFile($this, $name, $data);
    }

    public function createDirectory($name): string
    {
        return $this->factory->createContainer($this, $name);
    }

    abstract public function getChild($name): INode;

    public function getChildren(): array
    {
        return $this->factory->getChildren($this);
    }

    public function childExists($name): bool
    {
        return $this->factory->has($this, $name);
    }

    public function getFullPath(): string
    {
        return '';
    }

}
