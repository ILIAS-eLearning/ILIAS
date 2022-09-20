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

interface Storage
{
    /**
     * Store some metric in the storage.
     */
    public function store(string $key, Metric $metric): void;

    // Convenience methods to store the common types of metrics.

    public function storeConfigBool(string $key, bool $value, string $description = null): void;
    public function storeConfigCounter(string $key, int $value, string $description = null): void;
    public function storeConfigGauge(string $key, $value, string $description = null): void;
    public function storeConfigTimestamp(string $key, \DateTimeImmutable $value, string $description = null): void;
    public function storeConfigText(string $key, string $value, string $description = null): void;

    public function storeStableBool(string $key, bool $value, string $description = null): void;
    public function storeStableCounter(string $key, int $value, string $description = null): void;
    public function storeStableGauge(string $key, $value, string $description = null): void;
    public function storeStableTimestamp(string $key, \DateTimeImmutable $value, string $description = null): void;
    public function storeStableText(string $key, string $value, string $description = null): void;

    public function storeVolatileBool(string $key, bool $value, string $description = null): void;
    public function storeVolatileCounter(string $key, int $value, string $description = null): void;
    public function storeVolatileGauge(string $key, $value, string $description = null): void;
    public function storeVolatileTimestamp(string $key, \DateTimeImmutable $value, string $description = null): void;
    public function storeVolatileText(string $key, string $value, string $description = null): void;
}
