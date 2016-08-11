<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

/**
 * This describes how a Report could be modified during construction of UI.
 */
interface Report extends Panel {
	/**
	 * @param string $title Title of the Report
	 * @return \ILIAS\UI\Component\Panel\Report
	 */
	public function withTitle($title);

	/**
	 * @return string $title Title of the Report
	 */
	public function getTitle();

	/**
	 * @param \ILIAS\UI\Component\Panel\Sub[] $sub_panels Sub Panels used to structure the report.
	 * @return \ILIAS\UI\Component\Panel\Report
	 */
	public function withSubPanels($sub_panels);

	/**
	 * @return \ILIAS\UI\Component\Panel\Sub[]
	 */
	public function getSubPanels();
}
