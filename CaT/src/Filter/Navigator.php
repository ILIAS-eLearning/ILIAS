<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

namespace CaT\Filter;

/**
* Decides which kind of Filter should be displayed and initialize GUI
*/
class Navigator {

	protected $tree;

	public function __construct($tree) {
		$this->tree = $tree;
		$this->path = array("0");
	}

	public function tree() {
		return $this->tree;
	}

	public function left() {
		$path = $this->path;

		$left_path = (int)$path[count($path) - 1] - 1;
		$path[count($path) - 1] = $left_path;

		$left = $this->getItemByPath($path, $this->tree);

		if($left === null) {
			throw new \OutOfBoundsException("No left neighbor");
		}

		$this->path = $path;
	}

	public function right() {
		$path = $this->path;

		$right_path = (int)$path[count($path) - 1] + 1;
		$path[count($path) - 1] = $right_path;

		$right = $this->getItemByPath($path, $this->tree);

		if($right === null) {
			throw new \OutOfBoundsException("No right neighbor");
		}

		$this->path = $path;
	}

	public function enter() {
		$current = $this->current();

		if(!($current instanceof Filters\FilterList)) {
			throw new \OutOfBoundsException("Not possible to enter node");
		}

		$this->path[] = "0";
	}

	public function up() {
		$path = $this->path;

		if(count($path) == 1) {
			throw new \OutOfBoundsException("Not possible to enter upper node");
		}
		unset($path[count($path)-1]);

		$this->path = $path;
	}

	public function select($path) {
		$path = explode(":",$path);
		$tmp = $this->getItemByPath($path, $this->tree);

		if($tmp === null) {
			throw new \OutOfBoundsException("Not possible to select node ".$path);
		}

		$this->path = $path;
		return $tmp;
	}

	public function current() {
		return $this->getItemByPath($this->path, $this->tree);
	}

	protected function getItemByPath($path, $tmp) {
		foreach ($path as $value) {
			$tmp = $tmp->subs()[$value];
		}

		return $tmp;
	}

	public function path() {
		return implode(":",$this->path);
	}
}