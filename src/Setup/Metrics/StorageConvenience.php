<?php declare(strict_types=1);

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Metrics;

use ILIAS\Setup\Metrics\Metric as M;

/**
 * Implements the convenience methods of Storage over Storage::store
 */
trait StorageConvenience
{
    abstract public function store(string $key, M $metric) : void;

    public function storeConfigBool(string $key, bool $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_BOOL, $value, $description)
        );
    }

    public function storeConfigCounter(string $key, int $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_COUNTER, $value, $description)
        );
    }

    public function storeConfigGauge(string $key, $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_GAUGE, $value, $description)
        );
    }

    public function storeConfigTimestamp(string $key, \DateTimeImmutable $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_TIMESTAMP, $value, $description)
        );
    }

    public function storeConfigText(string $key, string $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_TEXT, $value, $description)
        );
    }


    public function storeStableBool(string $key, bool $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_BOOL, $value, $description)
        );
    }

    public function storeStableCounter(string $key, int $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_COUNTER, $value, $description)
        );
    }

    public function storeStableGauge(string $key, $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_GAUGE, $value, $description)
        );
    }

    public function storeStableTimestamp(string $key, \DateTimeImmutable $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_TIMESTAMP, $value, $description)
        );
    }

    public function storeStableText(string $key, string $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_TEXT, $value, $description)
        );
    }


    public function storeVolatileBool(string $key, bool $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_BOOL, $value, $description)
        );
    }

    public function storeVolatileCounter(string $key, int $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_COUNTER, $value, $description)
        );
    }

    public function storeVolatileGauge(string $key, $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_GAUGE, $value, $description)
        );
    }

    public function storeVolatileTimestamp(string $key, \DateTimeImmutable $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_TIMESTAMP, $value, $description)
        );
    }

    public function storeVolatileText(string $key, string $value, string $description = null) : void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_TEXT, $value, $description)
        );
    }
}
