<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Crawler\Entry;

use ILIAS\UI\Implementation\Crawler as Crawler;
use Iterator;
use Countable;
use JsonSerializable;

/**
 * Container storing a list of UI Component Entries, can act as Iterator, countable and is serializable
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @implements Iterator<string, ComponentEntry>
 */
class ComponentEntries extends AbstractEntryPart implements Iterator, Countable, JsonSerializable
{
    /** @var array<string, ComponentEntry> */
    protected array $id_to_entry_map = [];

    protected string $root_entry_id = 'root';

    public function __construct()
    {
        parent::__construct();
        $this->rewind();
    }

    /**
     * Add and entry, first is always root.
     *
     * @throws	Crawler\Exception\CrawlerException
     */
    public function addEntry(ComponentEntry $entry): void
    {
        $this->assert()->isNotIndex($entry->getId(), $this->id_to_entry_map);
        if (count($this) == 0) {
            $this->setRootEntryId($entry->getId());
        }
        $this->id_to_entry_map[$entry->getId()] = $entry;
    }

    /**
     * @throws	Crawler\Exception\CrawlerException
     */
    public function addEntries(ComponentEntries $entries): void
    {
        foreach ($entries as $entry) {
            $this->addEntry($entry);
        }
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function addEntriesFromArray(array $entries): void
    {
        foreach ($entries as $entry_array) {
            $this->addEntry(new Crawler\Entry\ComponentEntry($entry_array));
        }
    }

    public function setRootEntryId(string $root_entry_id): void
    {
        $this->root_entry_id = $root_entry_id;
    }

    public function getRootEntryId(): string
    {
        return $this->root_entry_id;
    }

    public function getRootEntry(): ComponentEntry
    {
        return $this->getEntryById($this->getRootEntryId());
    }

    /**
     * @throws	Crawler\Exception\CrawlerException
     */
    public function getEntryById(string $id = ""): ComponentEntry
    {
        if (array_key_exists($id, $this->id_to_entry_map)) {
            return $this->id_to_entry_map[$id];
        }
        throw $this->f->exception(Crawler\Exception\CrawlerException::INVALID_ID, $id);
    }

    /**
     * @return	string[]
     */
    public function getParentsOfEntry(string $id): array
    {
        $parent_id = $this->getEntryById($id)->getParent();

        if (!$parent_id) {
            return array();
        } else {
            $parents = $this->getParentsOfEntry($parent_id);
            array_push($parents, $parent_id);
            return $parents;
        }
    }

    public function isParentOfEntry(string $parent_id, string $entry_id): bool
    {
        return in_array($parent_id, $this->getParentsOfEntry($entry_id));
    }

    /**
     * @return	string[]
     */
    public function getParentsOfEntryTitles(string $id): array
    {
        $titles = array();
        foreach ($this->getParentsOfEntry($id) as $parent_id) {
            $titles[$parent_id] = $this->getEntryById($parent_id)->getTitle();
        }
        return $titles;
    }

    /**
     * @return	string[]
     */
    public function getDescendantsOfEntry(string $id): array
    {
        $children = $this->getEntryById($id)->getChildren();
        foreach ($this->getEntryById($id)->getChildren() as $child) {
            $children = array_merge($children, $this->getDescendantsOfEntry($child));
        }
        return $children;
    }

    /**
     * @param string $id
     * @return ComponentEntry[]
     * @throws Crawler\Exception\CrawlerException
     */
    public function getChildrenOfEntry(string $id): array
    {
        $children = [];
        foreach ($this->getEntryById($id)->getChildren() as $child_id) {
            $children[] = $this->getEntryById($child_id);
        }
        return $children;
    }

    /**
     * @return	string[]
     */
    public function getDescendantsOfEntryTitles(string $id): array
    {
        $titles = array();
        foreach ($this->getDescendantsOfEntry($id) as $parent_id) {
            $titles[$parent_id] = $this->getEntryById($parent_id)->getTitle();
        }
        return $titles;
    }

    public function expose(): array
    {
        return get_object_vars($this);
    }

    /**
     * Iterator implementations
     */
    public function valid(): bool
    {
        return current($this->id_to_entry_map) !== false;
    }

    public function key(): string
    {
        return key($this->id_to_entry_map);
    }

    public function current(): ComponentEntry
    {
        return current($this->id_to_entry_map);
    }

    public function next(): void
    {
        next($this->id_to_entry_map);
    }

    public function rewind(): void
    {
        reset($this->id_to_entry_map);
    }

    /**
     * Countable implementations
     */
    public function count(): int
    {
        return count($this->id_to_entry_map);
    }

    /**
     * jsonSerialize implementation
     */
    public function jsonSerialize(): array
    {
        $serialized = [];
        foreach ($this->id_to_entry_map as $id => $item) {
            $serialized[$id] = $item->jsonSerialize();
        }
        return $serialized;
    }
}
