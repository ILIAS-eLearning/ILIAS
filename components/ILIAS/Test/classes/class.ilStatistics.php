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
 * ******************************************************************* */

declare(strict_types=1);

/**
 * Constants for the handling of elements which are not a number
 */


/**
* This class provides mathematical functions for statistics.
* It works on an array of numeric values.
*
* @author Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
 */
class ilStatistics
{
    public array $stat_data = [];

    public function setData(array $stat_data): void
    {
        $this->stat_data = array_values($stat_data);
        $this->removeNonNumericValues();
    }

    public function getData(): array
    {
        return $this->stat_data;
    }

    public function min(): ?float
    {
        if ($this->stat_data === []) {
            return null;
        }

        return min($this->stat_data);
    }

    /**
    * Calculates the maximum value
    */
    public function max(): ?float
    {
        if ($this->stat_data === []) {
            return null;
        }

        return max($this->stat_data);
    }

    /**
    * Calculates number of data values
    */
    public function count(): int
    {
        return count($this->stat_data);
    }

    /**
    * Calculates the sum of x_1^n + x_2^n + ... + x_i^n
    */
    public function sum_n(int $n): ?float
    {
        if ($this->stat_data === []) {
            return null;
        }

        $sum_n = 0;
        foreach ($this->stat_data as $value) {
            $sum_n += pow((float) $value, (float) $n);
        }
        return $sum_n;
    }

    /**
    * Calculates the sum of x_1 + x_2 + ... + x_i
    */
    public function sum(): ?float
    {
        return $this->sum_n(1);
    }


    /**
    * Calculates the sum of x_1^2 + x_2^2 + ... + x_i^2
    */
    public function sum2(): float
    {
        return $this->sum_n(2);
    }

    /**
    * Calculates the product of x_1^n * x_2^n * ... * x_i^n
    */
    public function product_n(int $n): ?float
    {
        if ($this->stat_data === []) {
            return null;
        }

        if ($this->min() === 0) {
            return 0.0;
        }

        $prod_n = 1.0;
        foreach ($this->stat_data as $value) {
            $prod_n *= pow((float) $value, (float) $n);
        }
        return $prod_n;
    }

    /**
    * Calculates the product of x_1 * x_2 * ... * x_i
    */
    public function product(int $n): ?float
    {
        return $this->product_n(1);
    }

    /**
    * Arithmetic mean of the data values
    * xbar = (1/n)*∑x_i
    */
    public function arithmetic_mean(): ?float
    {
        $sum = $this->sum();
        if ($sum === null) {
            return null;
        }

        $count = $this->count();
        if ($count === 0) {
            return null;
        }
        return (float) ($sum / $count);
    }

    /**
    * Geometric mean of the data values
    * geometric_mean = (x_1 * x_2 * ... * x_n)^(1/n)
    *
    * The geometric mean of a set of positive data is defined as the product of all
    * the members of the set, raised to a power equal to the reciprocal of the number
    * of members.
    */
    public function geometric_mean(): ?float
    {
        $prod = $this->product(1);
        if (($prod === null) || ($prod === 0)) {
            return null;
        }
        $count = $this->count();
        if ($count === 0) {
            return null;
        }
        return pow((float) $prod, (float) (1 / $count));
    }

    /**
    * Harmonic mean of the data values
    * harmonic_mean = n/(1/x_1 + 1/x_2 + ... + 1/x_n)
    */
    public function harmonic_mean(): ?float
    {
        $min = $this->min();
        if (($min === null) or ($min === 0)) {
            return null;
        }
        $count = $this->count();
        if ($count === 0) {
            return null;
        }
        $sum = 0.0;
        foreach ($this->stat_data as $value) {
            $sum += 1 / $value;
        }
        return $count / $sum;
    }

    /**
    * Median of the data values
    */
    public function median(): ?float
    {
        if ($this->stat_data === null) {
            return null;
        }

        $median = 0.0;
        $count = $this->count();
        if ((count($this->stat_data) % 2) === 0) {
            return ($this->stat_data[($count / 2) - 1] + $this->stat_data[($count / 2)]) / 2;
        }

        return $this->stat_data[(($count + 1) / 2) - 1];
    }

    /**
    * Returns the rank of a given value
    */
    public function rank(float $value): ?int
    {
        $rank = array_search($value, $this->stat_data);
        if ($rank === false) {
            return null;
        }

        return $this->count() - $rank;
    }

    /**
    * Returns the rank of the median
    *
    * This method is different from the rank method because the median could
    * be the arithmetic mean of the two middle values when the data size is even.
    * In this case the median could a value which is not part of the data set.
    */
    public function rank_median(): ?float
    {
        $count = $this->count();
        if ($count === 0) {
            return null;
        }

        if (($count % 2) === 0) {
            return $rank_median = ($count + 1) / 2;
        }

        return $rank_median = ($count + 1) / 2;
    }

    /**
    * n-Quantile of the data values
    */
    public function quantile(float $n): ?float
    {
        $count = $this->count();
        if ($count === 0) {
            return null;
        }

        $nprod = ($n / 100) * $count;
        if (intval($nprod) != $nprod) {
            return $this->stat_data[ceil($nprod) - 1];
        }

        if ($nprod === 0.0) {
            return $this->stat_data[0];
        }

        if ($nprod === (float) $count) {
            return $this->stat_data[(int) $nprod - 1];
        }

        return ($this->stat_data[(int) $nprod - 1] + $this->stat_data[(int) $nprod]) / 2;
    }

    private function removeNonNumericValues(): void
    {

        foreach ($this->stat_data as $key => $value) {
            if (!is_numeric($value)) {
                unset($this->stat_data[$key]);
                break;
            }
        }
        sort($this->stat_data);
    }
}
