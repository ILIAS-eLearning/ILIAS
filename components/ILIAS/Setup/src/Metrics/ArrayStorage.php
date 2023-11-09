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

class ArrayStorage implements Storage
{
    use StorageConvenience;

    /**
     * @var array<string, Metric>
     */
    protected array $metrics;

    public function __construct()
    {
        $this->metrics = [];
    }

    /**
     * @inheritdocs
     */
    public function store(string $key, Metric $metric): void
    {
        $path = explode(".", $key);
        $this->metrics = $this->doStore($this->metrics, $path, $metric);
    }

    /**
     * Recursive implementation of storing.
     */
    protected function doStore(array $base, array $path, $metric): array
    {
        $key = array_shift($path);
        if (count($path) == 0) {
            $base[$key] = $metric;
            return $base;
        }

        $base[$key] = $this->doStore($base[$key] ?? [], $path, $metric);
        return $base;
    }

    public function get(): array
    {
        return $this->metrics;
    }

    public function asMetric(): Metric
    {
        return $this->doAsMetric($this->metrics);
    }

    protected function doAsMetric(array $cur): Metric
    {
        return new Metric(
            Metric::STABILITY_MIXED,
            Metric::TYPE_COLLECTION,
            array_map(
                function ($v) {
                    if (is_array($v)) {
                        return $this->doAsMetric($v);
                    }
                    return $v;
                },
                $cur
            )
        );
    }
}
