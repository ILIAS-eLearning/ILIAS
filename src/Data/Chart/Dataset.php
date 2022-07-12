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
    protected array $alternative_information = [];

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

    protected function checkDimensionCongruenceForValues(array $values) : void
    {
        if (array_diff_key($values, $this->getDimensions())
            || array_diff_key($this->getDimensions(), $values)
        ) {
            throw new \ArgumentCountError(
                "The number of the passed values does not match with the number of Dimensions."
            );
        }
    }

    /**
     * @param string $measurement_item_label Using the identical label multiple times will overwrite the values
     * @param array<string, mixed> $values key: Dimension name, value: mixed (validity depends on the dimension)
     * @return $this
     */
    public function withPoint(string $measurement_item_label, array $values) : self
    {
        $this->checkDimensionCongruenceForValues($values);

        foreach ($values as $dimension_name => $value) {
            $dimension = $this->getDimensions()[$dimension_name];
            $dimension->checkValue($value);
        }

        $clone = clone $this;
        $clone->points[$measurement_item_label] = $values;

        return $clone;
    }

    public function getPoints() : array
    {
        return $this->points;
    }

    public function getPointsPerDimension() : array
    {
        $points_restructured = [];
        foreach ($this->getPoints() as $measurement_item_label => $points_for_dimensions) {
            foreach ($points_for_dimensions as $dimension_name => $point) {
                $points_restructured[$dimension_name][$measurement_item_label] = $point;
            }
        }

        return $points_restructured;
    }

    /**
     * @param string $measurement_item_label
     * @param array<string, string> $values key: Dimension name, value: Alternative text as string value or null
     * @return $this
     */
    public function withAlternativeInformation(string $measurement_item_label, array $values) : self
    {
        $this->checkDimensionCongruenceForValues($values);

        foreach ($values as $dimension_name => $value) {
            if (!is_string($value) && !is_null($value)) {
                throw new \InvalidArgumentException(
                    "Expected array value to be a string or null, '$value' is given."
                );
            }
        }

        $clone = clone $this;
        $clone->alternative_information[$measurement_item_label] = $values;

        return $clone;
    }

    public function getAlternativeInformation() : array
    {
        return $this->alternative_information;
    }

    public function getAlternativeInformationPerDimension() : array
    {
        $alternative_information_restructured = [];
        foreach ($this->getAlternativeInformation() as $measurement_item_label => $alternative_information_for_dimensions) {
            foreach ($alternative_information_for_dimensions as $dimension_name => $alternative_info) {
                $alternative_information_restructured[$dimension_name][$measurement_item_label] = $alternative_info;
            }
        }

        return $alternative_information_restructured;
    }

    /**
     * Returns an empty Dataset clone
     */
    public function withResetDataset() : self
    {
        $clone = clone $this;
        $clone->points = [];
        $clone->alternative_information = [];
        return $clone;
    }

    public function isEmpty() : bool
    {
        if (empty($this->getPoints())) {
            return true;
        }
        return false;
    }

    public function getMinValueForDimension(string $dimension_name) : float
    {
        if ($this->isEmpty()) {
            throw new \RuntimeException(
                "Dataset must not be empty."
            );
        }

        $min = null;
        $dimension_points = $this->getPointsPerDimension()[$dimension_name];
        foreach ($dimension_points as $point) {
            if (is_array($point)) {
                foreach ($point as $p) {
                    if (is_null($min) || $p < $min) {
                        $min = $p;
                    }
                }
            } elseif (is_null($min) || !is_null($point) && $point < $min) {
                $min = $point;
            }
        }

        return (float) $min;
    }

    public function getMaxValueForDimension(string $dimension_name) : float
    {
        if ($this->isEmpty()) {
            throw new \RuntimeException(
                "Dataset must not be empty."
            );
        }

        $max = null;
        $dimension_points = $this->getPointsPerDimension()[$dimension_name];
        foreach ($dimension_points as $point) {
            if (is_array($point)) {
                foreach ($point as $p) {
                    if (is_null($max) || $p > $max) {
                        $max = $p;
                    }
                }
            } elseif (is_null($max) || !is_null($point) && $point > $max) {
                $max = $point;
            }
        }

        return (float) $max;
    }
}
