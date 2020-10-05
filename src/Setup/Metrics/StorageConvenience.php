<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Metrics;

use ILIAS\Setup\Metrics\Metric as M;

/**
 * Implements the convenience methods of Storage over Storage::store
 */
trait StorageConvenience
{
    abstract public function store(string $key, M $metric) : void;

    public function storeConfigBool($key, bool $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_BOOL, $value, $description)
        );
    }

    public function storeConfigCounter($key, int $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_COUNTER, $value, $description)
        );
    }

    public function storeConfigGauge($key, $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_GAUGE, $value, $description)
        );
    }

    public function storeConfigTimestamp($key, \DateTimeImmutable $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_TIMESTAMP, $value, $description)
        );
    }

    public function storeConfigText($key, string $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_TEXT, $value, $description)
        );
    }


    public function storeStableBool($key, bool $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_BOOL, $value, $description)
        );
    }

    public function storeStableCounter($key, int $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_COUNTER, $value, $description)
        );
    }

    public function storeStableGauge($key, $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_GAUGE, $value, $description)
        );
    }

    public function storeStableTimestamp($key, \DateTimeImmutable $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_TIMESTAMP, $value, $description)
        );
    }

    public function storeStableText($key, string $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_TEXT, $value, $description)
        );
    }


    public function storeVolatileBool($key, bool $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_BOOL, $value, $description)
        );
    }

    public function storeVolatileCounter($key, int $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_COUNTER, $value, $description)
        );
    }

    public function storeVolatileGauge($key, $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_GAUGE, $value, $description)
        );
    }

    public function storeVolatileTimestamp($key, \DateTimeImmutable $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_TIMESTAMP, $value, $description)
        );
    }

    public function storeVolatileText($key, string $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_TEXT, $value, $description)
        );
    }
}
