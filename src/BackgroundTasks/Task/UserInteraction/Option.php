<?php

namespace ILIAS\BackgroundTasks\Task;

interface Option {

	/**
	 * @return string
	 */
	public function getLangVar();

	/**
	 * @param string $lang_var
	 */
	public function setLangVar($lang_var);

	/**
	 * @return string
	 */
	public function getValue();

	/**
	 * @param string $value
	 */
	public function setValue($value);
}