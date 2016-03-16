<?php

namespace CaT\Filter\Types;


class DictionaryType extends Type {
	private $sub_types;
	
	public function __construct(array $types ) {

		foreach ($types as $key => $type) {
			if(!($type instanceof \CaT\Filter\Types\Type)) {
				throw new \InvalidArgumentException("DictionaryType::Expected type");
			}
		}
		$this->sub_types = $types;
	}
	
	/**
	 * @inheritdocs
	 */
	public function contains($value) {
		if(count($value) !== count($this->sub_types)) {
			return false;
		}
		foreach ($this->sub_types as $key => $type) {
			if(!array_key_exists($key, $value)) {
				return false;
			}
			if(!$type->contains($value[$key])) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @inheritdocs
	 */
	public function repr() {
		return var_dump(array_map(function($type, $key) {return array($key => $type->repr);}, $this->sub_types, array_keys($this->sub_types)));
	}


	/**
	 * @inheritdocs
	 */
	public function flatten($value) {
		$res = array();
		foreach ($this->sub_types as $key => $type) {
			$res = array_merge($res, $type->flatten($value[$key]));
		}
		return $res;
	}


	/**
	 * @inheritdocs
	 */
	public function unflatten(array &$value) {
		$vals = array();
		foreach ($this->sub_types as $key => $sub_type) {
			$vals[$key] = $sub_type->unflatten($value);
		}
		return $vals;
	}
}