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

namespace ILIAS\MetaData\Editor\Full\Services\Inputs;

use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Repository\Dictionary\DictionaryInterface as DatabaseDictionary;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Repository\Dictionary\ExpectedParameter;
use ILIAS\MetaData\Editor\Full\Services\DataFinder;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Vocabularies\VocabulariesInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Editor\Full\Services\Inputs\Conditions\FactoryWithConditionTypesService;
use ILIAS\MetaData\Elements\Data\Type;

class InputFactory
{
    protected UIFactory $ui_factory;
    protected Refinery $refinery;
    protected PresenterInterface $presenter;
    protected PathFactory $path_factory;
    protected NavigatorFactoryInterface $navigator_factory;
    protected DataFinder $data_finder;
    protected VocabulariesInterface $vocabularies;
    protected FactoryWithConditionTypesService $types;

    /**
     * This is only here because the
     * editor needs to know which elements can be created (meaning
     * have a non-null create query).
     * This should be changed when we change the DB structure to
     * something that can work better with the new editor.
     */
    protected DatabaseDictionary $db_dictionary;

    public function __construct(
        UIFactory $ui_factory,
        Refinery $refinery,
        PresenterInterface $presenter,
        PathFactory $path_factory,
        NavigatorFactoryInterface $navigator_factory,
        DataFinder $data_finder,
        VocabulariesInterface $vocabularies,
        DatabaseDictionary $db_dictionary,
        FactoryWithConditionTypesService $types
    ) {
        $this->ui_factory = $ui_factory;
        $this->refinery = $refinery;
        $this->presenter = $presenter;
        $this->path_factory = $path_factory;
        $this->navigator_factory = $navigator_factory;
        $this->data_finder = $data_finder;
        $this->vocabularies = $vocabularies;
        $this->db_dictionary = $db_dictionary;
        $this->types = $types;
    }

    public function getInputFields(
        ElementInterface $element,
        ElementInterface $context_element,
        bool $with_title
    ): Section|Group {
        $conditional_elements = [];
        $input_elements = [];
        foreach ($this->data_finder->getDataCarryingElements($element) as $data_carrier) {
            $conditional_element = null;
            /**
             * Currently, hidden inputs don't play nice with switchable group inputs,
             * so for the time being they get pulled out here.
             */
            if (
                $data_carrier->getDefinition()->dataType() !== Type::VOCAB_SOURCE &&
                $el = $this->getConditionElement($data_carrier)
            ) {
                $conditional_element = $data_carrier;
                $data_carrier = $el;
            }
            $path_string = $this->path_factory->toElement($data_carrier, true)
                                              ->toString();
            $input_elements[$path_string] = $data_carrier;
            if (isset($conditional_element)) {
                $conditional_elements[$path_string][] = $conditional_element;
            }
        }

        $inputs = [];
        $exclude_required = [];
        foreach ($input_elements as $path_string => $input_element) {
            $data_type = $input_element->getDefinition()->dataType();
            if (isset($conditional_elements[$path_string])) {
                $input = $this->types->conditionFactory($data_type)->getConditionInput(
                    $input_element,
                    $context_element,
                    ...$conditional_elements[$path_string]
                );
            } else {
                $input = $this->types->factory($data_type)->getInput(
                    $input_element,
                    $context_element
                );
            }
            $inputs[$path_string] = $input;

            /**
             * If a data element can't be created, it needs to be excluded
             * from checking whether at least one input field is not empty.
             */
            if (is_null($this->db_dictionary->tagForElement($input_element))) {
                $exclude_required[] = $path_string;
            }
        }

        if ($with_title) {
            $fields = $this->ui_factory->section(
                $inputs,
                $this->presenter->elements()->nameWithParents($context_element)
            );
        } else {
            $fields = $this->ui_factory->group($inputs);
        }

        return $this->addNotEmptyConstraintIfNeeded(
            $context_element,
            $this->flattenOutput($fields),
            ...$exclude_required
        );
    }

    protected function getConditionElement(
        ElementInterface $element
    ): ?ElementInterface {
        foreach ($this->vocabularies->vocabulariesForElement($element) as $vocab) {
            if ($path = $vocab->condition()?->path()) {
                return $this->navigator_factory->navigator($path, $element)
                                               ->lastElementAtFinalStep();
            }
        }
        return null;
    }

    protected function flattenOutput(
        Section|Group $fields
    ): Section|Group {
        return $fields->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($vs) {
                foreach ($vs as $key => $value) {
                    if (is_array($value)) {
                        $vs[$key] = $value[0];
                        foreach ($value[1] as $k => $v) {
                            $vs[$k] = $v[0];
                        }
                    }
                }
                return $vs;
            })
        );
    }

    /**
     * If the current element can't be created on its own due to the db
     * structure, the editor has to require that at least one of the
     * inputs is not empty.
     */
    protected function addNotEmptyConstraintIfNeeded(
        ElementInterface $context_element,
        Section|Group $fields,
        string ...$excluded_input_keys
    ): Section|Group {
        $db_tag = $this->db_dictionary->tagForElement($context_element);
        if (!is_null($db_tag) && !$db_tag->hasData()) {
            return $fields;
        }
        return $fields->withAdditionalTransformation(
            $this->refinery->custom()->constraint(
                function ($vs) use ($excluded_input_keys) {
                    foreach ($vs as $p => $v) {
                        if (in_array($p, $excluded_input_keys)) {
                            continue;
                        }
                        if ($v !== '' && $v !== null) {
                            return true;
                        }
                    }
                    return false;
                },
                $this->presenter->utilities()->txt('meta_error_empty_input')
            )
        );
    }
}
