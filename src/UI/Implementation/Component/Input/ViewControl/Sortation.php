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
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component\Input\NameSource;

class Sortation extends ViewControlInput implements VCInterface\Sortation, HasInputGroup
{
    use GroupDecorator;

    protected Signal $internal_selection_signal;
    protected string $aspect;
    protected string $direction;

    /**
     * @var array<string, Order>
     */
    protected array $options;

    /**
     * @param array<string, Order> $options
     */
    public function __construct(
        FieldFactory $field_factory,
        DataFactory $data_factory,
        Refinery $refinery,
        SignalGeneratorInterface $signal_generator,
        array $options
    ) {
        parent::__construct($data_factory, $refinery);

        $aspects = array_keys($options);
        $this->checkArgListElements('options', $aspects, 'string');
        $this->checkArgListElements('options', $options, [Order::class]);
        $this->options = $options;

        $this->setInputGroup($field_factory->group([
            $field_factory->hidden(), //aspect
            $field_factory->hidden(), //direction
        ])->withAdditionalTransformation($this->getOrderTransform()));

        $this->internal_selection_signal = $signal_generator->create();
    }

    protected function getOrderTransform(): Transformation
    {
        return $this->refinery->custom()->transformation(
            function ($v): Order {
                list($aspect, $direction) = $v;

                if (is_null($aspect) || $aspect === '') {
                    $options = array_values($this->getOptions());
                    $option = array_shift($options);
                    return $option;
                }
                return $this->data_factory->order($aspect, $direction);
            }
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

    public function withAriaLabel(string $label): self
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }
}
