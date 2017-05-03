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
	 * @var array
	 */
	protected $options = array();

	/**
	 * @param string $id
	 */
	public function __construct($id) {
		$this->id = $id;
	}

	/**
	 * @inheritdoc
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->id;
	}

}