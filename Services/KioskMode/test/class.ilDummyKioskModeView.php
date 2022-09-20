<?php

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

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
        int $parameter = null
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
        array $post = null
    ): Component {
        return new ILIAS\UI\Implementation\Component\Button\Close();
    }
}
