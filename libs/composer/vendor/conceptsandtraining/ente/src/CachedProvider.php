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
 * A chaching wrapper around a provider that caches components per type
 * and passes through the other methods.
 */
class CachedProvider implements Provider {
    /**
     * @var Provider
     */
    protected $provider;

    /**
     * @var array<string,Component[]>
     */
    protected $cache;

    public function __construct(Provider $provider) {
        $this->provider = $provider;
        $this->cache = [];
    }

    /**
     * @inheritdocs
     */
    public function componentsOfType($component_type) {
        if (!isset($this->cache[$component_type])) {
            $this->cache[$component_type] = $this->provider->componentsOfType($component_type);
        }
        return $this->cache[$component_type];
    }

    /**
     * @inheritdocs
     */
    public function componentTypes() {
        return $this->provider->componentTypes();
    }

    /**
     * @inheritdocs
     */
    public function entity() {
        return $this->provider->entity();
    }
}
