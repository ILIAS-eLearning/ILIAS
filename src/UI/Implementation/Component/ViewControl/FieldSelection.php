<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component\ViewControl\FieldSelection as FieldSelectionInterface;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Input;

class FieldSelection implements FieldSelectionInterface
{
    /**
     * @var string
     */
    protected $label;    

    /**
     * @var string
     */
    protected $button_label;    

    /**
     * @var <string value, string label>
     */
    protected $options;    

    /**
     * @var Signal
     */
    protected $internal_signal;

    use ComponentHelper;

    public function __construct(
        \ILIAS\HTTP\Request\RequestFactory $request_factory,
        Input\Factory $input_factory,
        array $options,
        string $label = SelectionInterface::DEFAULT_DROPDOWN_LABEL,
        string $button_label = SelectionInterface::DEFAULT_BUTTON_LABEL
    ) {
        $this->dropdown_label = $label;
        $form_action = '#';
        $input = $input_factory->field()->multiSelect('', $options, '');
        $request = $request_factory->create();

        $this->form = $input_factory->container()->form()
            ->standard($form_action, [$input])
            ->withRequest($request);
            //->withSubmitLabel($button_label);
    }

    public function getForm(): Input\Container\Form\Standard
    {
        return $this->form;
    }

    public function getDropdownLabel(): string
    {
        return $this->dropdown_label;
    }

    public function getValue(): array
    {
        $values = array_shift($this->getForm()->getData());
        if(! $values) {
            return [];
        } 
        return $values;  
    }
}
