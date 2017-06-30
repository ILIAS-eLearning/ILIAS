<?php
namespace ILIAS\UI\Implementation\Component;

/**
 * Class Signal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
class Signal implements \ILIAS\UI\Component\Signal {

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @param string $id
	 */
	public function __construct($id) {
		$this->id = $id;
	}

	public function __toString() {
		return $this->id;
	}

}