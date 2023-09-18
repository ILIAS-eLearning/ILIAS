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

namespace ILIAS\MetaData\Editor\Full\Services\Inputs\Conditions;

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Vocabularies\VocabulariesInterface;
use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Editor\Full\Services\Inputs\WithoutConditions\FactoryWithoutConditionTypesService;

class VocabValueConditionFactory extends BaseConditionFactory
{
    protected VocabulariesInterface $vocabularies;
    protected PathFactory $path_factory;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary,
        FactoryWithoutConditionTypesService $types,
        VocabulariesInterface $vocabularies,
        PathFactory $path_factory
    ) {
        parent::__construct($ui_factory, $presenter, $constraint_dictionary, $types);
        $this->vocabularies = $vocabularies;
        $this->path_factory = $path_factory;
    }

    protected function conditionInput(
        ElementInterface $element,
        ElementInterface $context_element,
        ElementInterface ...$conditional_elements
    ): FormInput {
        $groups = [];
        foreach ($this->vocabularies->vocabulariesForElement($element) as $vocab) {
            foreach ($vocab->values() as $value) {
                $inputs = [];
                foreach ($conditional_elements as $conditional_element) {
                    $input = $this->getInputInCondition(
                        $conditional_element,
                        $context_element,
                        $value
                    );
                    $path_string = $this->path_factory->toElement($conditional_element, true)
                                                      ->toString();
                    $inputs[$path_string] = $this->ui_factory->group(
                        [$input]
                    );
                }
                if (!isset($default_value)) {
                    $default_value = $value;
                }
                $groups[$value] = $this->ui_factory->group(
                    $inputs,
                    $this->presenter->data()->vocabularyValue($value)
                );
            }
        }
        $input = $this->ui_factory->switchableGroup(
            $groups,
            'placeholder'
        );
        if (isset($default_value)) {
            return $input->withValue($default_value);
        }
        return $input;
    }
}
