<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Breadcrumbs;
use ILIAS\UI\Component\Breadcrumbs as B;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Breadcrumbs implements B\Breadcrumbs {
	use ComponentHelper;

	/**
	 * @var Button\Shy[] 	list of shy-buttons
	 */
	protected $crumbs;

	public function __construct($crumbs) {
		$types = array(\ILIAS\UI\Component\Button\Shy::class);
		$this->checkArgListElements("crumbs", $crumbs, $types);
		$this->crumbs = $crumbs;
    }


	/**
	 * @inheritdoc
	 */
	public function getCrumbs() {
		return $this->crumbs;
	}

	/**
	 * @inheritdoc
	 */
	public function withAppendedEntry($crumb) {
		$this->checkArgInstanceOf("crumb", $crumb, \ILIAS\UI\Component\Button\Shy::class);
		$clone = clone $this;
		$clone->crumbs[] = $crumb;
		return $clone;
    }

}
