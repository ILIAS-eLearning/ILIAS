<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\KioskMode\State;
use ILIAS\KioskMode\URLBuilder;
use ILIAS\KioskMode\View;
use ILIAS\UI;

/**
 * Class ilContentPageKioskModeView
 */
class ilContentPageKioskModeView implements View
{
	/**
	 * @inheritDoc
	 */
	public function buildInitialState(State $empty_state): State
	{
		// TODO: Implement buildInitialState() method.
	}

	/**
	 * @inheritDoc
	 */
	public function buildControls(State $state, \ILIAS\KioskMode\ControlBuilder $builder)
	{
		// TODO: Implement buildControls() method.
	}

	/**
	 * @inheritDoc
	 */
	public function updateGet(State $state, string $command, int $param = null): State
	{
		// TODO: Implement updateGet() method.
	}

	/**
	 * @inheritDoc
	 */
	public function updatePost(State $state, string $command, array $post): State
	{
		// TODO: Implement updatePost() method.
	}

	/**
	 * @inheritDoc
	 */
	public function render(
		State $state,
		UI\Factory $factory,
		URLBuilder $url_builder,
		array $post = null
	): UI\Component\Component {
		// TODO: Implement render() method.
	}
}