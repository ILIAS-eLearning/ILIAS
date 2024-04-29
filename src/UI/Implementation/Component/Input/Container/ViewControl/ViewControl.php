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

namespace ILIAS\UI\Implementation\Component\Input\Container\ViewControl;

use ILIAS\UI\Component\Input\Container\ViewControl as I;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\UI\Implementation\Component\Input\QueryParamsFromServerRequest;
use ILIAS\UI\Implementation\Component\Input\Container\Container;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\StackedInputData;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component as C;

use ILIAS\UI\Implementation\Component\Input\ViewControl\HasInputGroup;

abstract class ViewControl extends Container implements I\ViewControl
{
    use JavaScriptBindable;

    protected Signal $submit_signal;
    protected ?ServerRequestInterface $request = null;
    protected Input\ArrayInputData $stored_input;

    /**
     * @param I\ViewControlInput[] $controls
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        Input\NameSource $name_source,
        C\Input\ViewControl\Factory $view_control_factory,
        array $controls
    ) {
        parent::__construct($name_source);
        $this->setInputGroup($view_control_factory->group($controls)->withDedicatedName('view_control'));
        $this->submit_signal = $signal_generator->create();
        $this->stored_input = new Input\ArrayInputData([]);
    }

    public function getSubmissionSignal(): Signal
    {
        return $this->submit_signal;
    }

    /**
     * @inheritDoc
     */
    public function withRequest(ServerRequestInterface $request): Container
    {
        $clone = parent::withRequest($request);
        $clone->request = $request;
        return $clone;
    }

    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    public function withStoredInput(Input\ArrayInputData $input): self
    {
        $clone = clone $this;
        $clone->stored_input = $input;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    protected function extractRequestData(ServerRequestInterface $request): InputData
    {
        $internal_input_data = new Input\ArrayInputData($this->getComponentInternalValues());
        return new StackedInputData(
            new QueryParamsFromServerRequest($request),
            $this->stored_input,
            $internal_input_data,
        );
    }

    /**
    * @return array     with key input name and its current value
    */
    public function getComponentInternalValues(
        C\Input\Group $component = null,
        array $input_values = []
    ): array {
        if(is_null($component)) {
            $component = $this->getInputGroup();
        }
        foreach ($component->getInputs() as $input) {
            if ($input instanceof C\Input\Group) {
                $input_values = $this->getComponentInternalValues($input, $input_values);
            }
            if ($input instanceof HasInputGroup) {
                $input_values = $this->getComponentInternalValues($input->getInputGroup(), $input_values);
            }
            if($name = $input->getName()) {
                $input_values[$input->getName()] = $input->getValue();
            }
        }

        return $input_values;
    }
}
