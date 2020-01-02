<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php");

use ILIAS\UI\Implementation\Crawler\Entry as Entry;

/**
 * Explorer example
 */
class ilKSDocumentationExplorerGUI extends ilExplorerBaseGUI
{
    /**
     * @var ilCtrl $ctrl
     */
    protected $ctrl;

    /**
     *
     */
    protected $id = "ksDocumentationExplorer";

    /**
     * @var string
     */
    protected $parentLink = "";

    /**
     * @var Entry\ComponentEntries
     */
    protected $entries = null;

    /**
     * @var string
     */
    protected $current_opened_node_id = "";

    /**
     * ilKSDocumentationExplorerGUI constructor.
     * @param ilSystemStyleDocumentationGUI $a_parent_obj
     * @param $a_parent_cmd
     * @param Entry\ComponentEntries $entries
     * @param $current_opened_node_id
     */
    public function __construct(ilSystemStyleDocumentationGUI $a_parent_obj, $a_parent_cmd, Entry\ComponentEntries $entries, $current_opened_node_id)
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();

        parent::__construct($this->id, $a_parent_obj, $a_parent_cmd);

        $this->setParentLink($this->ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd));

        $this->setEntries($entries);
        $this->setOfflineMode(true);

        if (!$current_opened_node_id) {
            $this->setCurrentOpenedNodeId($this->getEntries()->getRootEntryId());
        } else {
            $this->setCurrentOpenedNodeId($current_opened_node_id);
        }

        $this->openNodesRecursively($this->getCurrentOpenedNodeId());
    }

    /**
     * @param $id
     * @throws \ILIAS\UI\Implementation\Crawler\Exception\CrawlerException
     */
    protected function openNodesRecursively($id)
    {
        $this->setNodeOpen($id);
        $parent_id = $this->getEntries()->getEntryById($id)->getParent();

        if ($parent_id) {
            $this->openNodesRecursively($parent_id);
        }
    }
    /**
     * Get root node.
     *
     * @return mixed root node object/array
     */
    public function getRootNode()
    {
        return $this->getEntries()->getRootEntry();
    }

    /**
     * @param $a_parent_node_id
     * @return Entry\ComponentEntry[]
     */
    public function getChildsOfNode($a_parent_node_id)
    {
        $entry = $this->getEntries()->getEntryById($a_parent_node_id);

        /**
         * @var Entry\ComponentEntry[]
         */
        $children = array();
        foreach ($entry->getChildren() as $child_id) {
            $children[$child_id] = $this->getEntries()->getEntryById($child_id);
        }
        return $children;
    }

    /**
     * @param $a_entry_id
     * @return Entry\ComponentEntry
     * @throws \ILIAS\UI\Implementation\Crawler\Exception\CrawlerException
     */
    public function getNodeById($a_entry_id)
    {
        return $this->getEntries()->getEntryById($a_entry_id);
    }

    /**
     * @param mixed $entry
     * @return Entry\ComponentEntry
     */
    public function getNodeContent($entry)
    {
        return $entry->getTitle();
    }

    /**
     * @param Entry\ComponentEntry $entry
     * @return string
     */
    public function getNodeHref($entry)
    {
        return $this->getParentLink() . "&node_id=" . $entry->getId();
    }

    /**
     * @param Entry\ComponentEntry $entry
     * @return bool
     */
    public function isNodeHighlighted($entry)
    {
        return $entry->getId() == $this->getCurrentOpenedNode()->getId();
    }
    /**
     * @param Entry\ComponentEntry $entry
     * @return mixed
     */
    public function getNodeId($entry)
    {
        return $entry->getId();
    }

    /**
     * @return string
     */
    public function getParentLink()
    {
        return $this->parentLink;
    }

    /**
     * @param string $parentLink
     */
    public function setParentLink($parentLink)
    {
        $this->parentLink = $parentLink;
    }

    /**
     * @return Entry\ComponentEntries
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param Entry\ComponentEntries $entries
     */
    public function setEntries($entries)
    {
        $this->entries = $entries;
    }

    /**
     * @return string
     */
    public function getCurrentOpenedNodeId()
    {
        return $this->current_opened_node_id;
    }

    /**
     * @param string $current_opened_node_id
     */
    public function setCurrentOpenedNodeId($current_opened_node_id)
    {
        $this->current_opened_node_id = $current_opened_node_id;
    }

    /**
     * @return Entry\ComponentEntry
     * @throws ilSystemStyleException
     */
    public function getCurrentOpenedNode()
    {
        return $this->getEntries()->getEntryById($this->getCurrentOpenedNodeId());
    }
}
