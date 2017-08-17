<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

/**
 * This describes a Presentation Table
 */
interface Presentation extends \ILIAS\UI\Component\Component {

	/**
	 * @return SignalGenerator
	 */
	public function getSignalGenerator();


	/**
	 * Get the title of the table.
	 *
	 * @return string
	 */
	public function getTitle();

	/**
	 * Get view controls to be shown in the header of the table.
	 *
	 * @return ILIAS\UI\Component\ViewControl[]
	 */
	public function getViewControls();

	/**
	 * Return the closure to map some data to the row.
	 *
	 * The closure MUST accept the following parameter
	 * \PresentationRow $row
	 * mixed $record
	 * \Factory $ui_factory
	 * array<string,mixed> $environment
	 *
	 * The closure MUST return \PresentationRow
	 *
	 * @return \Closure
	 */
	public function getRowMapping();

	/**
	 * Add a list of objects the mapping-closure needs for processing.
	 *
	 * @param array<string,mixed> 	$environment
	 * @return \Presentation
	 */
	public function withEnvironment(array $environment);

	/**
	 * Get the environment configured with this table
	 *
	 * @return array<string,mixed>
	 */
	public function getEnvironment();

	/**
	 * Fill a recordset into the table.
	 * All elements in $records MUST be processable by the mapping-closure.
	 *
	 * @param array<mixed> 	$records
	 * @return \Presentation
	 */
	public function withData(array $records);

	/**
	 * Get the recordset of this table.
	 *
	 * @return array<mixed>
	 */
	public function getData();


}
