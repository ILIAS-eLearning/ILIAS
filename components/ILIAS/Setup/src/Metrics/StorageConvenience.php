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

use ILIAS\Setup\Metrics\Metric as M;
use ILIAS\Setup\Metrics\MetricType as MT;
use ILIAS\Setup\Metrics\MetricStability as MS;

/**
 * Implements the convenience methods of Storage over Storage::store
 */
trait StorageConvenience
{
    abstract public function store(string $key, M $metric): void;

    public function storeConfigBool(string $key, callable $config_bool_callable, string $description = null): void
    {
        if (!is_bool($config_bool_callable())) {
            throw new \InvalidArgumentException('Bool callable must return a boolean');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::BOOL, $config_bool_callable, $description)
        );
    }

    public function storeConfigCounter(string $key, callable $config_counter_callable, string $description = null): void
    {
        if (!is_int($config_counter_callable())) {
            throw new \InvalidArgumentException('Counter callable must return an integer');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::COUNTER, $config_counter_callable, $description)
        );
    }

    public function storeConfigGauge(string $key, callable $config_gauge_callable, string $description = null): void
    {
        if (!is_int($config_gauge_callable()) && !is_double($config_gauge_callable)) {
            throw new \InvalidArgumentException('Gauge callable must return an integer or double');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::GAUGE, $config_gauge_callable, $description)
        );
    }

    public function storeConfigTimestamp(string $key, callable $config_timestamp_callable, string $description = null): void
    {
        if (get_class($config_timestamp_callable()) != \DateTimeImmutable::class) {
            throw new \InvalidArgumentException('Timestamp callable must return a DateTimeImmutable');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::TIMESTAMP, $config_timestamp_callable, $description)
        );
    }

    public function storeConfigText(string $key, callable $config_text_callable, string $description = null): void
    {
        if (!is_string($config_text_callable())) {
            throw new \InvalidArgumentException('String callable must return a string');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::TEXT, $config_text_callable, $description)
        );
    }


    public function storeStableBool(string $key, callable $stable_bool_callable, string $description = null): void
    {
        if (!is_bool($stable_bool_callable())) {
            throw new \InvalidArgumentException('Bool callable must return a boolean');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::BOOL, $stable_bool_callable, $description)
        );
    }

    public function storeStableCounter(string $key, callable $stable_counter_callable, string $description = null): void
    {
        if (!is_int($stable_counter_callable())) {
            throw new \InvalidArgumentException('Counter callable must return an integer');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::COUNTER, $stable_counter_callable, $description)
        );
    }

    public function storeStableGauge(string $key, callable $stable_gauge_callable, string $description = null): void
    {
        if (!is_int($stable_gauge_callable()) && !is_double($stable_gauge_callable)) {
            throw new \InvalidArgumentException('Gauge callable must return an integer or double');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::GAUGE, $stable_gauge_callable, $description)
        );
    }

    public function storeStableTimestamp(string $key, callable $stable_timestamp_callable, string $description = null): void
    {
        if (get_class($stable_timestamp_callable()) != \DateTimeImmutable::class) {
            throw new \InvalidArgumentException('Timestamp callable must return a DateTimeImmutable');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::TIMESTAMP, $stable_timestamp_callable, $description)
        );
    }

    public function storeStableText(string $key, callable $stable_text_callable, string $description = null): void
    {
        if (!is_string($stable_text_callable())) {
            throw new \InvalidArgumentException('String callable must return a string');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::TEXT, $stable_text_callable, $description)
        );
    }


    public function storeVolatileBool(string $key, callable $volatile_bool_callable, string $description = null): void
    {
        if (!is_bool($volatile_bool_callable())) {
            throw new \InvalidArgumentException('Bool callable must return a boolean');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::BOOL, $volatile_bool_callable, $description)
        );
    }

    public function storeVolatileCounter(string $key, callable $volatile_counter_callable, string $description = null): void
    {
        if (!is_int($volatile_counter_callable())) {
            throw new \InvalidArgumentException('Counter callable must return an integer');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::COUNTER, $volatile_counter_callable, $description)
        );
    }

    public function storeVolatileGauge(string $key, callable $volatile_gauge_callable, string $description = null): void
    {
        if (!is_int($volatile_gauge_callable()) && !is_double($volatile_gauge_callable)) {
            throw new \InvalidArgumentException('Gauge callable must return an integer or double');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::GAUGE, $volatile_gauge_callable, $description)
        );
    }

    public function storeVolatileTimestamp(string $key, callable $volatile_timestamp_callable, string $description = null): void
    {
        if (get_class($volatile_timestamp_callable()) != \DateTimeImmutable::class) {
            throw new \InvalidArgumentException('Timestamp callable must return a DateTimeImmutable');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::TIMESTAMP, $volatile_timestamp_callable, $description)
        );
    }

    public function storeVolatileText(string $key, callable $volatile_text_callable, string $description = null): void
    {
        if (!is_string($volatile_text_callable())) {
            throw new \InvalidArgumentException('String callable must return a string');
        }
        $this->store(
            $key,
            new M(MS::CONFIG, MT::TEXT, $volatile_text_callable, $description)
        );
    }
}
