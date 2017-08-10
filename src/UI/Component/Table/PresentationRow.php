<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

use ILIAS\UI\Component\Triggerable;

/**
 * This describes a Row used in Presentation Table.
 * A row consists (potentially) of title, subtitle, important fields (in the
 * collapased row) and further fields to be shown in the expanded row.
 * The fields of a data-record are to be mapped to the row by their keys.
 */
interface PresentationRow extends \ILIAS\UI\Component\Component, Triggerable {

	/**
	 * Get the name of the field to be used as title.
	 *
	 * @return string
	 */
	public function getTitleField();

	/**
	 * Get a row like this with the record-field to be used as subtitle.
	 *
	 * @param string 	$subtitle_field
	 * @return \ILIAS\UI\Component\Table\PresentationRow
	 */
	public function withSubtitleField($subtitle_field);

	/**
	 * Get the name of the field to be used as subtitle.
	 *
	 * @return string
	 */
	public function getSubtitleField();

	/**
	 * Get a row like this with the record-fields and labels
	 * to be shown in the collapsed row.
	 *
	 * @param array<string,string> 	$fields
	 * @return \ILIAS\UI\Component\Table\PresentationRow
	 */
	public function withImportantFields(array $fields);

	/**
	 * Get the names and labels of the field to be used as important fields
	 * in the collapsed row.
	 *
	 * @return array<string,string>
	 */
	public function getImportantFields();

	/**
	 * Get a row like this with the record-fields and labels to be shown
	 * in the description list of the expanded row.
	 *
	 * @param array<string,string> 	$fields
	 * @return \ILIAS\UI\Component\Table\PresentationRow
	 */
	public function withDescriptionFields(array $fields);

	/**
	 * Get the names and labels of the fields to be used in the description list
	 * in the expanded row.
	 *
	 * @return array<string,string>
	 */
	public function getDescriptionFields();

	/**
	 * Get a row like this with a headline for the field-list in the expanded row.
	 *
	 * @param string 	$headline
	 * @return \ILIAS\UI\Component\Table\PresentationRow
	 */
	public function withFurtherFieldsHeadline($headline);

	/**
	 * Get the headline for additional fields in the expanded row.
	 *
	 * @return string
	 */
	public function getFurtherFieldsHeadline();

	/**
	 * Get a row like this with the record-fields and labels to be shown
	 * in the list of the expanded row.
	 *
	 * @param array<string,string> 	$fields
	 * @return \ILIAS\UI\Component\Table\PresentationRow
	 */
	public function withFurtherFields(array $fields);

	/**
	 * Get the names and labels of the fields to be used in the expanded row.
	 *
	 * @return array<string,string>
	 */
	public function getFurtherFields();

	/**
	 * Get a row like this with data from a record applied to it.
	 *
	 * @param array<string,mixed> 	$data
	 * @return \ILIAS\UI\Component\Table\PresentationRow
	 */
	public function withData(array $data);

	/**
	 * Get the record associated with this row.
	 *
	 * @return array<string,mixed>
	 */
	public function getData();

	/**
	 * Get a row like this with buttons for actions in the expanded row.
	 *
	 * @param ILIAS\UI\Component\Button\Button[] 	$buttons
	 * @return \ILIAS\UI\Component\Table\PresentationRow
	 */
	public function withButtons(array $buttons);

	/**
	 * Get a list of buttons to be shown in the expanded row.
	 *
	 * @return ILIAS\UI\Component\Button\Button[]
	 */
	public function getButtons();

}
