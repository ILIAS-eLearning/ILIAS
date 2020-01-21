<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class provides mathematical functions for statistics.
* It works on an array of numeric values.
*
* @author Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
*/

    /**
    * Constants for the handling of elements which are not a number
    */
    define("NAN_HANDLING_REMOVE", 0);
    define("NAN_HANDLING_ZERO", 1);

class ilStatistics
{
    /**
    * Handling of values which are no a number (NAN)
    *
    * If set to NAN_HANDLING_REMOVE, all elements which are not a number will be removed,
    * if set to NAN_HANDLING_ZERO, all elements which are not a number will be set to zero.
    *
    * @var integer
    */
    public $nan_handling;
    
    /**
    * Array containing the data
    *
    * @var array
    */
    
    public $stat_data;
    
    /**
    * Constructor of ilStatistics class
    *
    * @access public
    */
    public function __construct($nan_handling = NAN_HANDLING_REMOVE)
    {
        $this->nan_handling = $nan_handling;
        $this->stat_data = array();
    }
    
    /**
    * Set the handling of elements which are not a number
    *
    * If set to NAN_HANDLING_REMOVE, all elements which are not a number will be removed,
    * if set to NAN_HANDLING_ZERO, all elements which are not a number will be set to zero.
    *
    * @param integer $nan_handling A constant defining the handling of elements which are not a number
    * @access public
    */
    public function setNANHandling($nan_handling = NAN_HANDLING_REMOVE)
    {
        $this->nan_handling = $nan_handling;
    }
    
    /**
    * Get the handling of elements which are not a number
    *
    * Returns NAN_HANDLING_REMOVE if all elements which are not a number will be removed.
    * Returns NAN_HANDLING_ZERO if all elements which are not a number will be set to zero.
    *
    * @return integer A constant defining the handling of elements which are not a number
    * @access public
    */
    public function getNANHandling()
    {
        return $this->nan_handling;
    }
    
    /**
    * Sets the data and checks for invalid values
    *
    * @param array $stat_data An array containing the numeric data
    * @access public
    */
    public function setData($stat_data)
    {
        $this->stat_data = array_values($stat_data);
        $this->validate();
    }
    
    /**
    * Returns the numeric value array containing the data
    *
    * @return array An array containing the sorted numeric data
    * @access public
    */
    public function getData()
    {
        return $this->stat_data;
    }

    /**
    * Calculates the minimum value
    *
    * @return mixed The minimum value or false, if no minimum exists
    * @see max()
    * @access  public
    */
    public function min()
    {
        if (count($this->stat_data)) {
            $min = min($this->stat_data);
        } else {
            $min = false;
        }
        return $min;
    }

    /**
    * Calculates the maximum value
    *
    * @return mixed The maximum value or false, if no maximum exists
    * @see min()
    * @access  public
    */
    public function max()
    {
        if (count($this->stat_data)) {
            $max = max($this->stat_data);
        } else {
            $max = false;
        }
        return $max;
    }

    /**
    * Calculates number of data values
    *
    * @return mixed The number of data values
    * @access  public
    */
    public function count()
    {
        return count($this->stat_data);
    }

    /**
    * Calculates the sum of x_1^n + x_2^n + ... + x_i^n
    *
    * @param numeric $n The exponent
    * @return mixed The sum of x_1^n + x_2^n + ... + x_i^n or false, if no values exist
    * @access  public
    */
    public function sum_n($n)
    {
        $sum_n = false;
        if (count($this->stat_data)) {
            $sum_n = 0;
            foreach ($this->stat_data as $value) {
                $sum_n += pow((double) $value, (double) $n);
            }
        }
        return $sum_n;
    }

    /**
    * Calculates the sum of x_1 + x_2 + ... + x_i
    *
    * @return mixed The sum of x_1 + x_2 + ... + x_i or false, if no values exist
    * @access  public
    */
    public function sum()
    {
        return $this->sum_n(1);
    }


    /**
    * Calculates the sum of x_1^2 + x_2^2 + ... + x_i^2
    *
    * @return mixed The sum of x_1^2 + x_2^2 + ... + x_i^2 or false, if no values exist
    * @access  public
    */
    public function sum2()
    {
        return $this->sum_n(2);
    }
    
    /**
    * Calculates the product of x_1^n * x_2^n * ... * x_i^n
    *
    * @param numeric $n The exponent
    * @return mixed The product of x_1^n * x_2^n * ... * x_i^n or false, if no values exist
    * @access  public
    */
    public function product_n($n)
    {
        $prod_n = false;
        if (count($this->stat_data)) {
            if ($this->min() === 0) {
                return 0.0;
            }
            $prod_n = 1.0;
            foreach ($this->stat_data as $value) {
                $prod_n *= pow((double) $value, (double) $n);
            }
        }
        return $prod_n;
    }

    /**
    * Calculates the product of x_1 * x_2 * ... * x_i
    *
    * @param numeric $n The exponent
    * @return mixed The product of x_1 * x_2 * ... * x_i or false, if no values exist
    * @access  public
    */
    public function product($n)
    {
        return $this->product_n(1);
    }

    /**
    * Arithmetic mean of the data values
    * xbar = (1/n)*∑x_i
    *
    * @return mixed The arithmetic mean or false, if there is an error or no values
    * @access  public
    */
    public function arithmetic_mean()
    {
        $sum = $this->sum();
        if ($sum === false) {
            return false;
        }
        $count = $this->count();
        if ($count == 0) {
            return false;
        }
        return (double) ($sum/$count);
    }

    /**
    * Geometric mean of the data values
    * geometric_mean = (x_1 * x_2 * ... * x_n)^(1/n)
    *
    * The geometric mean of a set of positive data is defined as the product of all
    * the members of the set, raised to a power equal to the reciprocal of the number
    * of members.
    *
    * @return mixed The geometric mean or false, if there is an error or no values
    * @access  public
    */
    public function geometric_mean()
    {
        $prod = $this->product();
        if (($prod === false) or ($prod === 0)) {
            return false;
        }
        $count = $this->count();
        if ($count == 0) {
            return false;
        }
        return pow((double) $prod, (double) (1/$count));
    }

    /**
    * Harmonic mean of the data values
    * harmonic_mean = n/(1/x_1 + 1/x_2 + ... + 1/x_n)
    *
    * @return mixed The harmonic mean or false, if there is an error or no values
    * @access  public
    */
    public function harmonic_mean()
    {
        $min = $this->min();
        if (($min === false) or ($min === 0)) {
            return false;
        }
        $count = $this->count();
        if ($count == 0) {
            return false;
        }
        $sum = 0;
        foreach ($this->stat_data as $value) {
            $sum += 1/$value;
        }
        return $count/$sum;
    }

    /**
    * Median of the data values
    *
    * @return mixed The median or false, if there are no data values
    * @access  public
    */
    public function median()
    {
        $median = false;
        if (count($this->stat_data)) {
            $median = 0;
            $count = $this->count();
            if ((count($this->stat_data) % 2) == 0) {
                $median = ($this->stat_data[($count / 2) - 1] + $this->stat_data[($count / 2)]) / 2;
            } else {
                $median = $this->stat_data[(($count + 1) / 2) - 1];
            }
        }
        return $median;
    }
    
    /**
    * Returns the rank of a given value
    *
    * @return mixed The rank, if the value exists in the data, otherwise false
    * @access  public
    */
    public function rank($value)
    {
        if (!is_numeric($value)) {
            return false;
        }
        $rank = array_search($value, $this->stat_data);
        if ($rank !== false) {
            $rank = $this->count() - $rank;
        }
        return $rank;
    }
    
    /**
    * Returns the rank of the median
    *
    * This method is different from the rank method because the median could
    * be the arithmetic mean of the two middle values when the data size is even.
    * In this case the median could a value which is not part of the data set.
    *
    * @return mixed The rank of the median, otherwise false
    * @access  public
    */
    public function rank_median()
    {
        $count = $this->count();
        if ($count == 0) {
            return false;
        }
        
        if (($count % 2) == 0) {
            $rank_median = ($count + 1) / 2;
        } else {
            $rank_median = ($count + 1) / 2;
        }
        return $rank_median;
    }
    
    /**
    * n-Quantile of the data values
    *
    * @param double $n A value between 0 an 100 calculating the n-Quantile
    * @return mixed The n-quantile or false, if there are no data values
    * @access  public
    */
    public function quantile($n)
    {
        $count = $this->count();
        if ($count == 0) {
            return false;
        }
        $nprod = ($n/100)*$count;
        if (intval($nprod) == $nprod) {
            $k = $nprod;
            if ($k == 0) {
                return $this->stat_data[$k];
            } elseif ($k == $count) {
                return $this->stat_data[$k-1];
            } else {
                return ($this->stat_data[$k-1] + $this->stat_data[$k])/2;
            }
        } else {
            $k = ceil($nprod);
            return $this->stat_data[$k-1];
        }
    }
    
    /**
    * Validates the numeric data and handles values which are not a number
    * according to the $nan_handling variable. After validation the data
    * is sorted.
    *
    * @return boolean Returns true on success, otherwise false
    * @access private
    */
    public function validate()
    {
        $result = true;
        foreach ($this->stat_data as $key => $value) {
            if (!is_numeric($value)) {
                switch ($this->nan_handling) {
                    case NAN_HANDLING_REMOVE:
                        unset($this->stat_data[$key]);
                        break;
                    case NAN_HANDLING_ZERO:
                        $this->stat_data[$key] = 0;
                        break;
                    default:
                        $result = false;
                }
            }
        }
        sort($this->stat_data);
        return $result;
    }
}
