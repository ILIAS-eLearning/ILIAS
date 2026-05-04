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

use ILIAS\WebDAV\Entity\ProblemInfoFile;
use ILIAS\WebDAV\Config;
use ILIAS\WebDAV\Entity\Container;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\WebDAV\Entity\Entity;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\WebDAV\Objects\Filter\Filter;
use ILIAS\WebDAV\Objects\Filter\Action;
use Sabre\DAV\Exception\Forbidden;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class TreeProxyRepository implements ProxyRepository
{
    private array $storage = [];
    private ?\ilTree $tree = null;
    private ?Manager $manager = null;
    private ?\ilObjFileInfoRepository $file_info_repository = null;
    private ResourceStakeholder $stakeholder;

    public function __construct(
        private Config $config,
        private Filter $filter
    ) {
        global $DIC; // TODO remove Service Locator
        $this->stakeholder = new \ilObjFileStakeholder();
    }

    private function tree(): \ilTree
    {
        return $this->tree ?? $this->tree = $GLOBALS['DIC']->repositoryTree();
    }

    private function manager(): Manager
    {
        global $DIC;
        return $this->manager ?? $this->manager = $DIC->resourceStorage()->manage();
    }

    private function info(): \ilObjFileInfoRepository
    {
        return $this->file_info_repository ?? $this->file_info_repository = new \ilObjFileInfoRepository();
    }

    public function createObject(Type $type, Container $parent, string $name): ?Proxy
    {
        if (!$this->filter->checkName($name)) {
            return null;
        }

        if (!$this->filter->canUserIn(Action::WRITE, $parent)) {
            return null;
        }

        $object = match ($type) {
            Type::COURSE => new \ilObjCourse(),
            Type::GROUP => new \ilObjGroup(),
            Type::FOLDER => new \ilObjFolder(),
            Type::CATEGORY => new \ilObjCategory(),
            Type::FILE => new \ilObjFile(),
            default => null,
        };

        if (!$this->filter->canUserCreate($type, $parent)) {
            return null;
        }

        if ($object === null) {
            throw new \InvalidArgumentException(
                "Cannot create object of type {$type->name}"
            );
        }

        $object->setTitle($name);

        if ($type === Type::FILE) {
            $rid = $this->manager()->stream(
                Streams::ofString('-'),
                $this->stakeholder,
                '_Empty'
            );

            $object->setResourceId($rid->serialize());
            $this->manager()->unpublish(
                $rid,
            );
        }

        $object->create();
        $object->createReference();
        $object->putInTree($parent->getObjectProxy()->getRefId());
        $object->setPermissions($parent->getObjectProxy()->getRefId());

        $proxy = $this->getByRefId($object->getRefId(), $type);
        if ($proxy === null || !$this->filter->filterProxy($proxy)) {
            return null;
        }
        return $proxy;
    }

    public function createFile(Container $parent, string $name): ?FileProxy
    {
        if (!$this->filter->checkName($name)) {
            return null;
        }
        if (($proxy = $this->get($name, $parent, true)) !== null && $proxy->getType() === Type::FILE) {
            $ref_id = $parent->getObjectProxy()->getRefId();
            if ($this->tree()->isDeleted($ref_id)) {
                // TODO remove this "beauty". But: WebDAV at least on macOS Finder created a file, deleted it and cretes a
                // new one. we pick up the first from trash if possible to prevent shadow objects.

                \ilRepUtil::restoreObjects($ref_id, [$proxy->getRefId()]);
            }

            return $proxy;
        }

        return $this->createObject(
            Type::FILE,
            $parent,
            $name,
        );
    }

    public function createContainer(Container $parent, string $name): ?Proxy
    {
        $parent_type = $parent->getObjectProxy()?->getType() ?? Type::UNKNOWN;
        $new_type = match ($parent_type) {
            Type::COURSE, Type::GROUP, Type::FOLDER => Type::FOLDER,
            Type::CATEGORY => Type::CATEGORY,
            default => Type::UNKNOWN,
        };

        if (Type::UNKNOWN === $new_type) {
            return null;
        }

        return $this->createObject(
            $new_type,
            $parent,
            $name,
        );
    }

    public function get(
        string $path,
        ?Container $parent = null,
        bool $with_recently_deleted = false,
    ): ?Proxy {
        if (!$this->filter->checkName($path)) {
            return null;
        }

        if ($path === '') {
            return null;
        }
        if ($parent !== null) {
            foreach ($this->in($parent, $with_recently_deleted) as $proxy) {
                if (!$this->filter->filterProxy($proxy)) {
                    continue;
                }

                if ($proxy->getName() === $path) {
                    return $proxy;
                }
            }
            return null;
        }

        if (!str_starts_with($path, $this->config->getRefIdPrefix())) {
            return null;
        }
        $ref_id = (int) substr($path, strlen($this->config->getRefIdPrefix()));
        if ($ref_id < 1) {
            return null;
        }

        $proxy = $this->getByRefId($ref_id);
        if ($proxy === null || !$this->filter->filterProxy($proxy)) {
            return null;
        }
        return $proxy;
    }

    protected function getByNodeData(array $node_data, ?Type $type = null): ?Proxy
    {
        $ref_id = $node_data['ref_id'] ?? 0;
        if ($ref_id < 1) {
            return null;
        }

        if (isset($this->storage[$ref_id])) {
            return $this->storage[$ref_id];
        }

        $type ??= Type::tryFrom($node_data['type'] ?? '') ?? Type::UNKNOWN;
        $infos = null;
        $stream_resolver = null;

        // Check Title for Compatibility
        $title = $node_data['title'] ?? '$';
        if (!$this->filter->checkName($title)) {
            return null;
        }

        if ($type === Type::FILE) {
            // aggregate data from IRSS
            $infos = $this->info()->getByRefId((int) $node_data['ref_id']);
            $stream_resolver = new IRSSStreamHandler($infos?->getRID());

            $file_tree_proxy = new FileTreeProxy(
                $ref_id,
                $node_data['obj_id'] ?? 0,
                $title,
                strtotime($node_data['last_update'] ?? '') ?: 0,
                $infos?->getMimeType(),
                (int) $infos?->getFileSize()->inBytes(),
                $stream_resolver
            );
            if (!$this->filter->filterProxy($file_tree_proxy)) {
                return null;
            }

            return $this->storage[$ref_id] = $file_tree_proxy;
        }

        $tree_proxy = new TreeProxy(
            $ref_id,
            $node_data['obj_id'] ?? 0,
            $title,
            strtotime($node_data['last_update'] ?? '') ?: 0,
            $type
        );

        if (!$this->filter->filterProxy($tree_proxy)) {
            return null;
        }

        return $this->storage[$ref_id] = $tree_proxy;
    }

    protected function getByRefId(int $by_ref_id, ?Type $type = null): ?Proxy
    {
        return $this->getByNodeData(
            $this->tree()->getNodeData($by_ref_id),
            $type
        );
    }

    /**
     * Inspect raw children of a container and report titles that cannot be
     * exposed via WebDAV. Used to populate the virtual ProblemInfoFile.
     *
     * @return array{
     *     duplicates: list<string>,
     *     forbidden: list<string>,
     *     info_name_collision: bool
     * }
     */
    public function analyseProblems(Container $container): array
    {
        $ref_id = $container->getObjectProxy()?->getRefId();
        $duplicates = [];
        $forbidden = [];
        $info_name_collision = false;
        if ($ref_id === null) {
            return [
                'duplicates' => $duplicates,
                'forbidden' => $forbidden,
                'info_name_collision' => $info_name_collision,
            ];
        }

        $object_types = $this->config->getSupportedObjectTypes();
        $seen = [];
        foreach ($this->tree()->getChildsByTypeFilter($ref_id, $object_types) as $item) {
            $title = (string) ($item['title'] ?? '');
            if ($title === '') {
                continue;
            }
            if ($title === ProblemInfoFile::FILE_NAME) {
                $info_name_collision = true;
                continue;
            }
            if (!$this->filter->checkName($title)) {
                $forbidden[] = $title;
                continue;
            }
            if (isset($seen[$title])) {
                $duplicates[] = $title;
                continue;
            }
            $seen[$title] = true;
        }

        return [
            'duplicates' => $duplicates,
            'forbidden' => $forbidden,
            'info_name_collision' => $info_name_collision,
        ];
    }

    public function in(Container $container, bool $with_recently_deleted = false): \Generator|Proxy
    {
        $ref_id = $container->getObjectProxy()?->getRefId();
        if ($ref_id === null) {
            return;
        }

        $object_types = $this->config->getSupportedObjectTypes();

        foreach (
            $this->tree()->getChildsByTypeFilter($ref_id, $object_types) as $item
        ) {
            // check title for compatibility
            if (!$this->filter->checkName($item['title'] ?? '$')) {
                continue;
            }

            $proxy = $this->getByNodeData($item);
            if ($proxy === null) {
                continue;
            }
            if (!$this->filter->filterProxy($proxy)) {
                continue;
            }
            yield $proxy;
        }

        // check for deleted objects
        if ($with_recently_deleted) {
            foreach ($this->tree()->getSavedNodeData($ref_id) as $saved_node) {
                if (Type::tryFrom($saved_node['type'] ?? '') !== Type::FILE) {
                    continue;
                }

                $deleted = new \DateTimeImmutable($saved_node['deleted'] ?? 'now');
                // check if deleted in the last 30 seconds
                $threshold = (new \DateTimeImmutable())->modify(
                    "-{$this->config->getDeletedObjectsRetentionPeriod()} seconds"
                );
                if ($deleted < $threshold) {
                    continue;
                }

                $proxy = $this->getByNodeData($saved_node);
                if ($proxy === null) {
                    continue;
                }
                if (!$this->filter->filterProxy($proxy)) {
                    continue;
                }
                yield $proxy;
            }
        }
    }

    public function rename(Entity $entity): bool
    {
        $ref_id = $entity->getObjectProxy()?->getRefId();
        if ($ref_id === null) {
            return false;
        }
        if (!$this->filter->checkName($entity->getName())) {
            return false;
        }

        if (!$this->filter->canUserFor(Action::WRITE, $entity)) {
            return false;
        }

        $object = \ilObjectFactory::getInstanceByRefId($ref_id);
        $object->setTitle($entity->getName());
        $object->update();

        return true;
    }

    public function delete(Entity $entity): bool
    {
        $ref_id = $entity->getObjectProxy()?->getRefId();
        if ($ref_id === null) {
            return false;
        }

        if (!$this->filter->canUserFor(Action::DELETE, $entity)) {
            return false;
        }

        global $DIC;
        $DIC->repository()->internal()->domain()->deletion()->deleteObjectsByRefIds([$ref_id]);

        return true;
    }

}
