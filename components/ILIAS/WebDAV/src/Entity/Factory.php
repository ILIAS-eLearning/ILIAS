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

use ILIAS\WebDAV\Objects\FileProxy;
use Sabre\DAV\ICollection;
use ILIAS\WebDAV\Request\RequestTranslation;
use ILIAS\WebDAV\Objects\TreeProxyRepository;
use ILIAS\WebDAV\Objects\Type;
use ILIAS\WebDAV\Objects\Proxy;
use ILIAS\WebDAV\DataCheck;
use Sabre\DAV\Exception\Forbidden;
use ILIAS\WebDAV\Objects\NullProxy;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Factory
{
    use DataCheck;

    public function __construct(
        private RequestTranslation $request_translation,
        private TreeProxyRepository $proxy_generator,
    ) {
    }

    public function getMountPoint(): ?ICollection
    {
        $base_path = $this->request_translation->getRequestedPathAsArray()[0] ?? '';
        if (empty($base_path)) {
            return null;
        }

        return new MountPoint(
            $this,
            $base_path
        );
    }

    public function get(string $path, ?Container $parent = null): ?Entity
    {
        $proxy = $this->proxy_generator->get($path, $parent);
        if ($proxy === null || $proxy instanceof NullProxy) {
            return null;
        }
        return $this->buildEntity($proxy, $parent);
    }

    public function getProblemInfoFile(Container $container): ProblemInfoFile
    {
        global $DIC;
        $problems = $this->proxy_generator->analyseProblems($container);
        return new ProblemInfoFile(
            $problems['duplicates'],
            $problems['forbidden'],
            $problems['info_name_collision'],
            $DIC->language()
        );
    }

    private function buildEntity(Proxy $proxy, ?Container $container): Entity
    {
        if ($proxy->getType() === Type::FILE) {
            return new File(
                $this,
                $container?->getFullPath() . '/' . $proxy->getName(),
                $proxy,
                $container
            );
        }
        return new Container(
            $this,
            $container?->getFullPath() . '/' . $proxy->getName(),
            $proxy,
            $container
        );
    }

    public function createFile(Container $container, string $name, mixed $data = null): ?string
    {
        $proxy = $this->proxy_generator->createFile($container, $name);
        if (!$proxy instanceof FileProxy) {
            throw new Forbidden("Cannot create file '{$name}' in container '{$container->getName()}'");
        }
        $proxy->getStreamHandler()->put($name, $data, !$this->isEmpty($data));

        $entity = $this->get($name, $container);
        if (!$entity instanceof File) {
            throw new Forbidden("Created file '{$name}' could not be retrieved");
        }

        return $entity->getEtag();
    }

    public function createContainer(Container $container, string $name): string
    {
        $proxy = $this->proxy_generator->createContainer($container, $name);
        if ($proxy === null || $proxy instanceof NullProxy) {
            throw new Forbidden("Cannot create container '{$name}' in '{$container->getName()}'");
        }
        return $proxy->getName();
    }

    public function has(Container $container, string $name): bool
    {
        return $this->proxy_generator->get($name, $container) !== null;
    }

    public function rename(Entity $entity): void
    {
        if (!$this->proxy_generator->rename($entity)) {
            throw new Forbidden("Cannot rename '{$entity->getName()}'");
        }
    }

    public function delete(Entity $entity): void
    {
        if (!$this->proxy_generator->delete($entity)) {
            throw new Forbidden("Cannot delete '{$entity->getName()}'");
        }
    }

    public function getChildren(Container $container): array
    {
        $entities = [];
        foreach ($this->proxy_generator->in($container) as $proxy) {
            if ($proxy === null) {
                continue;
            }
            if ($proxy instanceof NullProxy) {
                continue;
            }
            $entities[] = $this->buildEntity($proxy, $container);
        }

        $info_file = $this->getProblemInfoFile($container);
        if ($info_file->hasProblems()) {
            $entities[] = $info_file;
        }

        return $entities;
    }

    public function getByFullPath(string $full_path): ?Entity
    {
        $parts = array_values(array_filter(explode('/', $full_path), static fn(string $p): bool => $p !== ''));
        if ($parts === []) {
            return null;
        }

        $entity = $this->get(array_shift($parts));
        foreach ($parts as $part) {
            if (!$entity instanceof Container) {
                return null;
            }
            $entity = $this->get($part, $entity);
        }

        return $entity;
    }
}
