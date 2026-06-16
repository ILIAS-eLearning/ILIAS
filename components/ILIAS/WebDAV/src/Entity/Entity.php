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
interface Entity extends INode
{
    public function __construct(
        Factory $factory,
        string $path,
        ?Proxy $proxy = null,
    );

    public function getObjectProxy(): ?Proxy;

    public function getPath(): string;

}
