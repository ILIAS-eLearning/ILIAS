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
use ILIAS\UI\Implementation\Component\Input\InputGroup;

class Sortation extends ViewControl implements VCInterface\Sortation
{
    use InputGroup;

    public const DEFAULT_DROPDOWN_LABEL = 'sortation';

    protected Signal $internal_selection_signal;
    protected string $aspect;
    protected string $direction;

    public function __construct(
        FieldFactory $field_factory,
        DataFactory $data_factory,
        Refinery $refinery,
        SignalGeneratorInterface $signal_generator,
        protected array $options,
        string $label
    ) {
        $opts = array_values($options);
        $this->checkArgListElements('options', $opts, [Order::class]);

        $this->inputs = [
            $field_factory->hidden(), //aspect
            $field_factory->hidden(), //direction
        ];
        parent::__construct($data_factory, $refinery, $label);
        $this->internal_selection_signal = $signal_generator->create();
        $this->operations[] = $this->getOrderTransform();
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
}
