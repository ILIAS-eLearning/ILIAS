<?php declare(strict_types=1);

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
 ********************************************************************
 */

namespace ILIAS\Data\Chart;

use ILIAS\Data\Dimension;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class Dataset
{
    protected array $dimensions = [];
    protected array $points = [];
    protected array $tooltips = [];

    /**
     * @param array<string, Dimension\Dimension> $dimensions Dimensions with their names as keys
     */
    public function __construct(array $dimensions)
    {
        foreach ($dimensions as $name => $dimension) {
            if (!is_string($name)) {
                throw new \InvalidArgumentException(
                    "Expected array key to be a string, '$name' is given."
                );
            }
            if (!$dimension instanceof Dimension\Dimension) {
                throw new \InvalidArgumentException(
                    "Expected array value to be an instance of Dimension, '$dimension' is given."
                );
            }
        }
        $this->dimensions = $dimensions;
    }

    public function getDimensions() : array
    {
        return $this->dimensions;
    }

    /**
     * @param string $messeaurement_item_label
     * @param array<string, int|float|int[]|float[]> $values key: Dimension name, value: point or range as int or float
     * @return $this
     */
    public function withPoints(string $messeaurement_item_label, array $values) : self
    {
        if (array_diff_key($values, $this->getDimensions())
            || array_diff_key($this->getDimensions(), $values)
        ) {
            throw new \ArgumentCountError(
                "The number of the passed values does not match with the number of Dimensions."
            );
        }

        foreach ($values as $dimension_name => $value) {
            $dimension = $this->getDimensions()[$dimension_name];

            switch (true) {
                case $dimension instanceof Dimension\OrdinalDimension:
                    if (!is_null($value) && !is_numeric($value)) {
                        throw new \InvalidArgumentException(
                            "Expected parameter to be numeric or null. '$value' is given."
                        );
                    }
                    break;
                case $dimension instanceof Dimension\RangeDimension:
                    if (is_null($value)) {
                        break;
                    }
                    if (!is_array($value) || count($value) !== 2) {
                        throw new \InvalidArgumentException(
                            "Expected parameter to be null or an array with two parameters of int or float.
                            '$value' is given."
                        );
                    } else {
                        foreach ($value as $number) {
                            if (!is_numeric($number)) {
                                throw new \InvalidArgumentException(
                                    "Expected parameters in array to be numeric. '$number' is given."
                                );
                            }
                        }
                    }
                    break;
                default:
                    throw new \InvalidArgumentException(
                        "Expected parameter to be an existing Dimension. '$dimension' is given."
                    );
            }
        }

        $clone = clone $this;
        $clone->points[$messeaurement_item_label] = $values;

        return $clone;
    }

    public function getPoints() : array
    {
        return $this->points;
    }

    public function getPointsPerDimension() : array
    {
        $points_restructured = [];
        foreach ($this->getPoints() as $messeaurement_item_label => $points_for_dimensions) {
            foreach ($points_for_dimensions as $dimension_name => $point) {
                $points_restructured[$dimension_name][$messeaurement_item_label] = $point;
            }
        }

        return $points_restructured;
    }

    /**
     * @param string $messeaurement_item_label
     * @param array<string, string> $values key: Dimension name, value: tooltip as string value or null
     * @return $this
     */
    public function withToolTips(string $messeaurement_item_label, array $values) : self
    {
        if (array_diff_key($values, $this->getDimensions())
            || array_diff_key($this->getDimensions(), $values)
        ) {
            throw new \ArgumentCountError(
                "The number of the passed values does not match with the number of Dimensions."
            );
        }

        foreach ($values as $dimension_name => $value) {
            if (!is_string($value) && !is_null($value)) {
                throw new \InvalidArgumentException(
                    "Expected array key to be a string, '$value' is given."
                );
            }
        }

        $clone = clone $this;
        $clone->tooltips[$messeaurement_item_label] = $values;

        return $clone;
    }

    public function getToolTips() : array
    {
        return $this->tooltips;
    }

    public function getTooltipsPerDimension() : array
    {
        $tooltips_restructured = [];
        foreach ($this->getToolTips() as $messeaurement_item_label => $tooltips_for_dimensions) {
            foreach ($tooltips_for_dimensions as $dimension_name => $tooltip) {
                $tooltips_restructured[$dimension_name][$messeaurement_item_label] = $tooltip;
            }
        }

        return $tooltips_restructured;
    }

    /**
     * Returns an empty Dataset clone
     */
    public function withResetDataSet() : self
    {
        $clone = clone $this;
        $clone->points = [];
        $clone->tooltips = [];
        return $clone;
    }

    public function getMinValue() : ?float
    {
        if (empty($this->getPoints())) {
            return null;
        }

        $min = 0;
        foreach ($this->getPoints() as $points_for_dimensions) {
            foreach ($points_for_dimensions as $point) {
                if (is_array($point)) {
                    foreach ($point as $p) {
                        if ($min > $p) {
                            $min = $p;
                        }
                    }
                } elseif (!is_null($point) && $min > $point) {
                    $min = $point;
                }
            }
        }

        return (float) $min;
    }

    public function getMaxValue() : ?float
    {
        if (empty($this->getPoints())) {
            return null;
        }

        $max = 0;
        foreach ($this->getPoints() as $points_for_dimensions) {
            foreach ($points_for_dimensions as $point) {
                if (is_array($point)) {
                    foreach ($point as $p) {
                        if ($max < $p) {
                            $max = $p;
                        }
                    }
                } elseif (!is_null($point) && $max < $point) {
                    $max = $point;
                }
            }
        }

        return (float) $max;
    }
}
