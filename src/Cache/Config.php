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

namespace ILIAS\Cache;

use org\bovigo\vfs\Issue104TestCase;
use ILIAS\Cache\Nodes\NodeRepository;
use ILIAS\Cache\Nodes\NullNodeRepository;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Config
{
    public const ALL = '*';
    public const APCU = 'apc';
    public const PHPSTATIC = 'static';
    public const MEMCACHED = 'memcached';
    protected int $default_ttl = 300;

    public function __construct(
        protected string $adaptor_name,
        protected bool $activated = false,
        protected array $container_keys = [],
        protected ?NodeRepository $nodes = null
    ) {
        if ($this->nodes === null) {
            $this->nodes = new NullNodeRepository();
        }
    }

    public function getAdaptorName(): string
    {
        return $this->adaptor_name;
    }

    public function getNodes(): array
    {
        return $this->nodes->getNodes();
    }

    public function getActivatedContainerKeys(): array
    {
        return $this->container_keys;
    }

    public function getDefaultTTL(): int
    {
        return $this->default_ttl;
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }
}
