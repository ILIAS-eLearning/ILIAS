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

class FieldSelection extends ViewControl implements VCInterface\FieldSelection
{
    public const DEFAULT_DROPDOWN_LABEL = 'field selection';
    public const DEFAULT_BUTTON_LABEL = 'refresh';

    protected Signal $internal_selection_signal;

    public function __construct(
        DataFactory $data_factory,
        Refinery $refinery,
        SignalGeneratorInterface $signal_generator,
        protected array $options,
        protected string $label,
        protected string $button_label
    ) {
        parent::__construct($data_factory, $refinery, $label);
        $this->internal_selection_signal = $signal_generator->create();
        $this->operations[] = $this->getArrayTransform();
    }

    protected function getDefaultValue(): string
    {
        return $this->value ?? '';
    }

    protected function isClientSideValueOk($value): bool
    {
        return is_null($value) ||
            array_filter(
                $this->getArrayTransform()->transform($value),
                fn ($v) => !array_key_exists($v, $this->getOptions())
            ) === [];
    }

    protected function getArrayTransform(): Transformation
    {
        return $this->refinery->custom()->transformation(
            fn ($v) => array_filter(explode(',', $v))
        );
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
}
