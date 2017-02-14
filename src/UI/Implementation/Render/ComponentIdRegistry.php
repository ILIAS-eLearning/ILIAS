<?php

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;

class ComponentIdRegistry implements ComponentIdRegistryInterface {

	/**
	 * @var array
	 */
	protected $mapping = array();


	/**
	 * @inheritdoc
	 */
	public function register(Component $component, $id) {
		$hash = $this->getHash($component);
		if (!isset($this->mapping[$hash])) {
			$this->mapping[$hash] = array();
		}
		$this->mapping[$hash][] = $id;
	}


	/**
	 * @inheritdoc
	 */
	public function getIds(Component $component) {
		$hash = $this->getHash($component);
		if (!isset($this->mapping[$hash])) {
			return array();
		}

		return $this->mapping[$hash];
	}


	/**
	 * Generate a hash of the given component
	 *
	 * @param Component $component
	 * @return string
	 */
	protected function getHash(Component $component) {
		return spl_object_hash($component);
	}

}