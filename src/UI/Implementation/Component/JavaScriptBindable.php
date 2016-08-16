<?php

namespace ILIAS\UI\Implementation\Component;

/**
 * Trait for components implementing JavaScriptBindable providing standard
 * implementation.
 */
trait JavaScriptBindable {
	/**
	 * @var		\Closure|null
	 */
	private $on_load_code_binder = null;

	/**
	 * @see \ILIAS\UI\Component\JavaScriptBindable::withOnLoadCode
	 */
	public function withOnLoadCode(\Closure $binder) {
		$this->checkBinder($binder);
		$clone = clone $this;
		$clone->on_load_code_binder = $binder;
		return $clone;
	}

	/**
	 * @see \ILIAS\UI\Component\JavaScriptBindable::getOnLoadCode
	 */
	public function getOnLoadCode() {
		return $this->on_load_code_binder;
	}

	/**
	 * @param	\Closure	$binder
	 * @throw	\InvalidArgumentException	if closure does not take one argument
	 * @return 	null
	 */
	private function checkBinder(\Closure $binder) {
		// TODO: implement me
	}	
}
