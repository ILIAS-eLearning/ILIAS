<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */
namespace ILIAS\UI\Component\ViewControl;

use \ILIAS\UI\Component as C;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Triggerer;
/**
 * This describes a Pagination Control
 */
interface Pagination extends C\Component, JavaScriptBindable, Triggerer {

	/**
	 * Initialize with the total amount of entries
	 * of the controlled data-list
	 *
	 * @param 	int 	$total
	 *
	 * @return \Pagination
	 */
	public function withTotalEntries($total);

	/**
	 * Set the amount of entries per page.
	 *
	 * @param 	int 	$size
	 *
	 * @return \Pagination
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
	 * @return \Pagination
	 */
	public function withCurrentPage($page);

	/**
	 * Get the data's offset according to current page and page size.
	 *
	 * @return int
	 */
	public function getOffset();



}
