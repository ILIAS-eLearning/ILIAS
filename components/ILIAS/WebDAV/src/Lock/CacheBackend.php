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

namespace ILIAS\WebDAV\Lock;

use ILIAS\Cache\Container\Container;
use Sabre\DAV\Locks\Backend\AbstractBackend;
use ILIAS\WebDAV\Entity\Factory;
use ILIAS\Cache\Container\Request;
use ILIAS\WebDAV\Objects\Type;
use Sabre\DAV\Locks\LockInfo;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CacheBackend extends AbstractBackend implements Request
{
    private Container $cache;

    private Transformation $raw;

    public function __construct(
        private Factory $entity_factory
    ) {
        global $DIC;
        $this->cache = $DIC->globalCache()->get($this);
        $this->cache->flush();
        $this->raw = new class () implements Transformation {
            use DeriveApplyToFromTransform;
            use DeriveInvokeFromTransform;

            public function transform($from)
            {
                return $from;
            }

        };
    }

    public function getContainerKey(): string
    {
        return 'webdav:locks';
    }

    public function isForced(): bool
    {
        return true;
    }

    private function key(string $uri): string
    {
        return 'locks:' . md5($uri);
    }

    public function getLocks($uri, $returnChildLocks): array
    {
        $key = $this->key($uri);
        if ($key === null) {
            return [];
        }

        if (!$this->cache->has($key)) {
            return [];
        }

        $locks = [];
        foreach ($this->cache->get($key, $this->raw) as $item) {
            $locks[] = $this->asLockInfo($item);
        }

        return $locks;
    }

    private function asArray(LockInfo $lockInfo): array
    {
        return [
            'owner' => $lockInfo->owner,
            'token' => $lockInfo->token,
            'scope' => $lockInfo->scope,
            'depth' => $lockInfo->depth,
            'timeout' => $lockInfo->timeout,
            'uri' => $lockInfo->uri,
            'created' => $lockInfo->created,
        ];
    }

    private function asLockInfo(array $item): LockInfo
    {
        $lock = new LockInfo();
        $lock->owner = $item['owner'];
        $lock->token = $item['token'];
        $lock->scope = $item['scope'];
        $lock->depth = $item['depth'];
        $lock->timeout = $item['timeout'];
        $lock->uri = $item['uri'];
        $lock->created = $item['created'];

        return $lock;
    }

    public function lock($uri, LockInfo $lockInfo): bool
    {
        $key = $this->key($uri);

        $locks = $this->cache->get($key, $this->raw) ?? [];

        $lock = new LockInfo();
        $lock->owner = $lockInfo->owner;
        $lock->token = $lockInfo->token;
        $lock->scope = LockInfo::EXCLUSIVE;
        $lock->depth = $lockInfo->depth;
        $lock->timeout = $lockInfo->timeout;
        $lock->uri = $uri;
        $lock->created = $lockInfo->created ?? time();

        $locks[] = $this->asArray($lock);

        $this->cache->set($key, $locks);

        return true;
    }

    public function unlock($uri, LockInfo $lockInfo): bool
    {
        $key = $this->key($uri);
        if ($key === null) {
            return false;
        }
        $this->cache->set($key, []);

        $entity = $this->entity_factory->getByFullPath($uri);
        $proxy = $entity?->getObjectProxy();
        if ($proxy !== null && $proxy->getType() === Type::FILE) {
            $proxy->getStreamHandler()->publish();
        }

        return true;
    }
}
