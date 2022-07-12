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

use ILIAS\UI\Component\ViewControl\HasViewControls;
use Closure;

/**
 * This describes a Presentation Table
 */
interface Presentation extends Table, HasViewControls
{
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
     * $environment When you need auxiliary classes or functions to properly render
     * 				the data, this is the place to put it.
     *
     * In short:
     * The closure MUST accept the following parameter
     *   \PresentationRow 	$row
     *   mixed 				$record
     *   \Factory 			$ui_factory
     *   mixed 				$environment
     * The closure MUST return \PresentationRow
     */
    public function withRowMapping(Closure $row_mapping) : Presentation;

    /**
     * Get the closure to construct row-entries with.
     */
    public function getRowMapping() : Closure;

    /**
     * Add a list of additional things the mapping-closure needs for processing.
     * These can be virtually anything.
     *
     * @param array<string,mixed> 	$environment
     */
    public function withEnvironment(array $environment) : Presentation;

    /**
     * Get an array of additionally needed elements to build a data-entry.
     *
     * @return array<string,mixed>
     */
    public function getEnvironment() : array;

    /**
     * Fill a recordset into the table.
     * All elements in $records MUST be processable by the mapping-closure.
     *
     * @param array<mixed> 	$records
     */
    public function withData(array $records) : Presentation;

    /**
     * Get the recordset of this table.
     * All elements in $records MUST be processable by the mapping-closure.
     *
     * @return array<mixed>
     */
    public function getData() : array;
}
