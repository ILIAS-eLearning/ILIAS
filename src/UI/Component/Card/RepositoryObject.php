<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Card;
use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Component\Icon\Icon;

/**
 * Interface Custom
 * @package ILIAS\UI\Component\Card
 */
interface RepositoryObject extends Standard {

	/**
	 *
	 * Get a Custom card like this, but with an additional UI Progressmeter object
	 * @param ProgressMeter $progressmeter
	 * @return RepositoryObject
	 */
	public function withProgress($progressmeter);

	/**
	 * Get a Custom card like this, but with an additional certificate outlined icon
	 * @param Icon $certificate_icon
	 * @return Custom
	 */
	public function withCertificate($certificate_icon);

	/**
	 * Get a Custom card like this, but with an additional UI Dropdown object
	 * @param $dropdown Dropdown
	 * @return RepositoryObject
	 */
	public function withActions($dropdown);
}
