<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAccessibilitySignableDocument
 */
interface ilAccessibilitySignableDocument
{
	/**
	 * @return string
	 */
	public function content() : string;

	/**
	 * @return string
	 */
	public function title() : string;

	/**
	 * @return int
	 */
	public function id() : int;

	/**
	 * @return ilAccessibilityEvaluableCriterion[]
	 */
	public function criteria() : array;
}
