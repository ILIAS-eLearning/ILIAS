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

namespace ILIAS\Setup\Metrics;

interface Storage
{
    /**
     * Store some metric in the storage.
     */
    public function store(string $key, Metric $metric): void;

    // Convenience methods to store the common types of metrics.

    public function storeConfigBool(string $key, callable $config_bool_callable, string $description = null): void;
    public function storeConfigCounter(string $key, callable $config_counter_callable, string $description = null): void;
    public function storeConfigGauge(string $key, callable $config_gauge_callable, string $description = null): void;
    public function storeConfigTimestamp(string $key, callable $config_timestamp_callable, string $description = null): void;
    public function storeConfigText(string $key, callable $config_text_callable, string $description = null): void;

    public function storeStableBool(string $key, callable $stable_bool_callable, string $description = null): void;
    public function storeStableCounter(string $key, callable $stable_counter_callable, string $description = null): void;
    public function storeStableGauge(string $key, callable $stable_gauge_callable, string $description = null): void;
    public function storeStableTimestamp(string $key, callable $stable_timestamp_callable, string $description = null): void;
    public function storeStableText(string $key, callable $stable_text_callable, string $description = null): void;

    public function storeVolatileBool(string $key, callable $volatile_bool_callable, string $description = null): void;
    public function storeVolatileCounter(string $key, callable $volatile_counter_callable, string $description = null): void;
    public function storeVolatileGauge(string $key, callable $volatile_gauge_callable, string $description = null): void;
    public function storeVolatileTimestamp(string $key, callable $volatile_timestamp_callable, string $description = null): void;
    public function storeVolatileText(string $key, callable $volatile_text_callable, string $description = null): void;
}
