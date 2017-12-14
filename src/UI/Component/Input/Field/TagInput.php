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


	/**
	 * @return array of options such as [ 'Interesting', 'Boring', 'Animating', 'Repetitious' ]
	 */
	public function getOptions(): array;


	/**
	 * Get an input like this, but decide whether the user can provide own
	 * tags or not. (Default: Allowed)
	 *
	 * @param bool $extendable
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withTagsAreExtendable(bool $extendable): TagInput;


	/**
	 * @see withTagsAreExtendable
	 * @return bool Whether the user is allowed to input more
	 * options than the given.
	 */
	public function areTagsExtendable(): bool;


	/**
	 * Get an input like this, but change the amount of characters the
	 * user has to provide before the seggustions start (Default: 1)
	 *
	 * @param int $characters , defaults to 1
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withSuggestionsStartAfter(int $characters): TagInput;


	/**
	 * @see withSuggestionsStartAfter
	 * @return int
	 */
	public function getSuggestionsStartAfter(): int;


	/**
	 * Get an input like this, but limit the amount of characters one tag can be. (Default: unlimited)
	 *
	 * @param int $max_length
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withTagMaxLength(int $max_length): TagInput;


	/**
	 * @see withTagMaxLength
	 * @return int
	 */
	public function getTagMaxLength(): int;


	/**
	 * Get an input like this, but limit the amount of tags a user can select or provide. (Default: unlimited)
	 *
	 * @param int $max_tags
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withMaxTags(int $max_tags): TagInput;


	/**
	 * @see withMaxTags
	 * @return int
	 */
	public function getMaxTags(): int;


	// Events


	/**
	 * @param \ILIAS\UI\Component\Signal $signal
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withAdditionalOnOptionAdded(Signal $signal): TagInput;


	/**
	 * @param \ILIAS\UI\Component\Signal $signal
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withAdditionalOnBeforeOptionAdded(Signal $signal): TagInput;


	/**
	 * @param \ILIAS\UI\Component\Signal $signal
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withAdditionalOnOptionRemoved(Signal $signal): TagInput;


	/**
	 * @param \ILIAS\UI\Component\Signal $signal
	 *
	 * @return \ILIAS\UI\Component\Input\Field\TagInput
	 */
	public function withAdditionalOnBeforeOptionRemoved(Signal $signal): TagInput;
}
