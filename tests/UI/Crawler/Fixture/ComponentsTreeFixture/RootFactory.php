<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Interfaces;

use ILIAS\UI\Component as C;
/**
 * Some Random Comment
 */
interface ProperEntry {
	/**
	 * ---
	 * description:
	 * rules:
	 * ---
	 *
	 * @return  tests\UI\Crawler\Fixture\ComponentsTreeFixture\Component1\Factory
	 */
	public function component1();

	/**
	 * ---
	 * description:
	 * rules:
	 * ---
	 *
	 * @return  tests\UI\Crawler\Fixture\ComponentsTreeFixture\Component2\Factory
	 */
	public function component2();
}