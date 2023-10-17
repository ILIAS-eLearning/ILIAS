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

namespace ILIAS\MetaData\Repository\Validation\Data;

use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Vocabularies\VocabulariesInterface;
use ILIAS\MetaData\DataHelper\DataHelperInterface;

class DataValidatorService
{
    protected DatetimeValidator $datetime;
    protected DurationValidator $duration;
    protected LangValidator $lang;
    protected NonNegIntValidator $non_neg_int;
    protected NullValidator $null;
    protected StringValidator $string;
    protected VocabSourceValidator $vocab_source;
    protected VocabValueValidator $vocab_value;

    public function __construct(
        VocabulariesInterface $vocabularies,
        DataHelperInterface $data_helper
    ) {
        $this->datetime = new DatetimeValidator($data_helper);
        $this->duration = new DurationValidator($data_helper);
        $this->lang = new LangValidator($data_helper);
        $this->non_neg_int = new NonNegIntValidator();
        $this->null = new NullValidator();
        $this->string = new StringValidator();
        $this->vocab_source = new VocabSourceValidator($vocabularies);
        $this->vocab_value = new VocabValueValidator($vocabularies);
    }

    public function validator(Type $type): DataValidatorInterface
    {
        switch ($type) {
            case Type::NULL:
                return $this->null;

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
        }
        throw new \ilMDRepositoryException(
            'Unhandled data type when validating.'
        );
    }
}
