<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilPDObjectsRenderer
 */
interface ilPDObjectsRenderer
{
	/**
	 * @param ilPDSelectedItemsBlockGroup[] $groupedItems
	 * @param bool $showHeader
	 * @return string
	 */
	public function render(array $groupedItems, bool $showHeader)  : string;
}