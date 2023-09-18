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

namespace ILIAS\MetaData\Editor\Manipulator;

use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Elements\Markers\MarkerFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Scaffolds\ScaffoldableInterface;
use ILIAS\MetaData\Elements\Markers\MarkableInterface;
use ILIAS\MetaData\Elements\Markers\Action;
use ILIAS\MetaData\Paths\Steps\StepInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Paths\Navigator\NavigatorInterface;

class Manipulator implements ManipulatorInterface
{
    protected RepositoryInterface $repository;
    protected MarkerFactoryInterface $marker_factory;
    protected NavigatorFactoryInterface $navigator_factory;

    public function __construct(
        RepositoryInterface $repository,
        MarkerFactoryInterface $marker_factory,
        NavigatorFactoryInterface $navigator_factory
    ) {
        $this->repository = $repository;
        $this->marker_factory = $marker_factory;
        $this->navigator_factory = $navigator_factory;
    }

    public function addScaffolds(
        SetInterface $set,
        ?PathInterface $path = null
    ): SetInterface {
        $set = clone $set;
        $to_be_scaffolded = [];
        foreach ($this->getElements($set, $path) as $el) {
            $super = $el->getSuperElement() ?? $el;
            if (!in_array($super, $to_be_scaffolded, true)) {
                $to_be_scaffolded[] = $super;
            }
        }
        while (!empty($to_be_scaffolded)) {
            $next = [];
            foreach ($to_be_scaffolded as $element) {
                if (!($element instanceof ScaffoldableInterface)) {
                    continue;
                }
                $element->addScaffoldsToSubElements($this->repository->scaffolds());
                $next = array_merge(
                    $next,
                    iterator_to_array($element->getSubElements())
                );
            }
            $to_be_scaffolded = $next;
        }
        return $set;
    }

    public function prepareCreateOrUpdate(
        SetInterface $set,
        PathInterface $path,
        string ...$values
    ): SetInterface {
        $set = clone $set;
        $navigator = $this->navigator_factory->navigator(
            $path,
            $element = $set->getRoot()
        );

        /*
         * Follow the path the first time adding scaffolds where necessary,
         * remembering the best elements along the way to add more scaffolds.
         */
        $first_anchor = null;
        $under_first_anchor = null;
        $navigator_at_first_anchor = null;
        $anchor = $element;
        $under_anchor = null;
        $navigator_at_anchor = $navigator;
        $reached_end = true;
        while ($next = $navigator->nextStep()) {
            if (!($next_element = $next->lastElement())) {
                $next_element = $this->addAndMarkScaffoldByStep(
                    $element,
                    $next->currentStep()
                );
            }
            if (!isset($next_element)) {
                if (!isset($first_anchor) || !isset($navigator_at_first_anchor)) {
                    throw new \ilMDEditorException('Invalid update path: ' . $path->toString());
                }
                $anchor = $first_anchor;
                $under_anchor = $under_first_anchor;
                $navigator_at_anchor = $navigator_at_first_anchor;
                $reached_end = false;
                break;
            }
            if (!$next_element->getDefinition()->unique()) {
                $anchor = $element;
                $under_anchor = $next_element;
                $navigator_at_anchor = $navigator;
                if (!isset($first_anchor) || !isset($navigator_at_first_anchor)) {
                    $first_anchor = $anchor;
                    $under_first_anchor = $under_anchor;
                    $navigator_at_first_anchor = $navigator_at_anchor;
                }
            }
            $navigator = $next;
            $element = $next_element;
        }

        /*
         * If there are not yet enough elements to accomodate all values that
         * are to be updated/added, add them as scaffolds, starting from the
         * previously chosen anchor element.
         */
        $navigator = $navigator_at_anchor->nextStep();
        $anchor_subs = $reached_end ? iterator_to_array($navigator->elements()) : [];
        if (
            $reached_end &&
            isset($under_anchor) &&
            !in_array($under_anchor, $anchor_subs, true)
        ) {
            $anchor_subs[] = $under_anchor;
        }
        while (count($anchor_subs) < count($values)) {
            $scaffold = $this->addAndMarkScaffoldByStep(
                $anchor,
                $navigator->currentStep()
            );
            if (!isset($scaffold)) {
                throw new \ilMDEditorException('Invalid update path: ' . $path->toString());
            }
            $anchor_subs[] = $scaffold;
        }
        $final_elements = [];
        $add_scaffolds = false;
        foreach ($anchor_subs as $el) {
            $element = $el;
            $nav = $navigator;
            while ($next = $nav->nextStep()) {
                $next_element = null;
                foreach ($next->elements() as $potential_next_el) {
                    if ($potential_next_el->getSuperElement() === $element) {
                        $next_element = $potential_next_el;
                    }
                }
                if ($add_scaffolds || !isset($next_element)) {
                    $next_element = $this->addAndMarkScaffoldByStep(
                        $element,
                        $next->currentStep()
                    );
                    $add_scaffolds = true;
                }
                if (!isset($next_element)) {
                    throw new \ilMDEditorException('Invalid update path: ' . $path->toString());
                }
                $nav = $next;
                $element = $next_element;
            }
            $final_elements[] = $element;
            $add_scaffolds = false;
        }

        /*
         * Mark all final elements to be created/updated with the given values.
         */
        foreach ($final_elements as $element) {
            if (!($element instanceof MarkableInterface)) {
                continue;
            }
            $element->mark(
                $this->marker_factory,
                Action::CREATE_OR_UPDATE,
                is_array($values) ? array_shift($values) : $values
            );
            if (empty($values)) {
                break;
            }
        }
        return $set;
    }

    public function prepareDelete(
        SetInterface $set,
        PathInterface $path,
    ): SetInterface {
        $set = clone $set;
        foreach ($this->getMarkables($set, $path) as $element) {
            $element->mark($this->marker_factory, Action::DELETE);
        }
        return $set;
    }

    public function execute(SetInterface $set): void
    {
        $this->repository->manipulateMD($set);
    }

    /**
     * also returns the added scaffold, if valid
     */
    protected function addAndMarkScaffoldByStep(
        ElementInterface $element,
        StepInterface $step
    ): ?ElementInterface {
        if ($step->name() === StepToken::SUPER) {
            return $element->getSuperElement();
        }
        if (!($element instanceof ScaffoldableInterface)) {
            return null;
        }
        $scaffold = $element->addScaffoldToSubElements(
            $this->repository->scaffolds(),
            $step->name()
        );
        if (!isset($scaffold)) {
            return null;
        }

        $data = '';
        foreach ($step->filters() as $filter) {
            if ($filter->type() === FilterType::DATA) {
                $data = $filter->values()->current();
                break;
            }
        }
        $scaffold->mark(
            $this->marker_factory,
            Action::CREATE_OR_UPDATE,
            $data
        );

        return $scaffold;
    }

    /**
     * @return MarkableInterface
     */
    protected function getMarkables(
        SetInterface $set,
        PathInterface $path
    ): \Generator {
        foreach ($this->getElements($set, $path) as $element) {
            if ($element instanceof MarkableInterface) {
                yield $element;
            }
        }
    }

    /**
     * @return ElementInterface[]
     */
    protected function getElements(
        SetInterface $set,
        ?PathInterface $path = null
    ): \Generator {
        if (!isset($path)) {
            yield $set->getRoot();
            return;
        }
        yield from $this->navigator_factory->navigator(
            $path,
            $set->getRoot()
        )->elementsAtFinalStep();
    }
}
