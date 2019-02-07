<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

use ILIAS\UI\Component\ViewControl\Pagination as Pagination;
use ILIAS\UI\Component\ViewControl\Sortation as Sortation;

/**
 * This describes a Secondary Panel.
 * TODO don't assume that ILIAS 6.0 will get as minimum PHP 7.1 version.
 * TODO remove all nullable types
 */
interface Secondary extends Panel {
	public function withSortation(Sortation $options);
	public function getSortation() : ?Sortation;
	public function withPagination(Pagination $pagination);
	public function getPagination() : ?Pagination;
}
