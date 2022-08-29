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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Repository\Validation\Data\DatetimeValidator;

class DatetimeFactory extends BaseFactory
{
    protected Refinery $refinery;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary,
        Refinery $refinery
    ) {
        parent::__construct($ui_factory, $presenter, $constraint_dictionary);
        $this->refinery = $refinery;
    }

    protected function rawInput(
        ElementInterface $element,
        ElementInterface $context_element,
        string $condition_value = ''
    ): FormInput {
        return $this->ui_factory
            ->dateTime('placeholder')
            ->withFormat($this->presenter->utilities()->getUserDateFormat())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    function ($v) {
                        return (string) $v?->format('Y-m-d');
                    }
                )
            );
    }

    protected function dataValueForInput(DataInterface $data): string
    {
        preg_match(
            DatetimeValidator::DATETIME_REGEX,
            $data->value(),
            $matches,
            PREG_UNMATCHED_AS_NULL
        );
        $date = new \DateTimeImmutable(
            ($matches[1] ?? '0000') . '-' .
            ($matches[2] ?? '01') . '-' .
            ($matches[3] ?? '01')
        );
        return $this->presenter->utilities()->getUserDateFormat()->applyTo($date);
    }
}
