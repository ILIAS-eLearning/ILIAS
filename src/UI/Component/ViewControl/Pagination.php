<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */
namespace ILIAS\UI\Component\ViewControl;

use \ILIAS\UI\Component as C;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Triggerer;

/**
 * This describes a Pagination Control
 */
interface Pagination extends C\Component, JavaScriptBindable, Triggerer
{

    /**
     * Get a Pagination with this target-url.
     * Shy-Buttons in this control will link to this url
     * and add $parameter_name with the selected value.
     *
     * @param 	string 	$url
     * @param 	string 	$parameter_name
     *
     * @return Pagination
     */
    public function withTargetURL($url, $parameter_name);

    /**
     * Get the url this instance should trigger.
     *
     * @return 	string
     */
    public function getTargetURL();

    /**
     * Get the parameter this instance uses.
     *
     * @return 	string
     */
    public function getParameterName();

    /**
     * Initialize with the total amount of entries
     * of the controlled data-list
     *
     * @param 	int 	$total
     *
     * @return Pagination
     */
    public function withTotalEntries($total);

    /**
     * Set the amount of entries per page.
     *
     * @param 	int 	$size
     *
     * @return Pagination
     */
    public function withPageSize($size);

    /**
     * Get the numebr of entries per page.
     *
     * @return int
     */
    public function getPageSize();

    /**
     * Set the selected page.
     *
     * @param 	int 	$page
     *
     * @return Pagination
     */
    public function withCurrentPage($page);

    /**
     * Get the currently slected page.
     *
     * @return int
     */
    public function getCurrentPage();

    /**
     * Get the data's offset according to current page and page size.
     *
     * @return int
     */
    public function getOffset();

    /**
     * Register a signal with the control.
     *
     * @param C\Signal $signal
     *
     * @return Pagination
     */
    public function withOnSelect(C\Signal $signal);

    /**
     * Calculate the total number of pages.
     *
     * @return int
     */
    public function getNumberOfPages();

    /**
     * Layout; define, how many page-options are shown (max).
     *
     * @param int 	$amount
     *
     * @return Pagination
     */
    public function withMaxPaginationButtons($amount);

    /**
     * Get the maximum amount of page-entries (not records per page!)
     * to be shown.
     *
     * @return int
     */
    public function getMaxPaginationButtons();

    /**
     * Layout; when number of page-entries reaches $amount,
     * the options will be rendered as dropdown.
     *
     * @param int 	$amount
     *
     * @return Pagination
     */
    public function withDropdownAt($amount);

    /**
     * Below this value, the options are directly rendered as shy-buttons,
     * on and above this value a dropdown is being used.
     *
     * @return int
     */
    public function getDropdownAt();
}
