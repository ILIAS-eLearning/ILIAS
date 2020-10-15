<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Metrics;

/**
 * A metric is something we can measure about the system.
 *
 * To make metrics processable and understandable for the setup, we use a closed
 * sum type to represent them. So basically, this class will contain every kind
 * of metric that can exist and the types are not extendable.
 */
final class Metric
{
    /**
     * The stability of a metric tells how often we expect changes in the metric.
     */
    // Config metrics only change when some administrator explicitely changes
    // a configuration.
    const STABILITY_CONFIG = "config";
    // Stable metric only change occassionally when some change in the installation
    // happened, e.g. a config change or an update.
    const STABILITY_STABLE = "stable";
    // Volatile metrics may change at every time even unexpectedly.
    const STABILITY_VOLATILE = "volatile";
    // This should only be used for collections with mixed content.
    const STABILITY_MIXED = "mixed";

    /**
     * The type of the metric tells what to expect of the values.
     */
    // Simply a yes or a no.
    const TYPE_BOOL = "bool";
    // A number that always increases.
    const TYPE_COUNTER = "counter";
    // A numeric value to measure some quantity of the installation.
    const TYPE_GAUGE = "gauge";
    // A timestamp to inform about a certain event in the installation.
    const TYPE_TIMESTAMP = "timestamp";
    // Some textual information about the installation. Prefer using one of the
    // other types.
    const TYPE_TEXT = "text";
    // A collection of metrics that contains multiple named metrics.
    const TYPE_COLLECTION = "collection";

    /**
     * @var mixed one of STABILITY_*
     */
    protected $stability;

    /**
     * @var mixed one of TYPE_*
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var string|null
     */
    protected $description;

    public function __construct(
        string $stability,
        string $type,
        $value,
        string $description = null
    ) {
        $this->checkStability($stability, $type);
        $this->checkType($type);
        $this->checkValue($type, $value);

        $this->stability = $stability;
        $this->type = $type;
        $this->value = $value;
        $this->description = $description;
    }

    protected function checkStability($stability, $type)
    {
        if (
            $stability !== self::STABILITY_CONFIG
            && $stability !== self::STABILITY_STABLE
            && $stability !== self::STABILITY_VOLATILE
            && !($stability === self::STABILITY_MIXED && $type === self::TYPE_COLLECTION)
        ) {
            throw new \InvalidArgumentException(
                "Invalid stability for metric: $stability"
            );
        }
    }

    protected function checkType($type)
    {
        if (
            $type !== self::TYPE_BOOL
            && $type !== self::TYPE_COUNTER
            && $type !== self::TYPE_GAUGE
            && $type !== self::TYPE_TIMESTAMP
            && $type !== self::TYPE_TEXT
            && $type !== self::TYPE_COLLECTION
        ) {
            throw new \InvalidArgumentException(
                "Invalid type for metric: $type"
            );
        }
    }

    protected function checkValue($type, $value)
    {
        if (
            ($type === self::TYPE_BOOL && !is_bool($value))
            || ($type === self::TYPE_COUNTER && !is_int($value))
            || ($type === self::TYPE_GAUGE && !(is_int($value) || is_float($value)))
            || ($type === self::TYPE_TIMESTAMP && !($value instanceof \DateTimeImmutable))
            || ($type === self::TYPE_TEXT && !is_string($value))
            || ($type === self::TYPE_COLLECTION && !is_array($value))
        ) {
            throw new \InvalidArgumentException(
                "Invalid type " . gettype($value) . " for metric of type $type"
            );
        }

        if ($type === self::TYPE_COLLECTION) {
            foreach ($value as $v) {
                if (!($v instanceof Metric)) {
                    throw new \InvalidArgumentException(
                        "Every element of a collection needs to be a metric, found " . gettype($v)
                    );
                }
            }
        }
    }

    public function getStability() : string
    {
        return $this->stability;
    }

    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function toYAML(int $indentation = 0) : string
    {
        $value = $this->getValue();
        switch ($this->getType()) {
            case self::TYPE_BOOL:
                if ($value) {
                    return "true";
                } else {
                    return "false";
                }
                // no break
            case self::TYPE_COUNTER:
                return "$value";
            case self::TYPE_GAUGE:
                if (is_int($value)) {
                    return "$value";
                }
                return sprintf("%.03f", $value);
            case self::TYPE_TIMESTAMP:
                return $value->format(\DateTimeInterface::ISO8601);
            case self::TYPE_TEXT:
                if (substr_count($value, "\n") > 0) {
                    return ">" . str_replace("\n", "\n" . $this->getIndentation($indentation), "\n$value");
                }
                return $value;
            case self::TYPE_COLLECTION:
                return implode(
                    "\n",
                    array_map(
                        function ($k, $v) use ($indentation) {
                            if ($v->getType() === self::TYPE_COLLECTION) {
                                $split = "\n";
                            } else {
                                $split = " ";
                            }
                            return $this->getIndentation($indentation) . "$k:$split" . $v->toYAML($indentation + 1);
                        },
                        array_keys($value),
                        array_values($value)
                    )
                );
            default:
                throw new \LogicException("Unknown type: " . $this->getType());
        }
    }

    protected function getIndentation(int $indentation = 0)
    {
        $res = "";
        while ($indentation--) {
            $res .= "    ";
        }
        return $res;
    }

    /**
     * The extracted part will be the first entry of the array, the second will be
     * the rest of the metrics.
     *
     * @return (Metric|null)[]
     */
    public function extractByStability(string $stability) : array
    {
        if ($stability === self::STABILITY_MIXED) {
            throw new \LogicException("Can not extract by mixed.");
        }

        if ($this->getStability() === $stability) {
            return [$this, null];
        }
        if ($this->getType() !== self::TYPE_COLLECTION) {
            return [null, $this];
        }

        // Now, this is a mixed collection. We need to go down.
        $values = $this->getValue();
        $extracted = [];
        $rest = [];
        foreach ($values as $k => $v) {
            list($e, $r) = $v->extractByStability($stability);
            if ($e !== null) {
                $extracted[$k] = $e;
            }
            if ($r !== null) {
                $rest[$k] = $r;
            }
        }

        if (count($extracted)) {
            $extracted = new Metric(
                $stability,
                self::TYPE_COLLECTION,
                $extracted,
                $this->getDescription()
            );
        } else {
            $extracted = null;
        }

        if (count($rest)) {
            $rest = new Metric(
                $this->getStability(),
                self::TYPE_COLLECTION,
                $rest,
                $this->getDescription()
            );
        } else {
            $rest = null;
        }

        return [$extracted, $rest];
    }
}
