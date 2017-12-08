<?php

namespace ILIAS\UI\Component\Input\Field;

/**
 * Interface MultiSelect
 *
 * this describes MultiSelect Inputs
 *
 * @package ILIAS\UI\Component\Input\Field
 */
interface MultiSelect extends Input {

	/**
	 * Get an input like this, but with an url where available option to choose from can be loaded.
	 *
	 * The URL MUST return a json-encoded array of key => value pairs
	 * such as [ 6 => 'root', 13 => 'anonymous' ], as JSON {"6":"root","13":"anonymous"}.
	 *
	 * @param string $async_option_url
	 *
	 * @return \ILIAS\UI\Component\Input\Field\MultiSelect
	 */
	public function withAsyncOptionsURL($async_option_url): MultiSelect;


	/**
	 * @see withAsyncOptionsURL
	 * @return string
	 */
	public function getAsyncOptionsURL(): string;


	/**
	 * @return array of options such as [ 6 => 'root', 13 => 'anonymous' ]
	 */
	public function getOptions(): array;
}
