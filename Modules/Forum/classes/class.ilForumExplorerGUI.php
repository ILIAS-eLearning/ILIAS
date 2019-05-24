<?php
declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Tree\TreeRecursion;

/**
 * Class ilForumExplorerGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumExplorerGUI implements TreeRecursion
{
    /**
     * @var
     */
    private $parent_obj;

    /**
     * @var
     */
    private $id;

    /**
     * @var \ILIAS\DI\UIServices
     */
    private $ui;

    /** @var  */
    private $parent_cmd;

    /**
     * @var string
     */
    protected $js_explorer_frm_path = './Modules/Forum/js/ilForumExplorer.js';

    /**
     * @var ilForumTopic
     */
    protected $thread;

    /**
     * @var int
     */
    protected $max_entries = PHP_INT_MAX;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var bool
     */
    protected $preloaded = false;

    /**
     * @var array
     */
    protected $preloaded_children = array();

    /**
     * @var array
     */
    protected $node_id_to_parent_node_id_map = array();

    /**
     * @var ilForumPost|null
     */
    protected $root_node = null;

    /** @var \ilForumAuthorInformation[] */
    protected $authorInformation = [];

    /** @var array */
    private $custom_open_nodes = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd, ilForumTopic $thread)
    {
        global $DIC;

        $this->id = $a_expl_id;

        $this->ui = $DIC->ui();
        $this->tpl  = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;

        $frm               = new ilForum();
        $this->max_entries = (int) $frm->getPageHits();

        $this->thread    = $thread;
        $this->root_node = $thread->getFirstPostNode();

        $this->setNodeOpen($this->root_node->getId());

        $this->ctrl->setParameter($this->parent_obj, 'thr_pk', $this->thread->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getChildsOfNode($a_parent_node_id)
    {
        if ($this->preloaded) {
            if (isset($this->preloaded_children[$a_parent_node_id])) {
                return $this->preloaded_children[$a_parent_node_id];
            }
        }

        return $this->thread->getNestedSetPostChildren($a_parent_node_id, 1);
    }

    /**
     * @param array $node_config
     * @return array
     */
    protected function getNodeTitleClasses(array $node_config)
    {
        $node_title_classes = array('ilForumTreeTitle');

        if ($this->root_node->getId() == $node_config['pos_pk']) {
            return $node_title_classes;
        }

        if (isset($node_config['post_read']) && !$node_config['post_read']) {
            $node_title_classes[] = 'ilForumTreeTitleUnread';
        }

        return $node_title_classes;
    }

    /**
     * {@inheritdoc}
     */
    public function getHTML()
    {
        $this->preloadChildren();

        return $this->render();
    }

    /**
     * Get a list of records (that list can also be empty).
     * Each record will be relayed to $this->build to retrieve a Node.
     * Also, each record will be asked for Sub-Nodes using this function.
     * @return array
     */
    public function getChildren($record, $environment = null) : array
    {
        return $this->getChildsOfNode($record['pos_pk']);
    }

    /**
     * Get Tree UI
     *
     * @return Tree|object
     */
    public function getTreeComponent()
    {
        $f = $this->ui->factory();
        /** @var ilTree $tree */

        $data = $this->thread->getNestedSetPostChildren($this->root_node->getId());

        $endData = array();
        foreach ($data as $node) {
            if ($node['depth'] == 2) {
                $endData[] = $node;
            }
        }

        $tree = $f->tree()
                  ->expandable($this)
                  ->withData($endData)
                  ->withHighlightOnNodeClick(true);

        return $tree;
    }

    /**
     * Build and return a Node.
     * The renderer will provide the $factory-parameter which is the UI-factory
     * for nodes, as well as the (unspecified) $environment as configured at the Tree.
     * $record is the data the node should be build for.
     * @param \ILIAS\UI\Component\Tree\Node\Factory $factory
     * @param                                       $record
     * @param null                                  $environment
     * @return \ILIAS\UI\Component\Tree\Node\Node
     * @throws ilDateTimeException
     */
    public function build(\ILIAS\UI\Component\Tree\Node\Factory $factory, $record, $environment = null) : \ILIAS\UI\Component\Tree\Node\Node
    {
        $node = $this->createNode($factory, $record);

        $href = $this->getNodeHref($record);

        if ($href) {
            $node = $node->withAdditionalOnLoadCode(function ($id) use ($href) {
                $js = "$('#$id').find('.node-label').on('click', function(event) {
                    window.location = '{$href}';
                    return false;
                });";
                return $js;
            });
        }

        return $node;
    }

    /**
     * Render tree
     *
     * @return string
     */
    private function render()
    {
        $renderer = $this->ui->renderer();

        return $renderer->render([
            $this->getTreeComponent()
        ]);
    }

    /**
     * Set node to be opened (additional custom opened node, not standard expand behaviour)
     *
     * @param
     * @return
     */
    private function setNodeOpen($a_id)
    {
        if (!in_array($a_id, $this->custom_open_nodes)) {
            $this->custom_open_nodes[] = $a_id;
        }
    }

    /**
     * @param $factory
     * @param $node
     * @return mixed
     * @throws ilDateTimeException
     */
    private function createNode(
        \ILIAS\UI\Component\Tree\Node\Factory $factory,
        $node
    ) {
        global $DIC;

        $path = $this->getNodeIcon($node);

        $icon = $DIC->ui()
                    ->factory()
                    ->symbol()
                    ->icon()
                    ->custom($path, 'forum');

        $authorInfo = $this->getAuthorInformationByNode($node);
        $creationDate = ilDatePresentation::formatDate(new ilDateTime($node['pos_date'], IL_CAL_DATETIME));
        $bylineString = $authorInfo->getAuthorShortName() . ', ' . $creationDate;

        $simple = $factory->bylined($node['pos_subject'], $bylineString, $icon);

        return $simple;
    }

    /**
     * @param array $node
     * @return \ilForumAuthorInformation
     */
    private function getAuthorInformationByNode(array $node): \ilForumAuthorInformation
    {
        if (isset($this->authorInformation[(int) $node['pos_pk']])) {
            return $this->authorInformation[(int) $node['pos_pk']];
        }

        return $this->authorInformation[(int) $node['pos_pk']] = new ilForumAuthorInformation(
            $node['pos_author_id'],
            $node['pos_display_user_id'],
            $node['pos_usr_alias'],
            $node['import_name']
        );
    }

    /**
     * {@inheritdoc}
     */
    private function getNodeIcon($a_node)
    {
        if ($this->root_node->getId() == $a_node['pos_pk']) {
            return ilObject::_getIcon(0, 'tiny', 'frm');
        }

        return $this->getAuthorInformationByNode($a_node)->getProfilePicture();
    }

    private function preloadChildren()
    {
        $this->preloaded_children            = array();
        $this->node_id_to_parent_node_id_map = array();

        $children = $this->thread->getNestedSetPostChildren($this->root_node->getId());

        array_walk($children, function (&$a_node, $key) {
            $this->node_id_to_parent_node_id_map[(int) $a_node['pos_pk']]             = (int) $a_node['parent_pos'];
            $this->preloaded_children[(int) $a_node['parent_pos']][$a_node['pos_pk']] = $a_node;
        });

        $this->preloaded = true;
    }

    private function getNodeHref($a_node)
    {
        $this->ctrl->setParameter($this->parent_obj, 'backurl', null);
        $this->ctrl->setParameter($this->parent_obj, 'pos_pk', $a_node['pos_pk']);

        if (isset($a_node['counter']) && $a_node['counter'] > 0) {
            $this->ctrl->setParameter(
                $this->parent_obj,
                'page',
                floor(($a_node['counter'] - 1) / $this->max_entries)
            );
        }

        if (isset($a_node['post_read']) && $a_node['post_read']) {
            return $this->ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd, $a_node['pos_pk'], false, false);
        }

        return $this->ctrl->getLinkTarget($this->parent_obj, 'markPostRead', $a_node['pos_pk'], false, false);
    }

}
