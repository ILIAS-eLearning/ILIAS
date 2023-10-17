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
use ILIAS\MetaData\DataHelper\DataHelperInterface;

class DurationFactory extends BaseFactory
{
    protected Refinery $refinery;
    protected DataHelperInterface $data_helper;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary,
        Refinery $refinery,
        DataHelperInterface $data_helper
    ) {
        parent::__construct($ui_factory, $presenter, $constraint_dictionary);
        $this->refinery = $refinery;
        $this->data_helper = $data_helper;
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
        $dh = $this->data_helper;
        return $this->ui_factory->group($nums)->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($vs) use ($dh) {
                $vs = array_map(fn ($v) => is_null($v) ? $v : (int) $v, $vs);
                return $dh->durationFromIntegers(...$vs);
            })
        );
    }

    /**
     * @return string[]|null[]
     */
    protected function dataValueForInput(DataInterface $data): array
    {
        return iterator_to_array($this->data_helper->durationToIterator($data->value()));
    }
}
