<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\KioskMode;

use ILIAS\UI;

/**
 * A kiosk mode view on a certain object. See README/Architecture for further
 * details and README/Implementing a Provider for further directions about
 * implementation.
 */
interface View
{
    /**
     * Build an initial state based on the Provided empty state.
     */
    public function buildInitialState(State $empty_state): State;

    /**
     * Construct the controls for the view based on the current state.
     *
     * The interaction with the controls build via the ControlBuilder will always
     * be delegated to updateGet.
     */
    public function buildControls(State $state, ControlBuilder $builder);

    /**
     * Update the state based on the provided command.
     *
     * If the update was caused by a control with a $parameter (see ControlBuilder)
     * that value is passed to $parameter here.
     *
     * Commands and parameters are defined by the view in `buildControl`.
     */
    public function updateGet(State $state, string $command, ?int $parameter = null): State;

    /**
     * Update the state and the object based on the provided command and post-data.
     *
     * Commands are defined via the url-builder provided to render.
     *
     * The POSTed data will be passed via $post.
     */
    public function updatePost(State $state, string $command, array $post): State;

    /**
     * Render a state using the ui-factory and URLs from the builder.
     *
     * Links inside the content that should lead to kiosk-mode-view again (forms)
     * must be created via the URLBuilder.
     *
     * If data was POSTed to the kiosk-mode-view, it will be passed via $post.
     */
    public function render(
        State $state,
        UI\Factory $factory,
        URLBuilder $url_builder,
        ?array $post = null
    ): UI\Component\Component;
}
