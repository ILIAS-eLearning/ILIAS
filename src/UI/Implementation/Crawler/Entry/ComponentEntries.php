<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Crawler\Entry;

use ILIAS\UI\Implementation\Crawler as Crawler;

/**
 * Container storing a list of UI Component Entries, can act as Iterator, countable and is serializable
 *
 * @author			  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class ComponentEntries extends AbstractEntryPart implements \Iterator, \Countable, \JsonSerializable
{
    /**
     * @var string
     */
    protected $root_entry_id = 'root';

    /**
     * @var ComponentEntry[]
     */
    protected $id_to_entry_map = array();

    /**
     * ComponentEntries constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->rewind();
    }

    /**
     * Add and entry, first is always root.
     *
     * @param	ComponentEntry $entry
     * @throws	Crawler\Exception\CrawlerException
     */
    public function addEntry(ComponentEntry $entry)
    {
        $this->assert()->isNotIndex($entry->getId(), $this->id_to_entry_map);
        if (count($this)==0) {
            $this->setRootEntryId($entry->getId());
        }
        $this->id_to_entry_map[$entry->getId()] = $entry;
    }

    /**
     * @param	ComponentEntries $entries
     * @throws	Crawler\Exception\CrawlerException
     */
    public function addEntries(ComponentEntries $entries)
    {
        foreach ($entries as $entry) {
            $this->addEntry($entry);
        }
    }

    /**
     * @param	string $root_entry_id
     */
    public function setRootEntryId($root_entry_id)
    {
        $this->root_entry_id = $root_entry_id;
    }

    /**
     * @return	string
     */
    public function getRootEntryId()
    {
        return $this->root_entry_id;
    }

    /**
     * @return	ComponentEntry
     */
    public function getRootEntry()
    {
        return $this->getEntryById($this->getRootEntryId());
    }

    /**
     * @param	string $id
     * @return	ComponentEntry
     * @throws	Crawler\Exception\CrawlerException
     */
    public function getEntryById($id = "")
    {
        if (array_key_exists($id, $this->id_to_entry_map)) {
            return $this->id_to_entry_map[$id];
        }
        throw $this->f->exception(Crawler\Exception\CrawlerException::INVALID_ID, $id);
    }

    /**
     * @param	string $id
     * @return	string[]
     */
    public function getParentsOfEntry($id)
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

    /**
     * @param	string $id
     * @return	string[]
     */
    public function getParentsOfEntryTitles($id)
    {
        $titles = array();
        foreach ($this->getParentsOfEntry($id) as $parent_id) {
            $titles[$parent_id] = $this->getEntryById($parent_id)->getTitle();
        }
        return $titles;
    }

    /**
     * @param	string $id
     * @return	string[]
     */
    public function getDescendantsOfEntry($id)
    {
        $children = $this->getEntryById($id)->getChildren();
        foreach ($this->getEntryById($id)->getChildren() as $child) {
            $children = array_merge($children, $this->getDescendantsOfEntry($child));
        }
        return $children;
    }

    /**
     * @param	string $id
     * @return	string[]
     */
    public function getDescendantsOfEntryTitles($id)
    {
        $titles = array();
        foreach ($this->getDescendantsOfEntry($id) as $parent_id) {
            $titles[$parent_id] = $this->getEntryById($parent_id)->getTitle();
        }
        return $titles;
    }

    public function expose()
    {
        return get_object_vars($this);
    }

    /**
     * Iterator implementations
     *
     * @return bool
     */
    public function valid()
    {
        return current($this->id_to_entry_map) !== false;
    }

    /**
     * @return	mixed
     */
    public function key()
    {
        return key($this->id_to_entry_map);
    }

    /**
     * @return	mixed
     */
    public function current()
    {
        return current($this->id_to_entry_map);
    }

    public function next()
    {
        next($this->id_to_entry_map);
    }
    public function rewind()
    {
        reset($this->id_to_entry_map);
    }

    /**
     * Countable implementations
     */
    public function count()
    {
        return count($this->id_to_entry_map);
    }

    /**
     * jsonSerialize implementation
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->id_to_entry_map;
    }
}
