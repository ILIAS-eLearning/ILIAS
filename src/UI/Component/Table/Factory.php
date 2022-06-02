<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
namespace ILIAS\UI\Component\Table;

use Closure;

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
     *       may be derived and composed of all kind of sources and do not necessarily
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
     *       in the system, i.e. an identifiable, persistently stored object. This is
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
     * @param string  $title
     * @param array   $view_controls 	a list of view controls
     * @param Closure $row_mapping
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
    public function presentation(string $title, array $view_controls, Closure $row_mapping) : Presentation;

    /**
     * ---
     * description:
     *   purpose: >
     *       The data table lists records in a complete and clear manner; the fields
     *       of a record are always of the same nature as their counterparts in all other
     *       records, i.e. a column has a dedicated shape.
     *       Each record is mapped to one row, while the number of visible columns is
     *       identical for each row.
     *       The purpose of exploration is unknown to the Data Table, and it does not suggest
     *       or favor a certain way of doing so.
     *
     *   composition: >
     *       The Data Table consists of a title, a View Control Container for (and with)
     *       View Controls and the table-body itself.
     *       The Table brings some ViewControls with it: The assumption is that the exploration
     *       of every Data Table will benefit from pagination, sortation and column selection.
     *       Records are being applied to Columns to build the actual cells.
     *
     *   effect: >
     *       The ordering among the records in the table, the visibility of columns as well as
     *       the number of simultaneously displayed rows are controlled by the Table's View Controls.
     *       Operating the order-glyphs in the column title will change the records' order.
     *       This will also reflect in the aria-sort attribute of  the columns' headers.
     *
     *   rivals:
     *     Presentation Table: >
     *       There is a weighting in the prominence of fields in the Presentation Table;
     *       Aside from maybe the column' order - left to right, not sortation of rows - all
     *       fields are displayed with equal emphasis (or rather, the lack of it).
     *     Listing Panel: >
     *       Listing Panels list items, where an item is a unique entity
     *       in the system, i.e. an identifiable, persistently stored object. This is
     *       not necessarily the case for Tables, where records can be composed
     *       of any data from any source in the system.
     *
     * rules:
     *   usage:
     *       1: >
     *         Tables MUST NOT be used to merely arrange elements visually;
     *         displayed records MUST have a certain consistency of content.
     *       2: A Data Table SHOULD have at least 3 Columns.
     *       3: A Data Table SHOULD potentially have an unlimited number of rows.
     *       4: Rows in the table MUST be of the same structure.
     *       5: >
     *         Tables MUST NOT have more than one View Control of a kind,
     *         e.g. a second pagination would be forbidden.
     *   interaction:
     *       1: View Controls used here MUST only affect the table itself.
     *   accessibility:
     *       1: >
     *         The HTML tag enclosing the actual tabular presentation MUST have the
     *         role-attribute "grid".
     *       2: >
     *         The HTML tag enclosing one record MUST have the role-attribute "row".
     *       3: >
     *         A single cell MUST be marked with the role-attribute "gridcell".
     *       4: >
     *         Every single cell (including headers) MUST have a tabindex-attibute
     *         initially set to "-1". When focused, this changes to "0".
     *       5: >
     *         The row with the columns' headers and the area with the actual data
     *         MUST each be enclosed by a tag bearing the role-attribute "rowgroup".
     *       6: >
     *         All (possible) columns of the Table MUST be counted; the result MUST
     *         show in an attribute "aria-colcount" of the tag having the 'role="grid"'.
     *
     * ---
     * @param string     $title
     * @return \ILIAS\UI\Component\Table\Data
     */
    public function data(string $title, ?int $page_size = 50) : Data;

    /**
     * ---
     * description:
     *   purpose: >
     *       Tables display data in a very structured way; columns are essential
     *       in that matter, for they define the nature of one field (aspect) of
     *       the data record.
     *
     *   composition: >
     *       Columns consist of a title and data-cells. Next to the title, according to config,
     *       a Glyph will indicate the ability to sort as well as the current direction.
     *
     *   effect: >
     *       Operating the order-glyphs in the column title will change the records' order.
     *
     * rules:
     *   usage:
     *       1: Columns MUST be used in a Table.
     *       2: Columns MUST have a title.
     *   interaction:
     *       1: Columns optionally MAY be displayed or hidden.
     *       2: Tables MAY be sortable by the data's field associated with the column.
     *   accessibility:
     *       1: >
     *           The HTML enclosing the Columns's title MUST bear the role-attribute "columnheader"
     *       2: >
     *         If the data is sorted by this column, it's header MUST show the direction in the
     *         "aria-sort" attribute ('ascending'|'descending'|'none', if sortable but not applied).
     *       3: >
     *         Every Column MUST have the attribute "aria-colindex" with its position
     *         in all available - not visible - columns of the table.
     * ---
     * @param string     $title
     * @return \ILIAS\UI\Component\Table\Column\Factory
     */
    public function column() : Column\Factory;
}
