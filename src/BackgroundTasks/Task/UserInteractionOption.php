<?php

namespace ILIAS\BackgroundTasks\Task;

class UserInteractionOption {
	/**
	 * @var string
	 */
	protected $lang_var;

	/**
	 * @var string
	 */
	protected $value;

	/**
	 * @return string
	 */
	public function getLangVar() {
		return $this->lang_var;
	}

	/**
	 * @param string $lang_var
	 */
	public function setLangVar($lang_var) {
		$this->lang_var = $lang_var;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}
}