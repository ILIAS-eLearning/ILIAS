<?php

class ArrayHandler implements Scalar
{
	/**
	 * @var array
	 */
	private $array;


	/**
	 * NumberHandler constructor.
	 *
	 * @param int $number
	 */
	public function __construct(array $array) {
		$this->array = $array;
	}

	public function isArray() {
		return true;
	}

	public function any()
	{
		$any = false;
		foreach($this->array as $val) {
			if($val) $any = true;
		}
		return $any;
	}
	public function all()
	{
		$all = true;
		foreach($this->array as $val) {
			if(!$val) $all = false;
		}
		return $all;
	}
	public function compact()
	{
		$array = $this->array;
		return array_filter($array);
	}
	public function chunk($size)
	{
		$this->verifyInteger($size, "chunk");
		return array_chunk($this->array, $size);
	}
	public function column($key)
	{
		$this->verifyString($key, "column");
		return array_column($this->array, $key);
	}
	public function combine($arrayVals)
	{
		$this->verifyArray($arrayVals, "combine");
		return array_combine($this->array,$arrayVals);
	}
	public function count() {
		return count($this->array);
	}
	public function countValues()
	{
		return array_count_values($this->array);
	}
	public function diff($array)
	{
		$this->verifyArray($array, "diff");
		return array_diff($this->array, $array);
	}
	public function difference($array)
	{
		$this->verifyArray($array, "diff");
		return array_diff($this->array, $array);
	}
	public function each($callback)
	{
		$this->verifyCallable($callback);
		array_walk_recursive($this->array, $callback);
		return $this;
	}
	public function filter($callback)
	{
		$this->verifyCallable($callback);
		return array_filter($this->array, $callback);
	}
	public function has($value)
	{
		return in_array($value, $this->array, true);
	}
	public function hasKey($key)
	{
		$this->verifyString($key, "hasKey");
		return array_key_exists($key, $this->array);
	}
	public function indexOf($value)
	{
		return array_search($value, $this->array);
	}
	public function intersect($array)
	{
		$this->verifyArray($array, "intersect");
		return array_intersect($this->array, $array);
	}
	public function intersperse($value)
	{
		$array = $this->array;
		$chunk = array_chunk($this->array, 1);
		$intersperser = function(&$row) {$row[1]="lalal";};
		foreach($chunk as &$row) {
			$row[1] = $value;
		}
		$result = call_user_func_array('array_merge', $chunk);
		array_pop($result);
		return $result;
	}
	public function join($on="")
	{
		return implode($on, $this->array);
	}
	public function keys()
	{
		return array_keys($this->array);
	}
	public function keySort()
	{
		ksort($this->array);
		return $this->array;
	}
	public function map($callback, $arguments = null)
	{
		$array = $this->array;
		if(null !== $callback) {
			$this->verifyCallable($callback);
		}
		if(null === $arguments) {
			$result = array_map($callback, $array);
		} else {
			$args = func_get_args();
			array_shift($args);
			array_unshift($args, $callback, $array);
			$result = call_user_func_array("array_map", $args);
		}
		return $result;
	}
	public function max()
	{
		return max($this->array);
	}
	public function merge($array)
	{
		$this->verifyArray($array, "merge");
		return array_merge($this->array, $array);
	}
	public function min()
	{
		return min($this->array);
	}
	public function push($val)
	{
		array_push($this->array, $val);
		return $this->array;
	}
	public function rand($number = 1)
	{
		$r = array_rand($this->array, $number);
		return $this[$r];
	}
	public function reduce($callback, $initial = null)
	{
		$this->verifyCallable($callback);
		return array_reduce($this->array, $callback, $initial);
	}
	public function reindex($by = null)
	{
		if(null === $by) return array_values($this->array);
		if(is_callable($by)) {
			$keys = array_map($by, $this->array);
			return array_combine($keys, array_values($this->array));
		}
	}
	public function reverse()
	{
		return array_reverse($this->array);
	}
	public function reverseKeySort()
	{
		krsort($this->array);
		return $this->array;
	}
	public function slice($offset, $length = null, $preserve = false)
	{
		$this->verifyInteger($offset, "slice");
		return array_slice($this->array, $offset, $length, $preserve);
	}
	public function splice($offset, $length = null, $replacement = null)
	{
		$this->verifyInteger($offset, "splice");
		$array = $this->array;
		if(null === $length) {
			$extracted = array_splice($array, $offset);
		} else {
			$extracted = array_splice($array, $offset, $length, $replacement);
		}
		return $array;
	}
	public function sort($flags = null)
	{
		$array = $this->array;
		$result = sort($array, $flags);
		if ($result === false) {
			throw new \InvalidArgumentException("Array object could not be sorted");
		}
		return $array;
	}
	public function sum()
	{
		return array_sum($this->array);
	}
	public function toArray()
	{
		$array = $this;
		return $array;
	}
	public function toJSON()
	{
		return json_encode($this->array);
	}
	public function values()
	{
		return array_values($this->array);
	}
}
