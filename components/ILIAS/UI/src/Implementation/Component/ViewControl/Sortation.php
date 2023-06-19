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

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\Signal;

class Sortation implements C\ViewControl\Sortation
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    protected Signal $select_signal;
    protected string $label = '';
    protected ?string $label_prefix = null;
    protected ?string $target_url = null;
    protected string $parameter_name = "sortation";
    protected ?string $active = null;
    protected ?string $selected = null;

    /**
     * @param array<string,string> $options
     */
    public function __construct(
        protected array $options,
        protected SignalGeneratorInterface $signal_generator
    ) {
        $this->initSignals();
    }

    /**
     * @inheritdoc
     */
    public function withResetSignals(): C\ViewControl\Sortation
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }

    /**
     * Set the signals for this component
     */
    protected function initSignals(): void
    {
        $this->select_signal = $this->signal_generator->create();
    }

    /**
     * @inheritdoc
     */
    public function withLabel(string $label): C\ViewControl\Sortation
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function withLabelPrefix(string $label_prefix): C\ViewControl\Sortation
    {
        $clone = clone $this;
        $clone->label_prefix = $label_prefix;
        return $clone;
    }

    public function getLabelPrefix(): ?string
    {
        return $this->label_prefix;
    }

    /**
     * @inheritdoc
     */
    public function withTargetURL(string $url, string $parameter_name): C\ViewControl\Sortation
    {
        $this->checkStringArg("url", $url);
        $this->checkStringArg("parameter_name", $parameter_name);
        $clone = clone $this;
        $clone->target_url = $url;
        $clone->parameter_name = $parameter_name;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getTargetURL(): ?string
    {
        return $this->target_url;
    }

    /**
     * @inheritdoc
     */
    public function getParameterName(): string
    {
        return $this->parameter_name;
    }

    /**
     * @inheritdoc
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     */
    public function withOnSort(Signal $signal): C\ViewControl\Sortation
    {
        return $this->withTriggeredSignal($signal, 'sort');
    }

    /**
     * @inheritdoc
     */
    public function getSelectSignal(): Signal
    {
        return $this->select_signal;
    }

    public function withSelected(?string $selected_option): self
    {
        $clone = clone $this;
        $clone->selected = $selected_option;
        return $clone;
    }

    public function getSelected(): ?string
    {
        return $this->selected;
    }
}
