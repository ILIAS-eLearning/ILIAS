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

use ILIAS\KioskMode\State;
use ILIAS\KioskMode\ControlBuilder;
use ILIAS\UI\Factory;
use ILIAS\KioskMode\URLBuilder;
use ILIAS\UI\Component\Component;

class ilDummyKioskModeView extends ilKioskModeView
{
    protected function getObjectClass(): string
    {
        return'ilObject';
    }

    protected function setObject(ilObject $object)
    {
    }

    protected function hasPermissionToAccessKioskMode(): bool
    {
        return true;
    }

    public function buildInitialState(State $empty_state): State
    {
        return new State();
    }

    public function buildControls(State $state, ControlBuilder $builder)
    {
    }

    public function updateGet(
        State $state,
        string $command,
        ?int $parameter = null
    ): State {
        return new State();
    }

    public function updatePost(State $state, string $command, array $post): State
    {
        return new State();
    }

    public function render(
        State $state,
        Factory $factory,
        URLBuilder $url_builder,
        ?array $post = null
    ): Component {
        return new ILIAS\UI\Implementation\Component\Button\Close();
    }
}
