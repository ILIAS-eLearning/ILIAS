<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Metrics;

class StorageOnPathWrapper implements Storage
{
    use StorageConvenience;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var Storage
     */
    protected $other;

    public function __construct(string $path, Storage $other)
    {
        $this->path = $path;
        $this->other = $other;
    }

    /**
     * @inheritdocs
     */
    public function store(string $key, Metric $metric) : void
    {
        $this->other->store("{$this->path}.$key", $metric);
    }
}
