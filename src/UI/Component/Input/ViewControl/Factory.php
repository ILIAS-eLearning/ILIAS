<?php

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\ViewControl;

/**
 * This describes the factory for (view-)controls.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      Field Selection is used to limit a visualization of data to a choice of aspects,
     *      e.g. in picking specific columns of a table or fields of a diagram.
     *   composition: >
     *      A Field Selection uses a Multiselect Input wrapped in a dropdown.
     *      A Standard Button is used to submit the user's choice.
     *   effect: >
     *      When operating the dropdown, the Multiselect is shown.
     *      The dropdown is being closed upon submission or by clicking outside of it.
     * ---
     * @param array<string,string> $options
     * @param string $label
     *
     * @return \ILIAS\UI\Component\Input\ViewControl\FieldSelection
     */
    public function fieldSelection(
        array $options,
        string $label = FieldSelection::DEFAULT_DROPDOWN_LABEL,
        string $button_label = FieldSelection::DEFAULT_BUTTON_LABEL
    ) : FieldSelection;
}
