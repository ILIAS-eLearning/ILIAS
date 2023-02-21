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

namespace ILIAS\UI\Component\Input\ViewControl;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Component\Triggerer;
use ILIAS\UI\Component\Signal;

/**
 * This describes the basis of all View Control Inputs.
 */
interface ViewControl extends Input, Triggerer
{
    /**
     * When a View Control becomes part of a View Control Input Container,
     * the Container will amend a Signal to the Component; this Signal MUST
     * be triggered when operating the Control.
     */
    public function withOnChange(Signal $signal): self;

    /**
     * Trigger this signal when the control is being operated.
     */
    public function getOnChangeSignal(): ?Signal;

    public function getLabel(): string;
    public function withLabel(string $label): self;
    public function isDisabled(): bool;
    public function withDisabled(bool $is_disabled): self;
}
