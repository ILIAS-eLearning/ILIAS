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

namespace ILIAS\MetaData\Repository\Utilities;

use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Elements\Factory as ElementFactory;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Elements\Element;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;
use ILIAS\MetaData\Repository\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Repository\Dictionary\TagInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\StructureNavigatorInterface;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\Data\Type;

class DatabaseReader implements DatabaseReaderInterface
{
    protected ElementFactory $element_factory;
    protected StructureSetInterface $structure;
    protected DictionaryInterface $dictionary;
    protected NavigatorFactoryInterface $navigator_factory;
    protected QueryExecutorInterface $executor;
    protected \ilLogger $logger;

    public function __construct(
        ElementFactory $element_factory,
        StructureSetInterface $structure,
        DictionaryInterface $dictionary,
        NavigatorFactoryInterface $navigator_factory,
        QueryExecutorInterface $executor,
        \ilLogger $logger
    ) {
        $this->element_factory = $element_factory;
        $this->structure = $structure;
        $this->dictionary = $dictionary;
        $this->navigator_factory = $navigator_factory;
        $this->executor = $executor;
        $this->logger = $logger;
    }

    public function getMD(RessourceIDInterface $ressource_id): SetInterface
    {
        return $this->getSetWithRoot(
            $ressource_id,
            ...$this->readSubElements(
                0,
                $this->structure->getRoot(),
                $ressource_id,
                0
            )
        );
    }

    public function getMDOnPath(
        PathInterface $path,
        RessourceIDInterface $ressource_id
    ): SetInterface {
        $navigator = $this->navigator_factory->structureNavigator(
            $path,
            $this->structure->getRoot()
        );
        return $this->getSetWithRoot(
            $ressource_id,
            ...$this->readSubElements(
                0,
                $navigator,
                $ressource_id,
                0
            )
        );
    }

    /**
     * @return Element[]
     */
    protected function readSubElements(
        int $depth,
        StructureElementInterface|StructureNavigatorInterface $struct,
        RessourceIDInterface $ressource_id,
        int $super_id,
        int ...$parent_ids
    ): \Generator {
        if ($depth > 20) {
            throw new \ilMDStructureException('LOM Structure is nested to deep.');
        }
        foreach ($this->subElements($struct) as $sub) {
            $tag = $this->tag($sub);
            $result = $this->executor->read($tag, $ressource_id, $super_id, ...$parent_ids);
            foreach ($result as $id => $data) {
                if (
                    $sub->getDefinition()->dataType() !== Type::NULL &&
                    ($data === null || $data === '')
                ) {
                    continue;
                }
                $definition = $this->definition($sub);
                $appended_parents = $parent_ids;
                if ($tag->isParent()) {
                    $appended_parents[] = $id;
                }
                yield $this->element_factory->element(
                    $id,
                    $definition,
                    $data,
                    ...$this->readSubElements(
                        $depth + 1,
                        $sub,
                        $ressource_id,
                        $id,
                        ...$appended_parents
                    )
                );
            }
        }
    }

    protected function definition(
        StructureElementInterface|StructureNavigatorInterface $struct,
    ): DefinitionInterface {
        if ($struct instanceof StructureNavigatorInterface) {
            $struct = $struct->element();
        }
        return $struct->getDefinition();
    }

    /**
     * @return StructureElementInterface[]|StructureNavigatorInterface[]
     */
    protected function subElements(
        StructureElementInterface|StructureNavigatorInterface $struct,
    ): \Generator {
        if ($struct instanceof StructureElementInterface) {
            yield from $struct->getSubElements();
            return;
        }
        if ($struct = $struct->nextStep()) {
            yield $struct;
        } else {
            yield from $struct->element()->getSubElements();
        }
    }

    protected function tag(
        StructureElementInterface|StructureNavigatorInterface $struct,
    ): TagInterface {
        if ($struct instanceof StructureNavigatorInterface) {
            $struct = $struct->element();
        }
        return $this->dictionary->tagForElement($struct);
    }

    protected function getSetWithRoot(
        RessourceIDInterface $ressource_id,
        Element ...$elements
    ): SetInterface {
        $root_definition = $this->structure->getRoot()->getDefinition();
        return $this->element_factory->set(
            $ressource_id,
            $this->element_factory->root(
                $root_definition,
                ...$elements
            )
        );
    }
}
