<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Metrics;

class ArrayStorage implements Storage
{
    use StorageConvenience;

    /**
     * @var array<string, Metric>
     */
    protected $metrics;

    public function __construct()
    {
        $this->metrics = [];
    }

    /**
     * @inheritdocs
     */
    public function store(string $key, Metric $metric) : void
    {
        $path = explode(".", $key);
        $this->metrics = $this->doStore($this->metrics, $path, $metric);
    }

    /**
     * Recursive implementation of storing.
     */
    protected function doStore(array $base, array $path, $metric) : array
    {
        $key = array_shift($path);
        if (count($path) == 0) {
            $base[$key] = $metric;
            return $base;
        }

        $base[$key] = $this->doStore($base[$key] ?? [], $path, $metric);
        return $base;
    }

    public function get() : array
    {
        return $this->metrics;
    }

    public function asMetric() : Metric
    {
        return $this->doAsMetric($this->metrics);
    }

    protected function doAsMetric(array $cur) : Metric
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
