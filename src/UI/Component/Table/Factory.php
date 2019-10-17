<?php

/* Copyright (c) 2017 Nils Haagen <nhaageng@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

/**
 * Table factory
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       The Presentation Table lists some tabular data in a pleasant way. The user
     *       can get a quick overview over the records in the dataset, the Presentation
     *       Table only shows the most relevant fields of the records at first glance.
     *       The records can be expanded to show more extensive information, i.e.
     *       additional fields and further information.
     *
     *       The Presentation Table represents the displayed dataset an entirety rather
     *       than a list of single rows. The table facilitates exploring the dataset,
     *       where the purpose of this exploration is known and supported. Single records
     *       may be derived and composed from all kind of sources and do not necessarily
     *       reference a persistent entity like an ilObject.
     *
     *   composition: >
     *       The Presentation Table consists of a title, a slot for View Controls and
     *       Presentation Rows. The rows will be prefixed by an Expand Glyph and consist
     *       of a headline, a subheadline and a choice of record-fields. The expanded row
     *       will show a lists of further fields and, optionally, a button or dropdown
     *       for actions. The table is visually represented as a wholeness and does not
     *       decompose into several parts.
     *
     *   effect: >
     *       Rows can be expanded and collapsed to show/hide more extensive and detailed
     *       information per record. A click on the Expand Glyph will enlarge the row
     *       vertically to show the complete record and exchange the Expand Glyph by a
     *       Collapse Glyph. Fields that were shown in the collapsed row will be hidden
     *       except for headline and subheadline. The ordering among the records in the
     *       table, the ordering of the fields in one row or the visible contents of the
     *       table itself can be adjusted with View Controls. In contrast to the accordions
     *       known from the page editor, it is possible to have multiple expanded rows in
     *       the table.
     *
     *   rivals:
     *     Data Table: >
     *       A data-table shows some dataset and offers tools to explore it
     *       in a user defined way. Instead of aiming at simplicity the Presentation
     *       Table aims at maximum explorability. Datasets that contain long content fields,
     *       e.g. free text or images, are hard to fit into a Data Table but can indeed
     *       be displayed in a Presentation Table.
     *     Listing Panel: >
     *       Listing Panels list items, where an item is a unique entity
     *       in the system, i.e. an identifyable, persistently stored object. This is
     *       not necessarily the case for Presentation Tables, where records can be composed
     *       of any data from any source in the system.
     *
     * rules:
     *   usage:
     *       1: Rows in the table MUST be of the same structure.
     *   interaction:
     *       1: View Controls used here MUST only affect the table itself.
     *       2: >
     *           Clicking the Expand Glyph MUST only expand the row. It MUST NOT trigger any
     *           other action.
     *   accessibility:
     *       1: >
     *           The expandable content, especially the contained buttons, MUST be accessible
     *           by only using the keyboard.
     *
     * ---
     * @param string	$title
     * @param array		$view_controls 	a list of view controls
     * @param \Closure 	$row_mapping
     * @return \ILIAS\UI\Component\Table\Presentation
     *
     * The closure $row_mapping MUST accept the following parameter
     *		PresentationRow 	$row
     *		mixed 				$record
     *		\ILIAS\UI\Factory 	$ui_factory
     *		mixed 				$environment
     * The closure must return a PresentationRow. It maps data from the supplied data
     * record $record to a row in the presentation.	For this purpose the closure is
     * presented with an empty $row	that it must modify according to its requirements.
     * To create additional components, the closure is also supplied with an $ui_factory.
     * The created table may supplied with additional information to be used when
     * creating the rows via `withEnvironment`. This information is then passed to
     * the mapping closure via the $environment parameter. Please also refer to the
     * UI examples.
     *
     */
    public function presentation($title, array $view_controls, \Closure $row_mapping);
}
