<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Component\Card as C;
use ILIAS\UI\Component\Icon\Icon;
use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\UI\Component\Dropdown\Dropdown;

class Custom extends Card implements C\Custom {

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
	 * @return Custom
	 */
	public function withProgress(ProgressMeter $a_progressmeter):Custom
	{
		$clone = clone $this;
		$clone->progress = $a_progressmeter;
		return $clone;
	}

	/**
	 * @param $a_certificate Icon
	 * @return Custom
	 */
	public function withCertificate($a_certificate):Custom
	{
		$clone = clone $this;
		$clone->certificate = $a_certificate;
		return $clone;
	}

	/**
	 * @param \ILIAS\UI\Component\Dropdown\Dropdown $dropdown
	 * @return Custom
	 */
	public function withActions($dropdown):Custom
	{
		$clone = clone $this;
		$clone->actions = $dropdown;
		return $clone;
	}
}
