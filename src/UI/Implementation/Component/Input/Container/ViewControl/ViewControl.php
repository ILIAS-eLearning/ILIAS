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

    /**
     * @inheritDoc
     */
    protected function extractRequestData(ServerRequestInterface $request): InputData
    {
        return new StackedInputData(
            new QueryParamsFromServerRequest($request),
            $this->extractCurrentValues()
        );
    }

    /**
     * Gets the current values of the inputs in the container.
     */
    public function extractCurrentValues(): InputData
    {
        return new Input\ArrayInputData($this->getComponentInternalValues());
    }

    public function getComponentInternalValues(C\Input\Group $component = null, array $names = []): array
    {
        if(is_null($component)) {
            $component = $this->getInputGroup();
        }
        foreach ($component->getInputs() as $input) {
            if ($input instanceof C\Input\Group) {
                $names = $this->getComponentInternalValues($input, $names);
            }
            if ($input instanceof HasInputGroup) {
                $names = $this->getComponentInternalValues($input->getInputGroup(), $names);
            }
            if($name = $input->getName()) {
                $names[$input->getName()] = $input->getValue();
            }
        }

        return $names;
    }
}
