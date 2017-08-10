<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

use ILIAS\UI\Component\Triggerable;

/**
 * This describes a Row used in Presentation Table
 */
interface PresentationRow extends \ILIAS\UI\Component\Component, Triggerable {

	/**
	 * Get the name of the field to be used as title.
	 *
	 * @return string
	 */
	public function getTitleField();

	/**
	 * Set the record-field to be used as subtitle.
	 *
	 * @param string 	$subtitle_field
	 */
	public function withSubtitleField($subtitle_field);

	/**
	 * Get the name of the field to be used as subtitle.
	 *
	 * @return string
	 */
	public function getSubtitleField();

	/**
	 * Set the record-fields and labels to be shown in the collapsed row.
	 *
	 * @param array<string, string> 	$fields
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
	 * Set the record-fields and labels to be shown in the description list
	 * of the expanded row.
	 *
	 * @param array<string, string> 	$fields
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
	 * Set a headline for the field-list in the expanded row.
	 *
	 * @param string 	$headline
	 */
	public function withFurtherFieldsHeadline($headline);

	/**
	 * Get the headline for additional fields in the expanded row.
	 *
	 * @return string
	 */
	public function getFurtherFieldsHeadline();

	/**
	 * Set the record-fields and labels to be shown in the list
	 * of the expanded row.
	 *
	 * @param array<string, string> 	$fields
	 */
	public function withFurtherFields(array $fields);

	/**
	 * Get the names and labels of the fields to be used in the expanded row.
	 *
	 * @return array<string,string>
	 */
	public function getFurtherFields();

	/**
	 * Apply a record to the row.
	 *
	 * @param array<string, mixed> 	$data
	 */
	public function withData(array $data);

	/**
	 * Get the record associated with this row.
	 *
	 * @return array<string,mixed>
	 */
	public function getData();

	/**
	 * Add buttons for actions in the expanded row.
	 *
	 * @param ILIAS\UI\Component\Button\Button[] 	$buttons
	 */
	public function withButtons(array $buttons);

	/**
	 * Get a list of buttons to be shown in the expanded row.
	 *
	 * @return ILIAS\UI\Component\Button\Button[]
	 */
	public function getButtons();

}
