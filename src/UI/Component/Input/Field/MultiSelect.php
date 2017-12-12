<?php

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Signal;

/**
 * Interface MultiSelect
 *
 * this describes MultiSelect Inputs
 *
 * @package ILIAS\UI\Component\Input\Field
 */
interface MultiSelect extends Input, JavaScriptBindable {

	const EVENT_ITEM_ADDED = 'itemAdded';
	const EVENT_BEFORE_ITEM_REMOVE = 'beforeItemRemove';
	const EVENT_BEFORE_ITEM_ADD = 'beforeItemAdd';
	const EVENT_ITEM_REMOVED = 'itemRemoved';


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


	/**
	 * Option to add is passed in getOptions()['option'] as key => value
	 *
	 * @param \ILIAS\UI\Component\Signal $signal
	 *
	 * @return \ILIAS\UI\Component\Input\Field\MultiSelect
	 */
	public function withAdditionalOnOptionAdded(Signal $signal): MultiSelect;


	/**
	 * Option to add is passed in getOptions()['option'] as key => value
	 *
	 * @param \ILIAS\UI\Component\Signal $signal
	 *
	 * @return \ILIAS\UI\Component\Input\Field\MultiSelect
	 */
	public function withAdditionalOnBeforeOptionAdded(Signal $signal): MultiSelect;


	/**
	 * Option to remove is passed in getOptions()['option'] as key => value
	 *
	 * @param \ILIAS\UI\Component\Signal $signal
	 *
	 * @return \ILIAS\UI\Component\Input\Field\MultiSelect
	 */
	public function withAdditionalOnOptionRemoved(Signal $signal): MultiSelect;


	/**
	 * Option to remove is passed in getOptions()['option'] as key => value
	 *
	 * @param \ILIAS\UI\Component\Signal $signal
	 *
	 * @return \ILIAS\UI\Component\Input\Field\MultiSelect
	 */
	public function withAdditionalOnBeforeOptionRemoved(Signal $signal): MultiSelect;
}
