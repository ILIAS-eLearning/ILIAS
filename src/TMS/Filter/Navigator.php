<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter;

class Navigator {

	protected $tree;

	/**
	 * @var	array|null
	 */
	protected $path = null;

	public function __construct($tree) {
		$this->tree = $tree;
	}

	/**
	 * get current tree
	 *
	 * @return CaT/Filter/Filters/Sequence
	 */
	public function tree() {
		return $this->tree;
	}

	/**
	 * pointer on next element one step left
	 *
	 * @throws OutOfBoundsException
	 *
	 * @return CaT/Filter/Navigator
	 */
	public function left() {
		$path = $this->path;

		$left_path = (int)$path[count($path) - 1] - 1;

		if($left_path < 0) {
			throw new \OutOfBoundsException("No left neighbor");
		}

		$path[count($path) - 1] = $left_path;
		$left = $this->getItemByPath($path, $this->tree);

		$this->path = $path;

		return $this;
	}

	/**
	 * pointer on next element one step right
	 *
	 * @throws OutOfBoundsException
	 *
	 * @return CaT/Filter/Navigator
	 */
	public function right() {
		$path = $this->path;

		$right_path = (int)$path[count($path) - 1] + 1;
		$path[count($path) - 1] = $right_path;

		$right = $this->getItemByPath($path, $this->tree);

		if(!$right) {
			throw new \OutOfBoundsException("No right neighbor");
		}

		$this->path = $path;

		return $this;
	}

	/**
	 * pointer on first element after entering next level
	 *
	 * @throws OutOfBoundsException
	 *
	 * @return CaT/Filter/Navigator
	 */
	public function enter() {
		$current = $this->current();

		if(!($current instanceof Filters\Sequence)) {
			throw new \OutOfBoundsException("Not possible to enter node");
		}

		$this->path[] = "0";

		return $this;
	}

	/**
	 * pointer on first element after jump one level up
	 *
	 * @throws OutOfBoundsException
	 *
	 * @return CaT/Filter/Navigator
	 */
	public function up() {
		$path = $this->path;

		if(count($path) == 1) {
			throw new \OutOfBoundsException("Not possible to enter upper node");
		}
		unset($path[count($path)-1]);

		$this->path = $path;

		return $this;
	}

	/**
	 * pointer on element to $path
	 *
	 * @param string $path
	 *
	 * @throws OutOfBoundsException
	 *
	 * @return CaT/Filter/Navigator
	 */
	public function go_to($path) {
		$path = explode("_",$path);
		$tmp = $this->getItemByPath($path, $this->tree);

		if(!$tmp) {
			throw new \OutOfBoundsException("Not possible to select node ".$this->path());
		}

		$this->path = $path;

		return $this;
	}

	/**
	 * get current element
	 *
	 * @return Filter
	 */
	public function current() {
		return $this->getItemByPath($this->path, $this->tree);
	}

	/**
	 * get element
	 *
	 * @return CaT/Filter/Filters/Filter | Bool
	 */
	protected function getItemByPath($path, $tmp) {
		foreach ($path as $value) {
			$tmp = $tmp->subs();

			if(!array_key_exists($value, $tmp)) {
				return false;
			}

			$tmp = $tmp[$value];
		}

		return $tmp;
	}

	/**
	 * pointer on first element after entering next level
	 *
	 * @return string|null
	 */
	public function path() {
		return is_array($this->path) ? implode("_",$this->path) : null;
	}
}
