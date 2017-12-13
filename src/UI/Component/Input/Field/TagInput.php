<?php
declare(strict_types=1);

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Signal;

/**
 * Interface TagInput
 *
 * this describes TagInput Inputs
 *
 * @package ILIAS\UI\Component\Input\Field
 */
interface TagInput extends Input, JavaScriptBindable {

	const EVENT_ITEM_ADDED = 'itemAdded';
	const EVENT_BEFORE_ITEM_REMOVE = 'beforeItemRemove';
	const EVENT_BEFORE_ITEM_ADD = 'beforeItemAdd';
	const EVENT_ITEM_REMOVED = 'itemRemoved';
	const QUERY_NAME = 'query';


	/**
	 * Get an input like this, but with an url where available option to choose from can be loaded.
	 *
	 * The URL MUST return a json-encoded array of key => value pairs
	 * such as [ 6 => 'root', 13 => 'anonymous' ], as JSON {"6":"root","13":"anonymous"}.
	 *
	 * The query will be appended to the $async_option_url as
	 * GET parameter TagInput::QUERY_NAME (currently "query")
	 *
	 * @param string $async_option_url
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withAsyncOptionsURL(string $async_option_url): TagInput;


	/**
	 * Enable or disable the possibility to allow the user to input more
	 * options than the given.
	 *
	 * @param bool $extendable
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withExtendableOptions(bool $extendable): TagInput;


	/**
	 * @return bool Whether the user is allowed to input more
	 * options than the given.
	 */
	public function areOptionsExtendable(): bool;


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
	 * Option to add is passed in .item as key => value
	 *
	 * @param \ILIAS\UI\Component\Signal $signal
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withAdditionalOnOptionAdded(Signal $signal): TagInput;


	/**
	 * Option to add is passed in getOptions()['option'] as key => value
	 *
	 * @param \ILIAS\UI\Component\Signal $signal
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withAdditionalOnBeforeOptionAdded(Signal $signal): TagInput;


	/**
	 * Option to remove is passed in getOptions()['option'] as key => value
	 *
	 * @param \ILIAS\UI\Component\Signal $signal
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withAdditionalOnOptionRemoved(Signal $signal): TagInput;


	/**
	 * Option to remove is passed in getOptions()['option'] as key => value
	 *
	 * @param \ILIAS\UI\Component\Signal $signal
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withAdditionalOnBeforeOptionRemoved(Signal $signal): TagInput;
}
