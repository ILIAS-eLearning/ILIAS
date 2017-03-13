<?php
namespace ILIAS\UI\Component\Input;
/**
 * This is how a factory for inputs looks like.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A selector allows to chose from a finite set of elements. There are 1:n selectors, which allow to select exactly one out of n elements
	 *     (this is sometimes also called single choice) and m:n Selections to select m elements of of a set of n elements, where n<=m (multiple choice).
	 *   composition: >
	 *     Selection Controls consist of selectable elements in the form of e.g. Checkboxes, Radio Options and Selection Lists.
	 *   effect: >
	 *
	 * background: >
	 *   Tiddwell describes Selection Controls as “List of Items” and states that the choice of control
	 *   “depends on the number of items or options to be selected (one or many) and the number of potentially selectable items (two, a handful, or many)”.
	 *
	 * rules:
	 *   accessibility:
	 *     1: Toggling the selection of a focused element MUST be achievable by only using the keyboard.
	 *
	 * ----
	 * @return  \ILIAS\UI\Component\Input\Selector\Factory
	 */
	public function selector();
}