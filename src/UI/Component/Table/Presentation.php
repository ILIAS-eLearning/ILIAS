<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

/**
 * This describes a Presentation Table
 */
interface Presentation extends \ILIAS\UI\Component\Component
{

    /**
     * Get a table like this with title $title.
     *
     * @param string 	$title
     * @return \Presentation
     */
    public function withTitle($title);

    /**
     * Get the title of the table.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get a table like this with these view controls.
     *
     * @param \ViewControl[] 	$view_controls
     * @return \Presentation
     */
    public function withViewControls(array $view_controls);

    /**
     * Get view controls to be shown in the header of the table.
     *
     * @return ILIAS\UI\Component\ViewControl[]
     */
    public function getViewControls();

    /**
     * Get a table like this with the closure $row_mapping.
     * This closure is called by the renderer upon building a row from
     * a record. The renderer will call the closure with these parameters:
     *
     * $row 		An instance of Component\Table\PresentationRow;
     *				fill the mutator according to your needs and the structure of
     *				your record.
     * $record 		An element of the table's data.
     * 				This is the actually variable part when rendering rows.
     * $ui_factory	You might, e.g., want a descriptive listing or and image
     *				within the content of the row. Use the UI-Factory to build it.
     * $environment When you need auxillary classes or functions to properly render
     * 				the data, this is the place to put it.
     *
     * In short:
     * The closure MUST accept the following parameter
     *   \PresentationRow 	$row
     *   mixed 				$record
     *   \Factory 			$ui_factory
     *   mixed 				$environment
     * The closure MUST return \PresentationRow
     *
     * @param \Closure 	$row_mapping
     * @return \Presentation
     */
    public function withRowMapping(\Closure $row_mapping);


    /**
     * Get the closure to construct row-entries with.
     *
     * @return \Closure
     */
    public function getRowMapping();

    /**
     * Add a list of additional things the mapping-closure needs for processing.
     * These can be virtually anything.
     *
     * @param array<string,mixed> 	$environment
     * @return \Presentation
     */
    public function withEnvironment(array $environment);

    /**
     * Get an array of additionally needed elements to build a data-entry.
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
     * All elements in $records MUST be processable by the mapping-closure.
     *
     * @return array<mixed>
     */
    public function getData();
}
