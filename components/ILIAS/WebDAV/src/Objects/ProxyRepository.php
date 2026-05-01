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

namespace ILIAS\WebDAV\Objects;

use ILIAS\WebDAV\Entity\Container;
use ILIAS\WebDAV\Entity\Entity;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface ProxyRepository
{
    public function createContainer(Container $parent, string $name): ?Proxy;

    public function get(string $path, ?Container $parent = null): ?Proxy;

    public function in(Container $container, bool $with_recently_deleted = false): \Generator|Proxy;

    public function delete(Entity $entity): bool;
}
