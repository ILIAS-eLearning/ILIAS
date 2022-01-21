<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php");

use ILIAS\UI\Implementation\Crawler\Entry as Entry;
use ILIAS\UI\Implementation\Crawler as Crawler;

/**
 * Explorer example
 */
class ilKSDocumentationExplorerGUI extends ilExplorerBaseGUI
{
    protected string $id = "ksDocumentationExplorer";

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
     * @param Entry\ComponentEntries $entries
     * @param $current_opened_node_id
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();

        parent::__construct($this->id, null, "");

        $this->setParentLink($this->ctrl->getLinkTargetByClass(["ilAdministrationGUI","ilObjStyleSettingsGUI","ilSystemStyleMainGUI","ilSystemStyleDocumentationGUI"], "entries"));

        $entries = Crawler\Entry\ComponentEntries::createFromArray(include ilSystemStyleDocumentationGUI::$DATA_PATH);

        $this->setEntries($entries);
        $this->setOfflineMode(true);
        $current_opened_node_id = $_GET["node_id"];

        if ($current_opened_node_id) {
            $this->setCurrentOpenedNodeId($current_opened_node_id);
        } else {
            $this->setCurrentOpenedNodeId($this->getEntries()->getRootEntryId());
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
    public function getChildsOfNode($a_parent_node_id) : array
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
     * @param mixed $a_node
     * @return string
     */
    public function getNodeContent($a_node) : string
    {
        return $a_node->getTitle();
    }

    /**
     * @param Entry\ComponentEntry $a_node
     * @return string
     */
    public function getNodeHref($a_node) : string
    {
        return $this->getParentLink() . "&node_id=" . $a_node->getId();
    }

    /**
     * @param Entry\ComponentEntry $a_node
     * @return bool
     */
    public function isNodeHighlighted($a_node) : bool
    {
        return $a_node->getId() == $this->getCurrentOpenedNode()->getId();
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
