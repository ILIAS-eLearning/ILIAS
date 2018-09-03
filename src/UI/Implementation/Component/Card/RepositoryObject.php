<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Component\Card as C;
use ILIAS\UI\Component\Icon\Icon;
use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\UI\Component\Dropdown\Dropdown;

class RepositoryObject extends Standard implements C\RepositoryObject {

	/**
	 * @var ProgressMeter
	 */
	protected $progress;

	/**
	 * @var Icon
	 */
	protected $certificate;

	/**
	 * @var Dropdown
	 */
	protected $actions;

	/**
	 * @param ProgressMeter $a_progressmeter
	 * @return RepositoryObject
	 */
	public function withProgress(ProgressMeter $a_progressmeter):Custom
	{
		$clone = clone $this;
		$clone->progress = $a_progressmeter;
		return $clone;
	}

	/**
	 * @param $a_certificate Icon
	 * @return RepositoryObject
	 */
	public function withCertificate($a_certificate):RepositoryObject
	{
		$clone = clone $this;
		$clone->certificate = $a_certificate;
		return $clone;
	}

	/**
	 * @param \ILIAS\UI\Component\Dropdown\Dropdown $dropdown
	 * @return RepositoryObject
	 */
	public function withActions($dropdown):RepositoryObject
	{
		$clone = clone $this;
		$clone->actions = $dropdown;
		return $clone;
	}
}
