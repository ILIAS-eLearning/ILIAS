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
use ILIAS\Data\Order;

class Sortation extends ViewControl implements VCInterface\Sortation
{
    public const DEFAULT_DROPDOWN_LABEL = 'sortation';

    protected Signal $internal_selection_signal;
    protected string $aspect;
    protected string $direction;

    public function __construct(
        DataFactory $data_factory,
        Refinery $refinery,
        SignalGeneratorInterface $signal_generator,
        protected array $options,
        string $label
    ) {
        parent::__construct($data_factory, $refinery, $label);
        $this->internal_selection_signal = $signal_generator->create();
        $this->operations[] = $this->getOrderTransform();
    }

    protected function getDefaultValue(): string
    {
        return $this->value ?? current(array_keys($this->getOptions()));
    }

    protected function isClientSideValueOk($value): bool
    {
        return is_null($value) ||
            is_a(
                $this->getOrderTransform()->transform($value),
                Order::class
            ) && array_key_exists($value, $this->getOptions());
    }

    protected function getOrderTransform(): Transformation
    {
        return $this->refinery->custom()->transformation(
            fn ($v) => $this->data_factory->order(...explode(':', $v))
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
}
