<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\ViewControl\ViewControl as BaseControl;

/**
 * This describes a Field Selection View Control
 */
interface FieldSelection extends BaseControl
{
    const DEFAULT_DROPDOWN_LABEL = 'selection';
    const DEFAULT_BUTTON_LABEL = 'refresh';

    public function getDropdownLabel(): string;
    public function getButtonLabel(): string;
    public function getInput(): Input;

    /**
     * This is an internal signal, used to submit the current choice
     */
    public function getSubmissionTrigger(): Signal;
    public function withResetSignals(): FieldSelection;
}
