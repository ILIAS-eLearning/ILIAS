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
 
namespace ILIAS\UI\Component\ViewControl;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Triggerer;
use ILIAS\Data\Range;

/**
 * This describes a Pagination Control
 */
interface Pagination extends Component, JavaScriptBindable, Triggerer
{
    public const DEFAULT_DROPDOWN_LABEL = 'pagination_label_x_of_y';

    /**
     * Get a Pagination with this target-url.
     * Shy-Buttons in this control will link to this url
     * and add $parameter_name with the selected value.
     */
    public function withTargetURL(string $url, string $parameter_name) : Pagination;

    /**
     * Get the url this instance should trigger.
     */
    public function getTargetURL() : ?string;

    /**
     * Get the parameter this instance uses.
     */
    public function getParameterName() : string;

    /**
     * Initialize with the total amount of entries
     * of the controlled data-list
     */
    public function withTotalEntries(int $total) : Pagination;

    /**
     * Set the amount of entries per page.
     */
    public function withPageSize(int $size) : Pagination;

    /**
     * Get the number of entries per page.
     */
    public function getPageSize() : int;

    /**
     * Set the selected page.
     */
    public function withCurrentPage(int $page) : Pagination;

    /**
     * Get the currently selected page.
     */
    public function getCurrentPage() : int;

    /**
     * Register a signal with the control.
     */
    public function withOnSelect(Signal $signal) : Pagination;

    /**
     * Calculate the total number of pages.
     */
    public function getNumberOfPages() : int;

    /**
     * Layout; define, how many page-options are shown (max).
     */
    public function withMaxPaginationButtons(int $amount) : Pagination;

    /**
     * Get the maximum amount of page-entries (not records per page!)
     * to be shown.
     */
    public function getMaxPaginationButtons() : ?int;

    /**
     * Layout; when number of page-entries reaches $amount,
     * the options will be rendered as dropdown.
     */
    public function withDropdownAt(int $amount) : Pagination;

    /**
     * Below this value, the options are directly rendered as shy-buttons,
     * on and above this value a dropdown is being used.
     */
    public function getDropdownAt() : ?int;

    /**
     * Layout; set the label for dropdown.
     * If need (or wish) arises, you can give a template-string
     * with variables for current and total page numbers.
     * The string will be filled with sprintf($template, $current_page, $total_pages),
     * so, e.g.: "page %1$d of %2$d" or "from %2$d, this is %1$d".
     */
    public function withDropdownLabel(string $template) : Pagination;

    /**
     * Get the template for the label of the dropdown.
     */
    public function getDropdownLabel() : string;

    /**
     * Get the default label (for comparison, mainly) - the default label
     * will be translated, a custom label will not.
     */
    public function getDefaultDropdownLabel() : string;

    /**
     * Get the current number of entries on this page.
     */
    public function getRange() : ?Range;
}
