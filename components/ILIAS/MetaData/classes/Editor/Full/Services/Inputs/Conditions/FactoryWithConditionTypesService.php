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

use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Editor\Full\Services\Inputs\WithoutConditions\FactoryWithoutConditionTypesService;
use ILIAS\MetaData\Editor\Full\Services\Inputs\WithoutConditions\BaseFactory;
use ILIAS\MetaData\DataHelper\DataHelperInterface;
use ILIAS\MetaData\Vocabularies\ElementHelper\ElementHelperInterface;
use ILIAS\MetaData\Vocabularies\Slots\HandlerInterface as VocabSlotHandler;

class FactoryWithConditionTypesService
{
    protected VocabValueConditionFactory $vocab_value;
    protected FactoryWithoutConditionTypesService $types_without_conditions;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary,
        Refinery $refinery,
        PathFactory $path_factory,
        DataHelperInterface $data_helper,
        ElementHelperInterface $element_vocab_helper,
        VocabSlotHandler $vocab_slot_handler
    ) {
        $this->types_without_conditions = new FactoryWithoutConditionTypesService(
            $ui_factory,
            $presenter,
            $constraint_dictionary,
            $refinery,
            $data_helper,
            $element_vocab_helper,
            $path_factory
        );
        $this->vocab_value = new VocabValueConditionFactory(
            $ui_factory,
            $presenter,
            $constraint_dictionary,
            $this->types_without_conditions,
            $path_factory,
            $element_vocab_helper,
            $vocab_slot_handler
        );
    }

    public function conditionFactory(Type $type): BaseConditionFactory
    {
        switch ($type) {
            case Type::VOCAB_VALUE:
                return $this->vocab_value;

            default:
                throw new \ilMDRepositoryException(
                    'Currently, only vocab values can serve as conditions.'
                );
        }
    }

    public function factory(Type $type): BaseFactory
    {
        return $this->types_without_conditions->factory($type);
    }
}
