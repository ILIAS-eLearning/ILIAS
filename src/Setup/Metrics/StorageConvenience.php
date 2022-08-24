<?php

declare(strict_types=1);

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

namespace ILIAS\Setup\Metrics;

use ILIAS\Setup\Metrics\Metric as M;

/**
 * Implements the convenience methods of Storage over Storage::store
 */
trait StorageConvenience
{
    abstract public function store(string $key, M $metric): void;

    public function storeConfigBool(string $key, bool $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_BOOL, $value, $description)
        );
    }

    public function storeConfigCounter(string $key, int $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_COUNTER, $value, $description)
        );
    }

    public function storeConfigGauge(string $key, $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_GAUGE, $value, $description)
        );
    }

    public function storeConfigTimestamp(string $key, \DateTimeImmutable $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_TIMESTAMP, $value, $description)
        );
    }

    public function storeConfigText(string $key, string $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_CONFIG, M::TYPE_TEXT, $value, $description)
        );
    }


    public function storeStableBool(string $key, bool $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_BOOL, $value, $description)
        );
    }

    public function storeStableCounter(string $key, int $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_COUNTER, $value, $description)
        );
    }

    public function storeStableGauge(string $key, $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_GAUGE, $value, $description)
        );
    }

    public function storeStableTimestamp(string $key, \DateTimeImmutable $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_TIMESTAMP, $value, $description)
        );
    }

    public function storeStableText(string $key, string $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_STABLE, M::TYPE_TEXT, $value, $description)
        );
    }


    public function storeVolatileBool(string $key, bool $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_BOOL, $value, $description)
        );
    }

    public function storeVolatileCounter(string $key, int $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_COUNTER, $value, $description)
        );
    }

    public function storeVolatileGauge(string $key, $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_GAUGE, $value, $description)
        );
    }

    public function storeVolatileTimestamp(string $key, \DateTimeImmutable $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_TIMESTAMP, $value, $description)
        );
    }

    public function storeVolatileText(string $key, string $value, string $description = null): void
    {
        $this->store(
            $key,
            new M(M::STABILITY_VOLATILE, M::TYPE_TEXT, $value, $description)
        );
    }
}
