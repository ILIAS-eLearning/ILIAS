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
	 *       Records can be expanded to show more extensive fields and data
	 *       less important for the identification of a record.
	 *       The purpose of the presentation and exploration is known, and
	 *       single records may only be explored in this way,
	 *   composition: >
	 *       The Presentation Table consists of a title, a slot for View Controls
	 *       and Presentation Rows.
	 *   effect: >
	 *       Rows can be expanded and collapsed to show/hide more extensive and detailed information per record.
	 *        The ordering or the contents of the table itself can be adjusted with view controls.
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
	 *       1: Data-rows in the table SHOULD be of the same structure (i.e. have the same fields)
	 *   interaction:
	 *       1: View Controls used here MUST only affect the table itself.
	 *
	 * ---
	 * @param string	$title
	 * @param array		$view_controls 	a list of view controls
	 * @param \ILIAS\UI\Component\Table\PresentationRow[] 	$rows
	 * @return \ILIAS\UI\Component\Table\Presentation
	 */
	public function presentation($title, array $view_controls, array $rows);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       A Presentation Row is a record-entry for Presentation Tables.
	 *   composition: >
	 *       The Presentation Row consists of a title, a subtitle and
	 *       a choice of record-fields.
	 *       The row is prefixed by an expander-button.
	 *       The expanded view of a row consists of a descriptive list,
	 *       a list of buttons and a list of (further) record-fields.
	 *   effect: >
	 *        A click on the expander will enlarge the row vertically to
	 *        show the complete record. Fields that were shown in the collapsed row will be
	 *        hidden except for title and subtitle.
	 *
	 * rules:
	 *   usage:
	 *       1: Presentaion Rows MUST only be used in Presentation Tables.
	 *   interaction:
	 *       1: Clicking the expander MUST only expand the row. It MUST NOT trigger any other action.
	 * ---
	 * @param string 	$title_field
	 * @return \ILIAS\UI\Component\Table\PresentationRow
	 */
	public function presentationRow($title_field);

}
