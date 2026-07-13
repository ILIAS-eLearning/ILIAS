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

namespace ILIAS\WebDAV\Objects\Filter;

use ILIAS\WebDAV\Entity\Entity;
use ILIAS\WebDAV\Entity\Container;
use ILIAS\WebDAV\Objects\Proxy;
use ILIAS\WebDAV\Config;
use ILIAS\WebDAV\Objects\Type;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CharacterFilter implements Filter
{
    public function __construct(
        private Config $config
    ) {
    }

    public function canUserIn(Action $action, Container $in): bool
    {
        return true;
    }

    public function canUserFor(Action $action, Entity $for): bool
    {
        return true;
    }

    public function filterEntity(Entity $entity): bool
    {
        return $this->checkName($entity->getName());
    }

    public function filterProxy(Proxy $proxy): bool
    {
        return $this->checkName($proxy->getName());
    }

    public function checkName(string $name): bool
    {
        foreach ($this->config->getInvalidStartCharacters() as $character) {
            if (str_starts_with($name, (string) $character)) {
                return false;
            }
        }
        return !preg_match(
            '/[' . preg_quote(implode('', $this->config->getIncompatibleCharacters()), '/') . ']/',
            $name
        );
    }

    public function canUserCreate(Type $type, Container $in): bool
    {
        return true;
    }

}
