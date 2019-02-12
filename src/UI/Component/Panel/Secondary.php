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
	 * Set sortation options.
	 * @param \ILIAS\UI\Component\ViewControl\Sortation $options
	 * @return Secondary
	 */
	public function withSortation(Sortation $options);

	/**
	 * Get sortation options.
	 * @return Sortation | null
	 */
	public function getSortation();

	/**
	 * @param Pagination $pagination
	 * @return Secondary
	 */
	public function withPagination(Pagination $pagination);

	/**
	 * @return Pagination | null
	 */
	public function getPagination();

	/**
	 * @param Section $section
	 * @return Secondary
	 */
	public function withSection(Section $section);

	/**
	 * @return Section | null
	 */
	public function getSection();
}
