<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente;

/**
 * Adds a caching wrapper around another repository that only calls
 * the underlying repos providersForEntity once per entity. The cached
 * providers are wrapped in CachedProviders to enable caching for
 * components as well.
 */
class CachedRepository implements Repository {
    use RepositoryHelper;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var array<mixed,Provider[]>
     */
    protected $cache;

    public function __construct(Repository $repository) {
        $this->repository = $repository;
        $this->cache = [];
    }

    /**
     * @inheritdocs
     */
    public function providersForEntity(Entity $entity, $component_type = null) {
        $id = $entity->id();
        if (!isset($this->cache[$id])) {
            $this->cache[$id] = array_map(function(Provider $p) {
                return new CachedProvider($p);
            }, $this->repository->providersForEntity($entity));
        }

        if ($component_type === null) {
            return $this->cache[$id];
        }

        $providers = [];
        foreach ($this->cache[$id] as $provider) {
            if (in_array($component_type, $provider->componentTypes())) {
                $providers[] = $provider;
            }
        }
        return $providers;
    }
}
