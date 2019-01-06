<?php

/* Copyright (c) 2019 Björn Heyser <info@bjoernheyser.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

use ILIAS\UI\Component\Component;

/**
 * This describes how a data-panel could be modified during construction of UI.
 */
interface Data extends \ILIAS\UI\Component\Component {
	
	/**
	 * Gets the title of the panel
	 *
	 * @return string $title Title of the Data-Panel
	 */
	public function getTitle();
	
	/**
	 * Gets the content to be displayed inside the data-panel
	 *
	 * @return Component[]|Component
	 */
	public function getContent();
	
	/**
	 * Adds data entry to be displayed within the data-panel
	 *
	 * @param Component $dataLabel
	 * @param Component $dataValue
	 * @return \ILIAS\UI\Component\Panel\Data
	 */
	public function withAdditionalEntry(Component $dataLabel, Component $dataValue);
	
	/**
	 * Returns the entries as array (Entries[ Entry[ Label, Value ] ])
	 *
	 * @return array
	 */
	public function getEntries();
}
