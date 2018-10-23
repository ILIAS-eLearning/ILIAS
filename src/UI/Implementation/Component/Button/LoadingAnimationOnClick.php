<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component;

/**
 * Implements LoadingAnimationOnClick interface
 * @author killing@leifos.de
 */
trait LoadingAnimationOnClick {

	/**
	 * @var bool
	 */
	protected $loading_animation_on_click = false;

	/**
	 * @inheritdoc
	 */
	public function withLoadingAnimationOnClick($loading_animation_on_click) {
		$this->checkBoolArg("loading_animation_on_click", $loading_animation_on_click);
		$clone = clone $this;
		$clone->loading_animation_on_click = $loading_animation_on_click;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function hasLoadingAnimationOnClick() {
		return $this->loading_animation_on_click;
	}
}
