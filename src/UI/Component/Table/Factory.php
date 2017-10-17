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
	 *       a record are being displayed at first glance. The deeper exploration of
	 *       data should be as easy: Rows can be expanded to show more extensive fields
	 *       and additional data that is less important for the identification of a record.
	 *
	 *       The aim of the Presentation Table is to represent a dataset as an entirety
	 *       rather than a list of single rows. The table focusses on exploring this set,
	 *       while the purpose of this exploration is known and supported.
	 *       A single record does not necessarily reference a persistent entity like an ilObject,
	 *       but can be derived and composed from all kind of sources.
	 *
	 *   composition: >
	 *       The Presentation Table consists of a title, a slot for View Controls
	 *       and Presentation Rows.
	 *       Rows again will be prefixed by an expander-button and consist of a
	 *       title, a subtitle and a choice of record-fields.
	 *       The expanded row will show a lists of further fields and, optionally, buttons.
	 *       The Table will not decompose into several parts but instead be represented
	 *       as a wholeness.
	 *
	 *   effect: >
	 *       Rows can be expanded and collapsed to show/hide more extensive and
	 *       detailed information per record. A click on the expander will enlarge
	 *       the row vertically to show the complete record. Fields that were shown
	 *       in the collapsed row will be hidden except for title and subtitle.
	 *       The ordering or the visible contents of the table itself can be
	 *       adjusted with view controls.
	 *   rivals:
	 *     1: >
	 *       Data Table: A data-table shows some dataset and offers tools to
	 *       explore it in a user defined way. Instead of aiming at simplicity
	 *       the Presentation Table aims at maximum explorability.
	 *     2: >
	 *       Listing Panel: Listing Panels list items, where an item is a
	 *       unique entity in the system, i.e. an identifyable, persistently
	 *       stored object. This is not necessarily the case for Presentation Tables,
	 *       where records can be composed of any data from any source.
	 *
	 * rules:
	 *   usage:
	 *       1: Rows in the table MUST be of the same structure.
	 *   interaction:
	 *       1: View Controls used here MUST only affect the table itself.
	 *       2: Clicking the expander MUST only expand the row. It MUST NOT trigger any other action.
	 *   accessibility:
	 *       1: The expandable content, especially the contained buttons, MUST be accessible by only using the keyboard.
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
	 * @param \Closure 	$row_mapping  	The closure $row_mapping MUST accept the following parameter
	 *										\PresentationRow 	$row
	 *										mixed 				$record
	 *										\Factory 			$ui_factory
	 *										mixed 				$environment
     *									The closure must also return \PresentationRow
	 *									A Presentation Row maps elements of the data-record to
	 *									the visual representation of the table-row.
	 *									The Presentation Row is supplied by the renderer and is modified
	 *									in the closure.
	 * 									Please also see docs at Component\Table\Presentation::withRowMapping
	 *
	 * @return \ILIAS\UI\Component\Table\Presentation
	 */
	public function presentation($title, array $view_controls, \Closure $row_mapping);

}
