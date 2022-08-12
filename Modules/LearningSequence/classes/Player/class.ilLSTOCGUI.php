<?php

declare(strict_types=1);

use ILIAS\KioskMode\TOCBuilder;

/**
 * Tree-GUI for ToC
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */

class ilLSTOCGUI extends ilExplorerBaseGUI
{
    /**
     * @deprecated will be deleted with R8
     */
    const NODE_ICONS = [
        TOCBuilder::LP_NOT_STARTED => "./templates/default/images/scorm/not_attempted.svg",
        TOCBuilder::LP_IN_PROGRESS => "./templates/default/images/scorm/incomplete.svg",
        TOCBuilder::LP_COMPLETED => "./templates/default/images/scorm/completed.svg",
        TOCBuilder::LP_FAILED => "./templates/default/images/scorm/failed.svg"
    ];

    protected $id = 'ls_toc';
    protected $structure;
    protected $nodes = [];
    /**
     * @var UrlBuilder
     */
    protected $url_builder;

    /**
     * @var array<string>
     */
    protected $node_icons = [
        TOCBuilder::LP_NOT_STARTED => '',
        TOCBuilder::LP_IN_PROGRESS => '',
        TOCBuilder::LP_COMPLETED => '',
        TOCBuilder::LP_FAILED => ''
    ];

    public function __construct(
        LSUrlBuilder $url_builder,
        ilCtrl $il_ctrl
    ) {
        parent::__construct("lsq_toc", null, "");
        $this->url_builder = $url_builder;
        $this->ctrl = $il_ctrl; //ilExplorerBaseGUI needs ctrl...
        $this->setSkipRootNode(false);
        $this->setNodeOnclickEnabled(true);

        //get the image paths to the node icons
        $lp_icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SHORT);
        $this->node_icons[TOCBuilder::LP_NOT_STARTED] = $lp_icons->getImagePathNotAttempted();
        $this->node_icons[TOCBuilder::LP_COMPLETED] = $lp_icons->getImagePathCompleted();
        $this->node_icons[TOCBuilder::LP_IN_PROGRESS] = $lp_icons->getImagePathInProgress();
        $this->node_icons[TOCBuilder::LP_FAILED] = $lp_icons->getImagePathFailed();
    }

    public function withStructure(string $json_structure)
    {
        $clone = clone $this;
        $clone->structure = $clone->addIds(
            json_decode($json_structure, true)
        );
        $clone->buildLookup($clone->structure); //sets $this->nodes
        $clone->open_nodes = array_keys($clone->nodes); //all open
        return $clone;
    }

    protected $counter = 0;
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

    protected function buildLookup(array $node)
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
    public function getChildsOfNode($a_parent_node_id)
    {
        $parent_node = $this->nodes[$a_parent_node_id];
        return (array) $parent_node['childs'];
    }

    /**
     * @inheritdoc
     */
    public function getNodeContent($a_node)
    {
        return $a_node['label'];
    }

    /**
     * @inheritdoc
     */
    public function getNodeIcon($a_node)
    {
        $state = $a_node['state'] ?? TOCBuilder::LP_NOT_STARTED;
        return $this->node_icons[$state];
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
    public function getNodeHref($a_node)
    {
        return $this->url_builder->getHref($a_node['command'], $a_node['parameter']);
    }

    /**
     * @inheritdoc
     */
    public function isNodeClickable($a_node)
    {
        return !is_null($a_node['parameter']);
    }

    /**
     * @inheritdoc
     */
    public function isNodeHighlighted($a_node)
    {
        return $a_node['current'];
    }
}
