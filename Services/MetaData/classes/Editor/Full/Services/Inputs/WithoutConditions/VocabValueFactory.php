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

namespace ILIAS\MetaData\Editor\Full\Services\Inputs\WithoutConditions;

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Vocabularies\VocabulariesInterface;
use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;

class VocabValueFactory extends BaseFactory
{
    protected VocabulariesInterface $vocabularies;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary,
        VocabulariesInterface $vocabularies
    ) {
        parent::__construct($ui_factory, $presenter, $constraint_dictionary);
        $this->vocabularies = $vocabularies;
    }

    protected function rawInput(
        ElementInterface $element,
        ElementInterface $context_element,
        string $condition_value = ''
    ): FormInput {
        $data = null;
        if ($element->getDefinition()->dataType() !== Type::NULL) {
            $data = $this->dataValueForInput($element->getData());
        }
        $values = [];
        $use_data_as_value = false;
        foreach ($this->vocabularies->vocabulariesForElement($element) as $vocab) {
            if ($condition_value !== '' && $vocab->condition()?->value() !== $condition_value) {
                continue;
            }
            foreach ($vocab->values() as $value) {
                if ($data === $value) {
                    $use_data_as_value = true;
                }
                $values[$value] = $this->presenter->data()->vocabularyValue($value);
            }
        }
        $input = $this->ui_factory->select('placeholder', $values);
        if ($use_data_as_value && isset($data)) {
            $input = $input->withValue($data);
        }
        return $input;
    }

    protected function dataValueForInput(DataInterface $data): string
    {
        $value = strtolower(
            preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $data->value())
        );
        $exceptions = [
            'is part of' => 'ispartof', 'has part' => 'haspart',
            'is version of' => 'isversionof', 'has version' => 'hasversion',
            'is format of' => 'isformatof', 'has format' => 'hasformat',
            'references' => 'references',
            'is referenced by' => 'isreferencedby',
            'is based on' => 'isbasedon', 'is basis for' => 'isbasisfor',
            'requires' => 'requires', 'is required by' => 'isrequiredby',
        ];
        if (array_key_exists($value, $exceptions)) {
            $value = $exceptions[$value];
        }
        return $value;
    }
}
