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
use ILIAS\MetaData\Repository\Utilities\Queries\DatabaseQuerierInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Results\RowInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

class DatabaseReader implements DatabaseReaderInterface
{
    protected ElementFactory $element_factory;
    protected StructureSetInterface $structure;
    protected DictionaryInterface $dictionary;
    protected NavigatorFactoryInterface $navigator_factory;
    protected PathFactoryInterface $path_factory;
    protected DatabaseQuerierInterface $querier;
    protected \ilLogger $logger;

    public function __construct(
        ElementFactory $element_factory,
        StructureSetInterface $structure,
        DictionaryInterface $dictionary,
        NavigatorFactoryInterface $navigator_factory,
        PathFactoryInterface $path_factory,
        DatabaseQuerierInterface $querier,
        \ilLogger $logger
    ) {
        $this->element_factory = $element_factory;
        $this->structure = $structure;
        $this->dictionary = $dictionary;
        $this->navigator_factory = $navigator_factory;
        $this->path_factory = $path_factory;
        $this->querier = $querier;
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
        $path = $this->shortenPath($path);

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
        int $id_from_parent_table,
        ?RowInterface $result_row = null
    ): \Generator {
        if ($depth > 20) {
            throw new \ilMDStructureException('LOM Structure is nested to deep.');
        }

        foreach ($this->subElements($struct) as $sub) {
            $tag = $this->tag($sub);
            $table = $tag?->table() ?? '';
            $definition = $this->definition($sub);

            // Read out the next table, if required.
            $parent_id = $id_from_parent_table;
            $result_rows = [];
            if (!is_null($result_row)) {
                $result_rows = [$result_row];
            }
            if ($table && $result_row?->table() !== $table) {
                $parent_id = $result_row?->id() ?? 0;
                $result_rows = $this->querier->read(
                    $ressource_id,
                    $parent_id,
                    ...$this->collectTagsFromSameTable($depth, $table, $sub)
                );
            }

            foreach ($result_rows as $row) {
                $value = $row->value($tag?->dataField() ?? '');

                if ($definition->dataType() !== Type::NULL && $value === '') {
                    continue;
                }

                /**
                 * Container elements without their own tables are only created
                 * of they have sub-elements.
                 */
                $sub_elements = iterator_to_array($this->readSubElements(
                    $depth + 1,
                    $sub,
                    $ressource_id,
                    $parent_id,
                    $row
                ));
                if (!isset($tag) && count($sub_elements) <= 0) {
                    continue;
                }

                yield $this->element_factory->element(
                    $row->id(),
                    $definition,
                    $value,
                    SlotIdentifier::NULL,
                    ...$sub_elements
                );
            }
        }
    }

    /**
     * @return TagInterface[]
     */
    protected function collectTagsFromSameTable(
        int $depth,
        string $table,
        StructureElementInterface|StructureNavigatorInterface $struct
    ): \Generator {
        if ($depth > 20) {
            throw new \ilMDStructureException('LOM Structure is nested to deep.');
        }

        $tag = $this->tag($struct);
        if (!is_null($tag) && $table !== $tag?->table()) {
            return;
        }
        if (!is_null($tag)) {
            yield $tag;
        }

        foreach ($this->subElements($struct) as $sub) {
            yield from $this->collectTagsFromSameTable(
                $depth + 1,
                $table,
                $sub
            );
        }
    }

    /**
     * Cuts off the path at the highest starting point of sub-paths
     * created with super steps.
     */
    protected function shortenPath(PathInterface $path): PathInterface
    {
        $depth = 0;
        $super_step_depths = [];
        foreach ($path->steps() as $step) {
            if ($step->name() === StepToken::SUPER) {
                $depth--;
                $super_step_depths[] = $depth;
                continue;
            }
            $depth++;
        }

        if (empty($super_step_depths)) {
            return $path;
        }

        $cut_off = min($super_step_depths);
        $depth = 0;
        $path_builder = $this->path_factory->custom();
        foreach ($path->steps() as $step) {
            if ($depth === $cut_off) {
                break;
            }
            $path_builder = $path_builder->withNextStepFromStep($step);
            $depth++;
        }
        return $path_builder->get();
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
        if ($next_struct = $struct->nextStep()) {
            yield $next_struct;
        } else {
            yield from $struct->element()->getSubElements();
        }
    }

    protected function tag(
        StructureElementInterface|StructureNavigatorInterface $struct,
    ): ?TagInterface {
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
