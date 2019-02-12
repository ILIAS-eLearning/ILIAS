<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

use ILIAS\UI\Component\ViewControl\Pagination as Pagination;
use ILIAS\UI\Component\ViewControl\Section as Section;
use ILIAS\UI\Component\ViewControl\Sortation as Sortation;

/**
 * This describes a Secondary Panel.
 */
interface Secondary extends Panel {
	/**
	 * Set Sortation view controller.
	 * @param \ILIAS\UI\Component\ViewControl\Sortation $sortation
	 * @return Secondary
	 */
	public function withSortation(Sortation $sortation);

	/**
	 * Get Sortation view controller or null
	 * @return Sortation | null
	 */
	public function getSortation();

	/**
	 * Set Pagination
	 * @param Pagination $pagination
	 * @return Secondary
	 */
	public function withPagination(Pagination $pagination);

	/**
	 * Get Pagination view controller or null
	 * @return Pagination | null
	 */
	public function getPagination();

	/**
	 * Set Section view controller
	 * @param Section $section
	 * @return Secondary
	 */
	public function withSection(Section $section);

	/**
	 * Get Section view controller or null
	 * @return Section | null
	 */
	public function getSection();
}
