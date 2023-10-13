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
use ILIAS\MetaData\Vocabularies\Dictionary\LOMDictionaryInitiator as LOMVocabInitiator;
use ILIAS\MetaData\Repository\Utilities\Queries\DatabaseQuerierInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Results\RowInterface;

class DatabaseReader implements DatabaseReaderInterface
{
    protected ElementFactory $element_factory;
    protected StructureSetInterface $structure;
    protected DictionaryInterface $dictionary;
    protected NavigatorFactoryInterface $navigator_factory;
    protected DatabaseQuerierInterface $querier;
    protected \ilLogger $logger;

    public function __construct(
        ElementFactory $element_factory,
        StructureSetInterface $structure,
        DictionaryInterface $dictionary,
        NavigatorFactoryInterface $navigator_factory,
        DatabaseQuerierInterface $querier,
        \ilLogger $logger
    ) {
        $this->element_factory = $element_factory;
        $this->structure = $structure;
        $this->dictionary = $dictionary;
        $this->navigator_factory = $navigator_factory;
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
        RowInterface $result_row = null
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
                if ($definition->dataType() === Type::VOCAB_SOURCE) {
                    $value = LOMVocabInitiator::SOURCE;
                }

                if (
                    $definition->dataType() !== Type::NULL &&
                    $value === ''
                ) {
                    continue;
                }

                /**
                 * Container elements without their own tables are only created
                 * of they have sub-elements, and if they have more than just a
                 * single vocab source as sub-elements. The latter is necessary
                 * because vocab sources are not (yet) persisted in the database.
                 */
                $sub_elements = iterator_to_array($this->readSubElements(
                    $depth + 1,
                    $sub,
                    $ressource_id,
                    $parent_id,
                    $row
                ));
                if (
                    !isset($tag) &&
                    $definition->dataType() !== Type::VOCAB_SOURCE &&
                    count($sub_elements) <= 1 &&
                    (($sub_elements[0] ?? null)?->getData()?->type() ?? Type::VOCAB_SOURCE) === Type::VOCAB_SOURCE
                ) {
                    continue;
                }

                yield $this->element_factory->element(
                    $row->id(),
                    $definition,
                    $value,
                    ...$sub_elements
                );
            }
        }
    }

    /**
     * @return RowInterface[]
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
