<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace ILIAS\KioskMode;

use ILIAS\UI;

/**
 * A kiosk mode view on a certain object. See README#Architecture for further
 * details.
 */
interface View {
	/**
	 * Construct the controls for the view based on the current state.
	 */
	public function buildControls(State $state, ControlBuilder $builder) : null;

	/**
	 * Update the state based on the provided command.
	 *
	 * Commands and parameters are defined by the view in `buildControl`.
	 */
	public function update(State $state, string $command, int $param = null) : State;

	/**
	 * Render a state using the ui-factory.
	 */
	public function render(State $state, UI\Factory $factory) : UI\Component\Component;
}
