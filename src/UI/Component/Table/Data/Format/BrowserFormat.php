<?php

namespace ILIAS\UI\Component\Table\Data\Format;

use ILIAS\UI\Component\Table\Data\Filter\Filter;
use ILIAS\UI\Component\Table\Data\Table;

/**
 * Interface BrowserFormat
 *
 * @package ILIAS\UI\Component\Table\Data\Format
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface BrowserFormat extends Format {

	/**
	 * @param Table $component
	 *
	 * @return string|null
	 */
	public function getInputFormatId(Table $component): ?string;


	/**
	 * @param Table  $component
	 * @param Filter $filter
	 *
	 * @return Filter
	 */
	public function handleFilterInput(Table $component, Filter $filter): Filter;
}
