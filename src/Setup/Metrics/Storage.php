<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Metrics;

interface Storage
{
    /**
     * Store some metric in the storage.
     */
    public function store(string $key, Metric $metric) : void;

    // Convenience methods to store the common types of metrics.

    public function storeConfigBool($key, bool $value, string $description = null) : void;
    public function storeConfigCounter($key, int $value, string $description = null) : void;
    public function storeConfigGauge($key, $value, string $description = null) : void;
    public function storeConfigTimestamp($key, \DateTimeImmutable $value, string $description = null) : void;
    public function storeConfigText($key, string $value, string $description = null) : void;

    public function storeStableBool($key, bool $value, string $description = null) : void;
    public function storeStableCounter($key, int $value, string $description = null) : void;
    public function storeStableGauge($key, $value, string $description = null) : void;
    public function storeStableTimestamp($key, \DateTimeImmutable $value, string $description = null) : void;
    public function storeStableText($key, string $value, string $description = null) : void;

    public function storeVolatileBool($key, bool $value, string $description = null) : void;
    public function storeVolatileCounter($key, int $value, string $description = null) : void;
    public function storeVolatileGauge($key, $value, string $description = null) : void;
    public function storeVolatileTimestamp($key, \DateTimeImmutable $value, string $description = null) : void;
    public function storeVolatileText($key, string $value, string $description = null) : void;
}
