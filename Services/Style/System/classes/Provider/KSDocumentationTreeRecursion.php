<?php

declare(strict_types=1);

use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntry as Entry;
use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntries as Entries;
use ILIAS\Data\URI;

/**
 *  Tree Recursion, putting Entries into a Tree
 */
class KSDocumentationTreeRecursion implements \ILIAS\UI\Component\Tree\TreeRecursion
{
    protected Entries $entries;
    protected URI $parent_uri;
    protected Entry $current_node;

    public function __construct(Entries $entries, URI $parent_uri, string $current_opened_entry_id)
    {
        $this->entries = $entries;
        $this->parent_uri = $parent_uri;
        $this->current_node = $entries->getRootEntry();
        if ($current_opened_entry_id) {
            $this->current_node = $entries->getEntryById($current_opened_entry_id);
        }
    }

    public function getChildren($record, $environment = null): array
    {
        /**
         * @var Entry $record
         */
        return $this->entries->getChildrenOfEntry($record->getId());
    }

    public function build(
        \ILIAS\UI\Component\Tree\Node\Factory $factory,
        $record,
        $environment = null
    ): \ILIAS\UI\Component\Tree\Node\Node {
        /**
         * @var Entry $record
         */
        $is_expanded = $this->entries->isParentOfEntry($record->getId(), $this->current_node->getId());
        $is_highlited = $this->current_node->getId() == $record->getId();

        return $factory->simple($record->getTitle())
                       ->withLink($this->getNodeUri($record))
                       ->withExpanded($is_expanded)
                       ->withHighlighted($is_highlited);
    }

    protected function getNodeUri(Entry $a_node): URI
    {
        return $this->parent_uri->withParameter('node_id', $a_node->getId());
    }
}
