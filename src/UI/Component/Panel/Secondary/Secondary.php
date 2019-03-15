<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Secondary;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\ViewControl\Pagination as Pagination;
use ILIAS\UI\Component\ViewControl\Section as Section;
use ILIAS\UI\Component\ViewControl\Sortation as Sortation;

/**
 * This describes a Secondary Panel.
 */
interface Secondary extends C\Component
{
	/**
	 * Set Sortation view controller.
	 * @param Sortation $sortation
	 * @return Secondary
	 */
	public function withSortation(Sortation $sortation) : Secondary;

	/**
	 * Get Sortation view controller or null
	 * @return Sortation | null
	 */
	public function getSortation() : ?Sortation;

	/**
	 * Set Pagination
	 * @param Pagination $pagination
	 * @return Secondary
	 */
	public function withPagination(Pagination $pagination) : Secondary;

	/**
	 * Get Pagination view controller or null
	 * @return Pagination | null
	 */
	public function getPagination() : ?Pagination;

	/**
	 * Set Section view controller
	 * @param Section $section
	 * @return Secondary
	 */
	public function withSection(Section $section) : Secondary;

	/**
	 * Get Section view controller or null
	 * @return Section | null
	 */
	public function getSection(): ?Section;
}
