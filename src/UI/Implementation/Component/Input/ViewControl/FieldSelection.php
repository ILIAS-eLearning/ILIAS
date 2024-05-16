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

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\ViewControl as VCInterface;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Implementation\Component\Input\Input;

class FieldSelection extends ViewControlInput implements VCInterface\FieldSelection
{
    protected Signal $internal_selection_signal;
    protected string $button_label = '';
    protected array $options;

    public function __construct(
        DataFactory $data_factory,
        Refinery $refinery,
        SignalGeneratorInterface $signal_generator,
        array $options
    ) {
        parent::__construct($data_factory, $refinery);
        $this->options =$options;
        $this->internal_selection_signal = $signal_generator->create();
    }

    protected function isClientSideValueOk($value): bool
    {
        return is_null($value) || is_array($value);
    }

    public function getInternalSignal(): Signal
    {
        return $this->internal_selection_signal;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getButtonLabel(): string
    {
        return $this->button_label;
    }

    public function withButtonLabel(string $button_label): self
    {
        $clone = clone $this;
        $clone->button_label = $button_label;
        return $clone;
    }

    public function withAriaLabel(string $label): self
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }
}
