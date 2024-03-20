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

namespace ILIAS\UI\Component\ViewControl;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Triggerer;

/**
 * This describes a Sortation Control
 */
interface Sortation extends Component, JavaScriptBindable, Triggerer
{
    /**
     * Set the prefix of the label
     */
    public function withLabelPrefix(string $label_prefix): self;

    /**
     * Get a Sortation with this target-url.
     * Shy-Buttons in this control will link to this url
     * and add $parameter_name with the selected value.
     */
    public function withTargetURL(string $url, string $parameter_name): self;

    /**
     * Get the url this instance should trigger.
     */
    public function getTargetURL(): ?string;

    /**
     * Get the identifier of this instance.
     */
    public function getParameterName(): string;

    /**
     * Get the sorting-options.
     *
     * @return 	array<string,string> 	value=>title
     */
    public function getOptions(): array;

    /**
     * Get a component like this, triggering a signal of another component.
     *
     * @param Signal $signal A signal of another component
     */
    public function withOnSort(Signal $signal): self;

    /**
     * Get the Signal for the selection of a option
     */
    public function getSelectSignal(): Signal;

    /**
     * Set the selected option.
     */
    public function withSelected(string $selected_option): self;
}
