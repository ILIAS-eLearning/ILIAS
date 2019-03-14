<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Secondary;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\ViewControl\Pagination as Pagination;
use ILIAS\UI\Component\ViewControl\Section as Section;
use ILIAS\UI\Component\ViewControl\Sortation as Sortation;

/**
 * Class Secondary
 * @package ILIAS\UI\Implementation\Component\Standard
 */
abstract class Secondary implements C\Panel\Secondary\Secondary
{
	use ComponentHelper;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var \ILIAS\UI\Component\Dropdown\Standard
	 */
	protected $actions = null;

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
	 * Gets the secondary panel title
	 *
	 * @return string
	 */
	public function getTitle() : string
	{
		return $this->title;
	}

	/**
	 * Sets the action drop down to be displayed on the right of the title
	 * @param C\Dropdown\Standard $actions
	 * @return Secondary
	 */
	public function withActions(C\Dropdown\Standard $actions) : Secondary
	{
		$clone = clone $this;
		$clone->actions = $actions;
		return $clone;
	}

	/**
	 * Gets the action drop down to be displayed on the right of the title
	 * @return C\Dropdown\Standard | null
	 */
	public function getActions() : ?C\Dropdown\Standard
	{
		return $this->actions;
	}

	/**
	 * @param Sortation $sortation
	 * @return C\Panel\Secondary\Secondary
	 */
	public function withSortation(Sortation $sortation) : C\Panel\Secondary\Secondary
	{
		$clone = clone $this;
		$clone->sortation = $sortation;
		return $clone;
	}

	/**
	 * @return Sortation|null
	 */
	public function getSortation() : ?Sortation
	{
		return $this->sortation;
	}

	/**
	 * @param Pagination $pagination
	 * @return C\Panel\Secondary\Secondary
	 */
	public function withPagination(Pagination $pagination) : C\Panel\Secondary\Secondary
	{
		$clone = clone $this;
		$clone->pagination = $pagination;
		return $clone;
	}

	/**
	 * @return Pagination|null
	 */
	public function getPagination() : ?Pagination
	{
		return $this->pagination;
	}

	/**
	 * @param Section $section
	 * @return C\Panel\Secondary|Secondary
	 */
	public function withSection(Section $section) : C\Panel\Secondary\Secondary
	{
		$clone = clone $this;
		$clone->section = $section;
		return $clone;
	}

	/**
	 * @return Section|null
	 */
	public function getSection() : ?Section
	{
		return $this->section;
	}
}
?>