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

namespace ILIAS\MetaData\Editor\Tree;

use ILIAS\UI\Component\Tree\TreeRecursion;
use ILIAS\UI\Component\Tree\Node\Factory;
use ILIAS\UI\Component\Tree\Node\Node;
use ILIAS\Data\URI;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Editor\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Editor\Dictionary\TagInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Editor\Http\Command;
use ILIAS\MetaData\Editor\Http\Parameter;
use ILIAS\MetaData\Editor\Http\LinkFactory as LinkFactory;

/**
 *  Tree Recursion, putting Entries into a Tree
 */
class Recursion implements TreeRecursion
{
    protected const MAX_LENGTH = 128;

    /**
     * @var ElementInterface[]
     */
    protected array $current_elements;

    protected PathFactoryInterface $path_factory;
    protected PresenterInterface $presenter;
    protected DictionaryInterface $dictionary;
    protected LinkFactory $link_factory;

    public function __construct(
        PathFactoryInterface $path_factory,
        PresenterInterface $presenter,
        DictionaryInterface $dictionary,
        LinkFactory $link_factory,
        ElementInterface ...$current_elements
    ) {
        $this->path_factory = $path_factory;
        $this->presenter = $presenter;
        $this->dictionary = $dictionary;
        $this->link_factory = $link_factory;

        $this->current_elements = $current_elements;
    }

    public function getChildren($record, $environment = null): array
    {
        /**
         * @var ElementInterface|ElementInterface[] $record
         */
        $tag = $this->dictionary->tagForElement(
            is_array($record) ? $record[0] : $record
        );
        if ($tag?->isLastInTree()) {
            return [];
        }
        if (!is_array($record)) {
            return $this->getCollectedSubElements($record);
        }
        return $record;
    }

    /**
     * @return array{ElementInterface|ElementInterface[]}
     */
    protected function getCollectedSubElements(
        ElementInterface $element
    ): array {
        $res = [];
        foreach ($element->getSubElements() as $sub) {
            if ($sub->isScaffold()) {
                continue;
            }
            $tag = $this->dictionary->tagForElement($sub);
            if ($tag?->isCollected()) {
                $res[$sub->getDefinition()->name()][] = $sub;
                continue;
            }
            $res[] = $sub;
        }
        return $res;
    }

    public function build(
        Factory $factory,
        $record,
        $environment = null
    ): Node {
        /**
         * @var ElementInterface|ElementInterface[] $record
         */
        $elements = is_array($record) ? $record : [$record];
        $tag = $this->dictionary->tagForElement($elements[0]);
        $is_collection = is_array($record);
        $is_last_in_tree = (bool) $tag?->isLastInTree();
        $is_root_or_directly_under = $elements[0]->getSuperElement()?->isRoot() ?? true;

        $with_extended_info = !$is_root_or_directly_under || !$is_collection;
        $label = $this->getNameWithRepresentation(
            $with_extended_info,
            $is_collection,
            ...$elements
        );
        $value = $this->getPreview(
            $with_extended_info,
            ...$elements
        );

        $is_linked = !$is_collection || $is_last_in_tree;
        $node = $factory
            ->keyValue($label, $value)
            ->withExpanded($this->isExpanded(...$elements))
            ->withHighlighted($this->isHighlighted($is_linked, ...$elements));

        if ($is_linked) {
            $node = $node->withLink(
                $this->getLink(is_array($record), ...$elements)
            );
        }

        return $node;
    }

    protected function isExpanded(ElementInterface ...$elements): bool
    {
        $current_and_parents = [$curr = $this->current_elements[0]];
        while (!$curr->isRoot()) {
            $curr = $curr->getSuperElement();
            $current_and_parents[] = $curr;
        }
        foreach ($elements as $el) {
            if (in_array($el, $current_and_parents, true)) {
                return true;
            }
        }
        return false;
    }

    protected function isHighlighted(
        bool $can_be_highlighted,
        ElementInterface ...$elements
    ): bool {
        return $can_be_highlighted && in_array($this->current_elements[0], $elements, true);
    }

    protected function getNameWithRepresentation(
        bool $with_representation,
        bool $plural,
        ElementInterface ...$elements
    ): string {
        if (!$with_representation) {
            $name =  $this->presenter->elements()->name(
                $elements[0],
                $plural
            );
        } else {
            $name = $this->presenter->elements()->nameWithRepresentation(
                $plural,
                ...$elements
            );
        }
        return $this->presenter->utilities()->shortenString(
            $name,
            self::MAX_LENGTH
        );
    }

    protected function getPreview(
        bool $with_preview,
        ElementInterface ...$elements
    ): string {
        if (!$with_preview) {
            return '';
        }
        return $this->presenter->utilities()->shortenString(
            $this->presenter->elements()->preview(...$elements),
            self::MAX_LENGTH
        );
    }

    protected function getLink(
        bool $record_is_array,
        ElementInterface ...$elements
    ): URI {
        $builder = $this->path_factory->custom();
        $el = $elements[0];
        $skip_last_id = $record_is_array;

        while (!$el->isRoot()) {
            $builder = $builder->withNextStep(
                $el->getDefinition()->name(),
                true
            );
            if (!$skip_last_id) {
                $builder = $builder->withAdditionalFilterAtCurrentStep(
                    FilterType::MDID,
                    (string) $el->getMDID()
                );
            }
            $el = $el->getSuperElement();
            if (is_null($el)) {
                throw new \ilMDEditorException('Invalid md set when building tree');
            }
            $skip_last_id = false;
        }
        $path_string = $builder->get()->toString();
        return $this->link_factory->custom(Command::SHOW_FULL)
                                  ->withParameter(Parameter::BASE_PATH, $path_string)
                                  ->get();
    }
}
