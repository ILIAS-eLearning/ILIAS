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

use ILIAS\UI\Factory;
use ILIAS\UI\Component\Panel\Report;

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
    public const STABILITY_CONFIG = "config";
    // Stable metric only change occassionally when some change in the installation
    // happened, e.g. a config change or an update.
    public const STABILITY_STABLE = "stable";
    // Volatile metrics may change at every time even unexpectedly.
    public const STABILITY_VOLATILE = "volatile";
    // This should only be used for collections with mixed content.
    public const STABILITY_MIXED = "mixed";

    /**
     * The type of the metric tells what to expect of the values.
     */
    // Simply a yes or a no.
    public const TYPE_BOOL = "bool";
    // A number that always increases.
    public const TYPE_COUNTER = "counter";
    // A numeric value to measure some quantity of the installation.
    public const TYPE_GAUGE = "gauge";
    // A timestamp to inform about a certain event in the installation.
    public const TYPE_TIMESTAMP = "timestamp";
    // Some textual information about the installation. Prefer using one of the
    // other types.
    public const TYPE_TEXT = "text";
    // A collection of metrics that contains multiple named metrics.
    public const TYPE_COLLECTION = "collection";

    /**
     * @var string one of STABILITY_*
     */
    protected string $stability;

    /**
     * @var string one of TYPE_*
     */
    protected string $type;

    /**
     * @var mixed
     */
    protected $value;


    protected ?string $description;

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

    protected function checkStability(string $stability, string $type): void
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

    protected function checkType($type): void
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

    protected function checkValue($type, $value): void
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

    public function getStability(): string
    {
        return $this->stability;
    }

    public function getType(): string
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function toYAML(int $indentation = 0): string
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
                        function (string $k, Metric $v) use ($indentation): string {
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

    public function toArray(int $indentation = 0)
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
                return (string) $value;
            case self::TYPE_GAUGE:
                if (is_int($value)) {
                    return (string) $value;
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
                $result = [];
                foreach ($value as $key => $val) {
                    $result[$key] = $val->toArray($indentation + 1);
                }
                return $result;
            default:
                throw new \LogicException("Unknown type: " . $this->getType());
        }
    }

    protected function getIndentation(int $indentation = 0): string
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
    public function extractByStability(string $stability): array
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

        if ($extracted !== []) {
            $extracted = new Metric(
                $stability,
                self::TYPE_COLLECTION,
                $extracted,
                $this->getDescription()
            );
        } else {
            $extracted = null;
        }

        if ($rest !== []) {
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

    public function toUIReport(Factory $f, string $name): Report
    {
        $yaml = $this->toYAML();
        $sub = $f->panel()->sub("", $f->legacy("<pre>" . $yaml . "</pre>"));
        return $f->panel()->report($name, [$sub]);
    }
}
