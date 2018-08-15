<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceSignableDocument
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceSignableDocument
{
	/**
	 * @return string
	 */
	public function getText(): string;

	/**
	 * @return string
	 */
	public function getTitle(): string;

	/**
	 * @return int
	 */
	public function getId(): int;

	/**
	 * @return \ilTermsOfServiceEvaluableCriterion[]
	 */
	public function getCriteria(): array;
}
