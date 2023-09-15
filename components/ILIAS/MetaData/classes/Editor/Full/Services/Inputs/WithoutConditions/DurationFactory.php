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
use ILIAS\MetaData\Repository\Validation\Data\DurationValidator;

class DurationFactory extends BaseFactory
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
        $num = $this->ui_factory
            ->numeric('placeholder')
            ->withAdditionalTransformation(
                $this->refinery->int()->isGreaterThanOrEqual(0)
            );
        $nums = [];
        foreach ($this->presenter->data()->durationLabels() as $label) {
            $nums[] = (clone $num)->withLabel($label);
        }
        return $this->ui_factory->group($nums)->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($vs) {
                if (
                    count(array_unique($vs)) === 1 &&
                    array_unique($vs)[0] === null
                ) {
                    return '';
                }
                $r = 'P';
                $signifiers = ['Y', 'M', 'D', 'H', 'M', 'S'];
                foreach ($vs as $key => $int) {
                    if (isset($int)) {
                        $r .= $int . $signifiers[$key];
                    }
                    if (
                        $key === 2 &&
                        !isset($vs[3]) &&
                        !isset($vs[4]) &&
                        !isset($vs[5])
                    ) {
                        return $r;
                    }
                    if ($key === 2) {
                        $r .= 'T';
                    }
                }
                return $r;
            })
        );
    }

    /**
     * @return string[]
     */
    protected function dataValueForInput(DataInterface $data): array
    {
        preg_match(
            DurationValidator::DURATION_REGEX,
            $data->value(),
            $matches,
            PREG_UNMATCHED_AS_NULL
        );
        return array_slice($matches, 1);
    }
}
