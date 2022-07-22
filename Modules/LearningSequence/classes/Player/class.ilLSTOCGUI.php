<?php declare(strict_types=1);

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
 
use ILIAS\KioskMode\TOCBuilder;

/**
 * Tree-GUI for ToC
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

    /**
     * @var array<string>
     */
    protected array $node_icons = [
        TOCBuilder::LP_NOT_STARTED => '',
        TOCBuilder::LP_IN_PROGRESS => '',
        TOCBuilder::LP_COMPLETED => '',
        TOCBuilder::LP_FAILED => ''
    ];

    public function __construct(LSUrlBuilder $url_builder)
    {
        parent::__construct("lsq_toc", null, "");

        $this->url_builder = $url_builder;
        $this->setSkipRootNode(false);
        $this->setNodeOnclickEnabled(true);

        //get the image paths to the node icons
        $lp_icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SHORT);
        $this->node_icons[TOCBuilder::LP_NOT_STARTED] = $lp_icons->getImagePathNotAttempted();
        $this->node_icons[TOCBuilder::LP_COMPLETED] = $lp_icons->getImagePathCompleted();
        $this->node_icons[TOCBuilder::LP_IN_PROGRESS] = $lp_icons->getImagePathInProgress();
        $this->node_icons[TOCBuilder::LP_FAILED] = $lp_icons->getImagePathFailed();
    }

    public function withStructure(string $json_structure) : self
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
