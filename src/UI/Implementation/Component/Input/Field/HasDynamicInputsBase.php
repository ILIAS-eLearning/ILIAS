<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\DynamicInputDataIterator;
use ILIAS\UI\Implementation\Component\Input\DynamicInputsNameSource;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Component\Input\Field\Input as InputInterface;
use ILIAS\UI\Component\Input\Field\HasDynamicInputs;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use InvalidArgumentException;
use LogicException;
use ilLanguage;

/**
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
abstract class HasDynamicInputsBase extends Input implements HasDynamicInputs
{
    // ==========================================
    // BEGIN IMPLEMENTATION OF DynamicInputsAware
    // ==========================================

    /**
     * @var InputInterface[]
     */
    protected array $dynamic_inputs = [];
    protected InputInterface $dynamic_input_template;
    protected ilLanguage $language;

    public function __construct(
        ilLanguage $language,
        DataFactory $data_factory,
        Refinery $refinery,
        string $label,
        InputInterface $template,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->dynamic_input_template = $template;
        $this->language = $language;
    }

    /**
     * Returns the instance of Input which should be used to generate
     * dynamic inputs on clientside.
     */
    public function getTemplateForDynamicInputs() : InputInterface
    {
        return $this->dynamic_input_template;
    }

    /**
     * Returns serverside generated dynamic Inputs, which happens when
     * providing this InputInterface::withValue().
     * @return InputInterface[]
     */
    public function getDynamicInputs() : array
    {
        return $this->dynamic_inputs;
    }

    // ==========================================
    // END IMPLEMENTATION OF DynamicInputsAware
    // ==========================================

    // ==========================================
    // BEGIN OVERWRITTEN METHODS OF Input
    // ==========================================

    /**
     * @param mixed $value
     */
    public function withValue($value) : self
    {
        if (!$this->isDynamicInputsValueOk($value)) {
            throw new InvalidArgumentException("Display value does not match input(-template) type.");
        }

        $clone = clone $this;

        foreach ($value as $input_name => $input_value) {
            $clone->dynamic_inputs[$input_name] = $clone->dynamic_input_template->withValue($input_value);
        }

        return $clone;
    }

    public function withDisabled(bool $is_disabled) : self
    {
        $clone = parent::withDisabled($is_disabled);
        $clone->dynamic_input_template = $clone->dynamic_input_template->withDisabled($is_disabled);

        foreach ($clone->dynamic_inputs as $key => $input) {
            $clone->dynamic_inputs[$key] = $input->withDisabled($is_disabled);
        }

        return $clone;
    }

    public function withNameFrom(NameSource $source) : self
    {
        $clone = parent::withNameFrom($source);

        $clone->dynamic_input_template = $clone->dynamic_input_template->withNameFrom(
            new DynamicInputsNameSource($clone->getName())
        );

        foreach ($clone->dynamic_inputs as $key => $input) {
            $clone->dynamic_inputs[$key] = $input->withNameFrom(
                new DynamicInputsNameSource($clone->getName())
            );
        }

        return $clone;
    }

    public function withInput(InputData $post_data) : self
    {
        if (null === $this->getName()) {
            throw new LogicException(static::class . '::withNameFrom must be called first.');
        }

        $clone = clone $this;
        $contains_error = false;
        $contents = [];

        foreach ((new DynamicInputDataIterator($post_data, $clone->getName())) as $index => $input_data) {
            $clone->dynamic_inputs[$index] = $this->dynamic_input_template->withInput($input_data);
            if ($clone->dynamic_inputs[$index]->getContent()->isOk()) {
                $contents[] = $clone->dynamic_inputs[$index]->getContent()->value();
            } else {
                $contains_error = true;
            }
        }

        if ($contains_error) {
            $clone->content = $clone->data_factory->error($this->language->txt("ui_error_in_group"));
        } else {
            $clone->content = $clone->applyOperationsTo($contents);
        }

        if ($clone->content->isError()) {
            $clone = $clone->withError((string) $clone->content->error());
        }

        return $clone;
    }

    public function getValue() : array
    {
        if (null === $this->getTemplateForDynamicInputs()) {
            return parent::getValue();
        }

        $values = [];
        foreach ($this->getDynamicInputs() as $key => $input) {
            $values[$key] = $input->getValue();
        }

        return $values;
    }

    // ==========================================
    // END OVERWRITTEN METHODS OF Input
    // ==========================================

    /**
     * @param mixed $value
     */
    protected function isDynamicInputsValueOk($value) : bool
    {
        if (!is_array($value)) {
            return $this->dynamic_input_template->isClientSideValueOk($value);
        }

        if (empty($value)) {
            return false;
        }

        foreach ($value as $input_value) {
            if (!$this->dynamic_input_template->isClientSideValueOk($input_value)) {
                return false;
            }
        }

        return true;
    }
}
