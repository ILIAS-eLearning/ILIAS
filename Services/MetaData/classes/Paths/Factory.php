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

namespace ILIAS\MetaData\Paths;

use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Elements\Structure\StructureElement;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;

class Factory implements FactoryInterface
{
    protected StructureSetInterface $structure;

    public function __construct(StructureSetInterface $structure)
    {
        $this->structure = $structure;
    }

    public function fromString(string $string): PathInterface
    {
        $exploded = explode(Token::SEPARATOR->value, strtolower($string));
        $builder = $this->setModesFromString($this->custom(), $exploded[0]);
        $exploded = array_slice($exploded, 1);
        foreach ($exploded as $step_string) {
            $builder = $this->addStepFromString($builder, $step_string);
        }
        return $builder->get();
    }

    protected function setModesFromString(
        BuilderInterface $builder,
        string $string
    ): BuilderInterface {
        $pattern = '/^(' . strtolower(Token::LEADS_TO_EXACTLY_ONE->value) .
            ')?(' . strtolower(Token::START_AT_ROOT->value) . '|' .
            strtolower(Token::START_AT_CURRENT->value) . ')$/';
        if (!preg_match($pattern, $string, $matches)) {
            throw new \ilMDPathException(
                'Cannot create path, invalid modes in input string: ' . $string
            );
        }
        if (!empty($matches[1])) {
            $builder = $builder->withLeadsToExactlyOneElement(true);
        }
        if ($matches[2] === Token::START_AT_ROOT->value) {
            $builder = $builder->withRelative(false);
        } else {
            $builder = $builder->withRelative(true);
        }
        return $builder;
    }

    protected function addStepFromString(
        BuilderInterface $builder,
        string $string
    ): BuilderInterface {
        $exploded = explode(Token::FILTER_SEPARATOR->value, strtolower($string));
        $name = StepToken::tryFrom($exploded[0]) ?? $exploded[0];
        if ($name === StepToken::SUPER) {
            $builder = $builder->withNextStepToSuperElement(false);
        } else {
            $builder = $builder->withNextStep($name, false);
        }
        $exploded = array_slice($exploded, 1);
        foreach ($exploded as $filter_string) {
            $exploded_filter = explode(
                Token::FILTER_VALUE_SEPARATOR->value,
                strtolower($filter_string)
            );
            $type = FilterType::tryFrom($exploded_filter[0]);
            $exploded_filter = array_slice($exploded_filter, 1);
            if (!is_null($type)) {
                $builder = $builder = $builder->withAdditionalFilterAtCurrentStep(
                    $type,
                    ...$exploded_filter
                );
                continue;
            }
            throw new \ilMDPathException(
                'Cannot create path, invalid filter type.'
            );
        }
        return $builder;
    }

    public function toElement(
        BaseElementInterface $to,
        bool $leads_to_exactly_one = false
    ): PathInterface {
        $builder = $this
            ->custom()
            ->withRelative(false)
            ->withLeadsToExactlyOneElement($leads_to_exactly_one);

        while (!$to->isRoot()) {
            $builder = $this->addElementAsStep(
                $builder,
                $to,
                $leads_to_exactly_one,
                true
            );
            $to = $to->getSuperElement();
            if (!isset($to)) {
                throw new \ilMDPathException(
                    'Cannot build path from element without root.'
                );
            }
        }

        return $builder->get();
    }

    public function betweenElements(
        BaseElementInterface $from,
        BaseElementInterface $to,
        bool $leads_to_exactly_one = false
    ): PathInterface {
        $to_and_supers = [];
        while ($to) {
            array_unshift($to_and_supers, $to);
            $to = $to->getSuperElement();
        }

        $builder = $this
            ->custom()
            ->withRelative(true)
            ->withLeadsToExactlyOneElement($leads_to_exactly_one);

        while (!in_array($from, $to_and_supers, true)) {
            $builder = $builder->withNextStepToSuperElement();
            $from = $from->getSuperElement();
            if (!isset($from)) {
                throw new \ilMDPathException(
                    'Cannot build path between elements from disjunct sets.'
                );
            }
        }

        $to_and_supers = array_slice(
            $to_and_supers,
            array_search($from, $to_and_supers, true) + 1
        );
        foreach ($to_and_supers as $element) {
            $builder = $this->addElementAsStep(
                $builder,
                $element,
                $leads_to_exactly_one,
                false
            );
        }

        return $builder->get();
    }

    protected function addElementAsStep(
        BuilderInterface $builder,
        BaseElementInterface $element,
        bool $leads_to_exactly_one,
        bool $add_as_first
    ): BuilderInterface {
        $builder = $builder->withNextStep(
            $element->getDefinition()->name(),
            $add_as_first
        );

        $id = $element->getMDID();
        if ($element instanceof StructureElement) {
            return $builder;
        }
        $id = is_int($id) ? (string) $id : $id->value;
        if ($leads_to_exactly_one) {
            $builder = $builder->withAdditionalFilterAtCurrentStep(
                FilterType::MDID,
                $id
            );
        }

        return $builder;
    }

    public function custom(): BuilderInterface
    {
        return new Builder($this->structure);
    }
}
