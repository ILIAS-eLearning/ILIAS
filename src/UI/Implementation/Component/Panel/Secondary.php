<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\ViewControl\Pagination as Pagination;
use ILIAS\UI\Component\ViewControl\Sortation as Sortation;

/**
 * Class Secondary
 * @package ILIAS\UI\Implementation\Component\Standard
 */
class Secondary extends Panel implements C\Panel\Secondary
{
	/**
	 * @var Sortation
	 */
	protected $sortation;

	/**
	 * @var Pagination
	 */
	protected $pagination;

	public function withSortation(Sortation $sortation) {
		$clone = clone $this;
		$clone->sortation = $sortation;
		return $clone;
	}

	public function getSortation() : ?Sortation {
		return $this->sortation;
	}

	public function withPagination(Pagination $pagination)
	{
		$clone = clone $this;
		$clone->pagination = $pagination;
		return $clone;
	}

	public function getPagination() : ?Pagination
	{
		return $this->pagination;
	}
}
?>