<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\ViewControl\Pagination as Pagination;
use ILIAS\UI\Component\ViewControl\Section as Section;
use ILIAS\UI\Component\ViewControl\Sortation as Sortation;

/**
 * Class Secondary
 * @package ILIAS\UI\Implementation\Component\Standard
 */
class Secondary extends Panel implements C\Panel\Secondary
{
	/**
	 * @var Sortation|null
	 */
	protected $sortation = null;

	/**
	 * @var Pagination|null
	 */
	protected $pagination = null;

	/**
	 * @var Section|null
	 */
	protected $section = null;

	/**
	 * @param Sortation $sortation
	 * @return C\Panel\Secondary
	 */
	public function withSortation(Sortation $sortation): C\Panel\Secondary
	{
		$clone = clone $this;
		$clone->sortation = $sortation;
		return $clone;
	}

	/**
	 * @return Sortation|null
	 */
	public function getSortation(): ?Sortation
	{
		return $this->sortation;
	}

	/**
	 * @param Pagination $pagination
	 * @return C\Panel\Secondary
	 */
	public function withPagination(Pagination $pagination): C\Panel\Secondary
	{
		$clone = clone $this;
		$clone->pagination = $pagination;
		return $clone;
	}

	/**
	 * @return Pagination|null
	 */
	public function getPagination(): ?Pagination
	{
		return $this->pagination;
	}

	/**
	 * @param Section $section
	 * @return C\Panel\Secondary|Secondary
	 */
	public function withSection(Section $section): C\Panel\Secondary
	{
		$clone = clone $this;
		$clone->section = $section;
		return $clone;
	}

	/**
	 * @return Section|null
	 */
	public function getSection(): ?Section
	{
		return $this->section;
	}
}
?>