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

use ILIAS\MetaData\Vocabularies\VocabulariesInterface;
use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\DataHelper\DataHelperInterface;

class FactoryWithoutConditionTypesService
{
    protected DatetimeFactory $datetime;
    protected DurationFactory $duration;
    protected LangFactory $lang;
    protected NonNegIntFactory $non_neg_int;
    protected StringFactory $string;
    protected VocabSourceFactory $vocab_source;
    protected VocabValueFactory $vocab_value;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary,
        VocabulariesInterface $vocabularies,
        Refinery $refinery,
        DataHelperInterface $data_helper
    ) {
        $this->datetime = new DatetimeFactory(
            $ui_factory,
            $presenter,
            $constraint_dictionary,
            $refinery,
            $data_helper
        );
        $this->duration = new DurationFactory(
            $ui_factory,
            $presenter,
            $constraint_dictionary,
            $refinery,
            $data_helper
        );
        $this->lang = new LangFactory(
            $ui_factory,
            $presenter,
            $constraint_dictionary,
            $data_helper
        );
        $this->non_neg_int = new NonNegIntFactory(
            $ui_factory,
            $presenter,
            $constraint_dictionary,
            $refinery
        );
        $this->string = new StringFactory(
            $ui_factory,
            $presenter,
            $constraint_dictionary
        );
        $this->vocab_source = new VocabSourceFactory(
            $ui_factory,
            $presenter,
            $constraint_dictionary
        );
        $this->vocab_value = new VocabValueFactory(
            $ui_factory,
            $presenter,
            $constraint_dictionary,
            $vocabularies
        );
    }

    public function factory(Type $type): BaseFactory
    {
        switch ($type) {
            case Type::STRING:
                return $this->string;

            case Type::LANG:
                return $this->lang;

            case Type::VOCAB_SOURCE:
                return $this->vocab_source;

            case Type::VOCAB_VALUE:
                return $this->vocab_value;

            case Type::DATETIME:
                return $this->datetime;

            case Type::NON_NEG_INT:
                return $this->non_neg_int;

            case Type::DURATION:
                return $this->duration;

            default:
                throw new \ilMDRepositoryException(
                    'Unhandled data type when building inputs.'
                );
        }
    }
}
