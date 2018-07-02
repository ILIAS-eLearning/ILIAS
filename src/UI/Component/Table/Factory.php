<?php

/* Copyright (c) 2017 Nils Haagen <nhaageng@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

/**
 * Table factory
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Presentation Table lists some data from the system in a pleasant way.
	 *       The user should be able to get a quick overview over records in a
	 *       dataset as simple as possible; for this, only most relevant fields of
	 *       a record are being displayed at first glance.
	 *       Rows can be expanded to show more extensive fields and data
	 *       less important for the identification of a record.
	 *       The purpose of the presentation and exploration is known, and
	 *       single records may only be explored in this way,
	 *   composition: >
	 *       The Presentation Table consists of a title, a slot for View Controls
	 *       and Presentation Rows.
	 *       Rows again will be prefixed by an expander-button and consist of a
	 *       title, a subtitle and a choice of recors-fields.
	 *       The expanded row will show a lists of further fields and, optionally, buttons.
	 *   effect: >
	 *       Rows can be expanded and collapsed to show/hide more extensive and
	 *       detailed information per record.
	 *       A click on the expander will enlarge the row vertically to
	 *       show the complete record. Fields that were shown in the collapsed row will be
	 *       hidden except for title and subtitle.
	 *       The ordering or the contents of the table itself can be adjusted with view controls.
	 *   rivals:
	 *     1: >
	 *       Data Table: A data-table shows some dataset and offers tools to
	 *       explore it in a user defined way. Instead of aiming at simplicity
	 *       it aims at maximum explorability.
	 *     2: >
	 *       Listing Panel: Items represent system-entities; this is not necessarily
	 *       the case for Presentation Tables.
	 *
	 * rules:
	 *   usage:
	 *       1: Rows in the table SHOULD be of the same structure
	 *   interaction:
	 *       1: View Controls used here MUST only affect the table itself.
	 *       2: Clicking the expander MUST only expand the row. It MUST NOT trigger any other action.
	 *
	 * additional information: >
	 *   The closure $row_mapping MUST accept the following parameter
	 *   \PresentationRow $row
	 *   mixed $record
	 *   \Factory $ui_factory
	 *   mixed $environment
	 *
	 *   The closure MUST return \PresentationRow
	 * ---
	 * @param string	$title
	 * @param array		$view_controls 	a list of view controls
	 * @param \Closure 	$row_mapping  	the closure MUST accept and return a \PresentationRow as parameter
	 * @return \ILIAS\UI\Component\Table\Presentation
	 */
	public function presentation($title, array $view_controls, \Closure $row_mapping);



}
