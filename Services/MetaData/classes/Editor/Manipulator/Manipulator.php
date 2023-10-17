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

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Scaffolds\ScaffoldableInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Manipulator\ManipulatorInterface as BaseManipulator;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ilMDPathException;

class Manipulator implements ManipulatorInterface
{
    protected BaseManipulator $base_manipulator;
    protected NavigatorFactoryInterface $navigator_factory;
    protected RepositoryInterface $repository;

    public function __construct(
        BaseManipulator $base_manipulator,
        NavigatorFactoryInterface $navigator_factory,
        RepositoryInterface $repository
    ) {
        $this->base_manipulator = $base_manipulator;
        $this->navigator_factory = $navigator_factory;
        $this->repository = $repository;
    }

    /**
     * @throws ilMDPathException
     */
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

    public function prepareCreateOrUpdate(SetInterface $set, PathInterface $path, string ...$values): SetInterface
    {
        return $this->base_manipulator->prepareCreateOrUpdate(
            $set,
            $path,
            ...$values
        );
    }

    public function prepareForceCreate(SetInterface $set, PathInterface $path, string ...$values): SetInterface
    {
        return $this->base_manipulator->prepareForceCreate(
            $set,
            $path,
            ...$values
        );
    }

    public function prepareDelete(SetInterface $set, PathInterface $path): SetInterface
    {
        return $this->base_manipulator->prepareDelete($set, $path);
    }

    public function execute(SetInterface $set): void
    {
        $this->base_manipulator->execute($set);
    }

    /**
     * @return ElementInterface[]
     * @throws ilMDPathException
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
