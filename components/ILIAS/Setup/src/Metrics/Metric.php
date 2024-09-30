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
    protected $value = null;

    public function __construct(
        protected MetricStability $stability,
        protected MetricType $type,
        protected $value_producer,
        protected ?string $description = null
    ) {
        $this->checkStability($stability, $type);
        $this->checkType($type);
        if (!is_callable($value_producer)) {
            throw new \InvalidArgumentException(
                "Expected \$value_producer to be callable."
            );
        }
    }

    protected function checkStability(MetricStability $stability, MetricType $type): void
    {
        if (
            $stability !== MetricStability::CONFIG
            && $stability !== MetricStability::STABLE
            && $stability !== MetricStability::VOLATILE
            && !($stability === MetricStability::MIXED && $type === MetricType::COLLECTION)
        ) {
            throw new \InvalidArgumentException(
                "Invalid stability for metric: $stability->name"
            );
        }
    }

    protected function checkType(MetricType $type): void
    {
        if (
            $type !== MetricType::BOOL
            && $type !== MetricType::COUNTER
            && $type !== MetricType::GAUGE
            && $type !== MetricType::TIMESTAMP
            && $type !== MetricType::TEXT
            && $type !== MetricType::COLLECTION
        ) {
            throw new \InvalidArgumentException(
                "Invalid type for metric: $type->name"
            );
        }
    }

    protected function checkValue($type, $value): void
    {
        if (
            ($type === MetricType::BOOL && !is_bool($value))
            || ($type === MetricType::COUNTER && !is_int($value))
            || ($type === MetricType::GAUGE && !(is_int($value) || is_float($value)))
            || ($type === MetricType::TIMESTAMP && !($value instanceof \DateTimeImmutable))
            || ($type === MetricType::TEXT && !is_string($value))
            || ($type === MetricType::COLLECTION && !is_array($value))
        ) {
            throw new \InvalidArgumentException(
                "Invalid type " . gettype($value) . " for metric of type $type->name"
            );
        }

        if ($type === MetricType::COLLECTION) {
            foreach ($value as $v) {
                if (!($v instanceof Metric)) {
                    throw new \InvalidArgumentException(
                        "Every element of a collection needs to be a metric, found " . gettype($v)
                    );
                }
            }
        }
    }

    public function getStability(): MetricStability
    {
        return $this->stability;
    }

    public function getType(): MetricType
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if (!is_null($this->value)) {
            return $this->value;
        }

        $value = $this->value_producer->__invoke();
        $this->checkValue($this->getType(), $value);
        $this->value = $value;

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
            case MetricType::BOOL:
                if ($value) {
                    return "true";
                } else {
                    return "false";
                }
                // no break
            case MetricType::COUNTER:
                return "$value";
            case MetricType::GAUGE:
                if (is_int($value)) {
                    return "$value";
                }
                return sprintf("%.03f", $value);
            case MetricType::TIMESTAMP:
                return $value->format(\DateTimeInterface::ISO8601);
            case MetricType::TEXT:
                if (substr_count($value, "\n") > 0) {
                    return ">" . str_replace("\n", "\n" . $this->getIndentation($indentation), "\n$value");
                }
                return $value;
            case MetricType::COLLECTION:
                return implode(
                    "\n",
                    array_map(
                        function (string $k, Metric $v) use ($indentation): string {
                            if ($v->getType() === MetricType::COLLECTION) {
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
                throw new \LogicException("Unknown type: " . $this->getType()->name);
        }
    }

    public function toArray(int $indentation = 0)
    {
        $value = $this->getValue();

        switch ($this->getType()) {
            case MetricType::BOOL:
                if ($value) {
                    return "true";
                } else {
                    return "false";
                }
                // no break
            case MetricType::COUNTER:
                return (string) $value;
            case MetricType::GAUGE:
                if (is_int($value)) {
                    return (string) $value;
                }
                return sprintf("%.03f", $value);
            case MetricType::TIMESTAMP:
                return $value->format(\DateTimeInterface::ISO8601);
            case MetricType::TEXT:
                if (substr_count($value, "\n") > 0) {
                    return ">" . str_replace("\n", "\n" . $this->getIndentation($indentation), "\n$value");
                }
                return $value;
            case MetricType::COLLECTION:
                $result = [];
                foreach ($value as $key => $val) {
                    $result[$key] = $val->toArray($indentation + 1);
                }
                return $result;
            default:
                throw new \LogicException("Unknown type: " . $this->getType()->name);
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
    public function extractByStability(MetricStability $stability): array
    {
        if ($stability === MetricStability::MIXED) {
            throw new \LogicException("Can not extract by mixed.");
        }

        if ($this->getStability() === $stability) {
            return [$this, null];
        }
        if ($this->getType() !== MetricType::COLLECTION) {
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
                MetricType::COLLECTION,
                fn() => $extracted,
                $this->getDescription()
            );
        } else {
            $extracted = null;
        }

        if ($rest !== []) {
            $rest = new Metric(
                $this->getStability(),
                MetricType::COLLECTION,
                fn() => $rest,
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
