<?php
namespace ILIAS\UI\Implementation\Component;

/**
 * Class SignalGenerator
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
class SignalGenerator implements SignalGeneratorInterface {

	const PREFIX = 'il_signal_';

	/**
	 * @inheritdoc
	 */
	public function create() {
		return new Signal($this->createId());
	}

	/**
	 * @return string
	 */
	protected function createId() {
		return str_replace(".", "_", uniqid(self::PREFIX));
	}
}