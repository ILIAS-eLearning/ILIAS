<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace ILIAS\KioskMode;

use ILIAS\UI;

/**
 * A kiosk mode view on a certain object. See README/Architecture for further
 * details and README/Implementing a Provider for further directions about
 * implementation.
 */
interface View {
	/**
	 * Build an initial state based on the Provided empty state.
	 */
	public function buildInitialState(State $empty_state) : State;

	/**
	 * Construct the controls for the view based on the current state.
	 */
	public function buildControls(State $state, ControlBuilder $builder) : null;

	/**
	 * Update the state based on the provided command.
	 *
	 * Commands and parameters are defined by the view in `buildControl`.
	 */
	public function updateGet(State $state, string $command, int $param = null) : State;

	/**
	 * Update the state and the object based on the provided command and post-data.
	 *
	 * Commands are defined via the post_link-closure provided to render.
	 */
	public function updatePost(State $state, string $command, array $post) : State;

	/**
	 * Render a state using the ui-factory and URLs from the builder.
	 */
	public function render(
		State $state,
		UI\Factory $factory,
		URLBuilder $post_link,
		array $post = null
	) : UI\Component\Component;
}
