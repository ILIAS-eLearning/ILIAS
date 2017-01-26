<?php
namespace ILIAS\UI\Implementation\Component;

/**
 * Class SignalGenerator
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
class SignalGenerator implements \ILIAS\UI\Component\SignalGenerator {

	const PREFIX = 'il_signal_';

	/**
	 * @inheritdoc
	 */
	public function create() {
		return str_replace(".", "_", uniqid(self::PREFIX, true));
	}
}