<?php declare(strict_types=1);

/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\KioskMode\TOCBuilder;

/**
 * Tree-GUI for ToC
 */
class ilLSTOCGUI extends ilExplorerBaseGUI
{
    const NODE_ICONS = [
        TOCBuilder::LP_NOT_STARTED => "./templates/default/images/scorm/not_attempted.svg",
        TOCBuilder::LP_IN_PROGRESS => "./templates/default/images/scorm/incomplete.svg",
        TOCBuilder::LP_COMPLETED => "./templates/default/images/scorm/completed.svg",
        TOCBuilder::LP_FAILED => "./templates/default/images/scorm/failed.svg"
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $structure;

    /**
     * @var array<string, mixed>
     */
    protected array $nodes = [];
    protected LSUrlBuilder $url_builder;
    protected int $counter = 0;

    public function __construct(LSUrlBuilder $url_builder)
    {
        parent::__construct("lsq_toc", null, "");

        $this->url_builder = $url_builder;
        $this->setSkipRootNode(false);
        $this->setNodeOnclickEnabled(true);
    }

    public function withStructure(string $json_structure)
    {
        $clone = clone $this;
        $clone->structure = $clone->addIds(json_decode($json_structure, true));
        $clone->buildLookup($clone->structure);
        $clone->open_nodes = array_keys($clone->nodes);
        return $clone;
    }

    protected function addIds(array $node) : array
    {
        $node['_id'] = $this->counter;
        $this->counter++;
        if (array_key_exists('childs', $node)) {
            foreach ($node['childs'] as $idx => $child) {
                $node['childs'][$idx] = $this->addIds($child);
            }
        }
        return $node;
    }

    protected function buildLookup(array $node) : void
    {
        $this->nodes[$node['_id']] = $node;
        if (array_key_exists('childs', $node)) {
            foreach ($node['childs'] as $child) {
                $this->buildLookup($child);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getRootNode()
    {
        return reset($this->nodes);
    }

    /**
     * @inheritdoc
     */
    public function getChildsOfNode($a_parent_node_id) : array
    {
        $parent_node = $this->nodes[$a_parent_node_id];
        return (array) $parent_node['childs'];
    }

    /**
     * @inheritdoc
     */
    public function getNodeContent($a_node) : string
    {
        return $a_node['label'];
    }

    /**
     * @inheritdoc
     */
    public function getNodeIcon($a_node) : string
    {
        $state = $a_node['state'] ?? TOCBuilder::LP_NOT_STARTED;
        return static::NODE_ICONS[$state];
    }

    /**
     * @inheritdoc
     */
    public function getNodeId($a_node)
    {
        return $a_node['_id'];
    }

    /**
     * @inheritdoc
     */
    public function getNodeHref($a_node) : string
    {
        return $this->url_builder->getHref($a_node['command'], $a_node['parameter']);
    }

    /**
     * @inheritdoc
     */
    public function isNodeClickable($a_node) : bool
    {
        return !is_null($a_node['parameter']);
    }

    /**
     * @inheritdoc
     */
    public function isNodeHighlighted($a_node) : bool
    {
        return $a_node['current'];
    }
}
