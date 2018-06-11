<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This is what a factory for input fields looks like.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A text-field is intended for entering short single-line texts.
	 *   composition: >
	 *      Text fields will render an input-tag with type="text".
	 *   effect: >
	 *      Text inputs are restricted to one line of text.
	 *   rivals:
	 *      numeric field: Use a numeric field if users should input numbers.
	 *      alphabet field: >
	 *          Use an alphabet field if the user should input single letters.
	 *
	 * rules:
	 *   usage:
	 *     1: Text Input MUST NOT be used for choosing from predetermined options.
	 *     2: >
	 *         Text input MUST NOT be used for numeric input, a Numeric Field is
	 *         to be used instead.
	 *     3: >
	 *         Text Input MUST NOT be used for letter-only input, an Alphabet Field
	 *         is to be used instead.
	 *   interaction:
	 *     1: >
	 *         Text Input MUST limit the number of characters, if a certain length
	 *         of text-input may not be exceeded (e.g. due to database-limitations).
	 *
	 * ---
	 *
	 * @param    string      $label
	 * @param    string|null $byline
	 *
	 * @return    \ILIAS\UI\Component\Input\Field\Text
	 */
	public function text($label, $byline = null);


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      A numeric field is used to retrieve numeric values from the user.
	 *   composition: >
	 *      Numeric inputs will render an input-tag with type="number".
	 *   effect: >
	 *      The field does not accept any data other than numeric values. When
	 *      focused most browser will show a small vertical rocker to increase
	 *      and decrease the value in the field.
	 * rules:
	 *   usage:
	 *     1: Number Inputs MUST NOT be used for binary choices.
	 *     2: >
	 *         Magic numbers such as -1 or 0 to specify “limitless” or smoother
	 *         options MUST NOT be used.
	 *     3: A valid input range SHOULD be specified.
	 *
	 * ---
	 *
	 * @param    string      $label
	 * @param    string|null $byline
	 *
	 * @return    \ILIAS\UI\Component\Input\Field\Numeric
	 */
	public function numeric($label, $byline = null);


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Input groups are an unlabeled collection of inputs. They are used to
	 *      build logical units of other fields. Such units might be used to attach some
	 *      constraints or transformations for multiple fields.
	 *   composition: >
	 *      Groups are composed of inputs. They do not contain a label. The grouping
	 *      remains invisible for the client.
	 *   effect: >
	 *      There is no visible effect using groups.
	 *   rivals:
	 *      sections: Sections are used to generate a visible relation of fields.
	 *
	 * rules: []
	 *
	 * ---
	 *
	 * @param    array<mixed,\ILIAS\UI\Component\Input\Field\Input>    $inputs
	 *
	 * @return    \ILIAS\UI\Component\Input\Field\Group
	 */
	public function group(array $inputs);


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Sections are used to visually group inputs to a common context.
	 *   composition: >
	 *      Sections are composed of inputs. They carry a label and are visible for
	 *      the client.
	 *   rivals:
	 *      Groups: >
	 *          Groups are used as purely logical units, while sections visualize
	 *          the correlation of fields.
	 *
	 * rules:
	 *   composition:
	 *     1: Sections SHOULD comprise 2 to 5 Settings.
	 *     2: >
	 *       More than 5 Settings SHOULD be split into two areas unless this would
	 *       tamper with the “familiar” information architecture of forms.
	 *     3: >
	 *       In standard forms, there MUST NOT be a Setting without an enclosing Titled
	 *       Form Section. If necessary a Titled Form Section MAY contain only one single
	 *       Setting.
	 *   wording:
	 *     1: >
	 *       The label SHOULD summarize the contained settings accurately from a
	 *       user’s perspective.
	 *     2: >
	 *       The title SHOULD contain less than 30 characters.
	 *     3: >
	 *       The titles MUST be cross-checked with similar sections in other objects or
	 *       services to ensure consistency throughout ILIAS.
	 *     4: >
	 *       In doubt consistency SHOULD be prioritized over accuracy in titles.
	 *
	 * ---
	 *
	 * @param    array<mixed,\ILIAS\UI\Component\Input\Field\Input>    $inputs
	 * @param    string|null $label
	 * @param    string      $byline
	 *
	 * @return    \ILIAS\UI\Component\Input\Field\Section
	 */
	public function section(array $inputs, $label, $byline = null);


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Fields can be nested by using dependant groups (formerly known as subforms)
	 *      allowing for settings-dependent configurations.
	 *   composition: >
	 *      Dependant groups are like groups composed of a set of input fields.
	 *   effect: >
	 *      The display of dependent group is triggered by enabling some other input
	 *      field which has an attached dependant group. Note that not all fields allow
	 *      this (e.g. Checkboxes do). Look at the interface whether and how dependant
	 *      groups can be attached.
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *       There MUST NOT be a nesting of more than one dependant group. The only
	 *       exception to this rule is the required quantification of a subsetting by a
	 *       date or number. These exceptions MUST individually accepted by the Jour Fixe.
	 * ---
	 *
	 *
	 * @param    array<mixed,\ILIAS\UI\Component\Input\Field\Input>    $inputs
	 *
	 * @return    \ILIAS\UI\Component\Input\Field\DependantGroup
	 */
	public function dependantGroup(array $inputs);


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A checkbox is used to govern a state, action, or set / not to set a value.
	 *     Checkboxes are typically used to switch on some additional behaviour or services.
	 *   composition: >
	 *     Each Checkbox is labeled by an identifier stating something positive to
	 *     describe the effect of checking the Checkbox.
	 *   effect: >
	 *     If used in a form, a checkbox may open a dependant section (formerly known
	 *     as sub form).
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *      A checkbox MUST NOT be used whenever a user has to perform a binary choice where
	 *      option is not automatically the inverse of the other (such as 'Order by Date' and
	 *      'Order by Name'). A Select Input or a Radio Group in MUST be
	 *      used in this case.
	 *   wording:
	 *     1: The checkbox’s identifier MUST always state something positive.
	 *
	 * ---
	 *
	 * @return    \ILIAS\UI\Component\Input\Field\Checkbox
	 */
	public function checkbox($label, $byline = null);


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A Tag Input is used to choose a subset amount of tags (techn.: array of strings) out
	 *     of a finite list of tags. The Tag Field SHOULD be used, whenever it is not required
	 *     or not possible to display all available options, e.g. because the amount is too high
	 *     when the options are "all users" or "all tags.
	 *     Besides the tags to choose from, the user can provide own tags by typing them
	 *     into the Input (@see Tag::withOptionsAreExtendable ).
	 *   composition: >
	 *     The Input is presented as a text-input and prepended by already selected tags
	 *     presented as texts including a close-button.  (e.g. [ Amsterdam X ] )
	 *     The input is labeled by the label given.
	 *     Suggested tags are listed in a dropdown-list beneath the text-input.
	 *     All mentioned elements are not taken from the UI-Service.
	 *   effect: >
	 *     As soon as the user types in the text-input, the Tag Input suggests matching tags from
	 *     the the given list of tags. Suggestions will appear after a defined
	 *     amount of characters, one by default.
	 *     Clicking on one of these tags closes the list and transfers the selected tag into
	 *     the text-input, displayed as a tag with a close-button.
	 *     By clicking on a close-button of a already selected tag, this tag will disappear
	 *     from the Input.
	 *     All mentioned elements are not taken from the UI-Service.
	 *
	 * rivals: >
	 *     + SelectInput: Currently not part of the UI-Service.
	 *     + Checkbox Group
	 *
	 * context:
	 *   - Tag Input is used in forms.
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *      A Tag Input MUST NOT be used whenever a user has to perform a binary choice where
	 *      option is automatically the inverse of the other. A Checkbox MUST be used in this case.
	 *     2: >
	 *      A Tag Input MUST NOT be used whenever a user has to perform a choice from a list of
	 *      options where only one Option has to be selected. A Select MUST be used in this case
	 *      (Not yet part of the KitchenSink).
	 *     3: >
	 *      A Tag Input SHOULD be used whenever a User should be able to extend the list of given options.
	 *     4: >
	 *      A Tag Input MUST NOT be used when a User has to choose from a finite list of options
	 *      which can't be extended by users Input, a Multi Select MUST be used in this case
	 *      (Not yet part of the KitchenSink).
	 *     5: The tags provided SHOULD NOT have long titles (50 characters).
	 *
	 * ---
	 * @param string $label
	 * @param string $byline
	 * @param string[]  $tags    List of tags to select from, given as a list of texts
	 *                        such as [ 'Interesting', 'Boring', 'Animating', 'Repetitious' ]
	 *
	 * @return    \ILIAS\UI\Component\Input\Field\Tag
	 */
	public function tag(string $label, array $tags, $byline = null);
}
