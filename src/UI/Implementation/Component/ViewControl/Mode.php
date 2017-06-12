<?php

/* Copyright (c) 2016 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;


class Mode implements C\ViewControl\Mode {

	use ComponentHelper;

	protected $labeled_actions;
	protected $active;

	public function __construct($labelled_actions)
	{
		$this->labeled_actions = $this->toArray($labelled_actions);
	}

	public function withActive($label)
	{
		$this->active = $this->checkStringArg("label", $label);
	}

	public function getActive()
	{
		return $this->active;
	}

	public function getLabelledActions()
	{
		return $this->labeled_actions;
	}

}
