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
use Sabre\DAV\Exception\NotFound;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class MountPoint extends Container implements ICollection
{
    #[\Override]
    public function getName(): string
    {
        return 'MountPoint';
    }

    #[\Override]
    public function getChild($name): INode
    {
        return $this->factory->get($name) ?? throw new NotFound($name);
    }

}
