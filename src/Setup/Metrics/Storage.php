<?php declare(strict_types=1);

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Metrics;

interface Storage
{
    /**
     * Store some metric in the storage.
     */
    public function store(string $key, Metric $metric) : void;

    // Convenience methods to store the common types of metrics.

    public function storeConfigBool(string $key, bool $value, string $description = null) : void;
    public function storeConfigCounter(string $key, int $value, string $description = null) : void;
    public function storeConfigGauge(string $key, $value, string $description = null) : void;
    public function storeConfigTimestamp(string $key, \DateTimeImmutable $value, string $description = null) : void;
    public function storeConfigText(string $key, string $value, string $description = null) : void;

    public function storeStableBool(string $key, bool $value, string $description = null) : void;
    public function storeStableCounter(string $key, int $value, string $description = null) : void;
    public function storeStableGauge(string $key, $value, string $description = null) : void;
    public function storeStableTimestamp(string $key, \DateTimeImmutable $value, string $description = null) : void;
    public function storeStableText(string $key, string $value, string $description = null) : void;

    public function storeVolatileBool(string $key, bool $value, string $description = null) : void;
    public function storeVolatileCounter(string $key, int $value, string $description = null) : void;
    public function storeVolatileGauge(string $key, $value, string $description = null) : void;
    public function storeVolatileTimestamp(string $key, \DateTimeImmutable $value, string $description = null) : void;
    public function storeVolatileText(string $key, string $value, string $description = null) : void;
}
