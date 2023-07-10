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
        $is_expanded = ($this->entries->isParentOfEntry($record->getId(), $this->current_node->getId())) ||
            ($record == $this->entries->getRootEntry());
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
