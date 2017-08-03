<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php';

/**
 * Class ilForumExplorerGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumExplorerGUI extends ilExplorerBaseGUI
{
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

	/**
	 * {@inheritdoc}
	 */
	public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd)
	{
		global $DIC;

		parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd);

		$this->setSkipRootNode(false);
		$this->setAjax(true);

		$this->tpl  = $DIC->ui()->mainTemplate();
		$this->ctrl = $DIC->ctrl();

		$frm               = new ilForum();
		$this->max_entries = (int)$frm->getPageHits();
	}

	/**
	 * @return ilForumTopic
	 */
	public function getThread()
	{
		return $this->thread;
	}

	/**
	 * @param ilForumTopic $thread
	 */
	public function setThread($thread)
	{
		$this->thread    = $thread;
		$this->root_node = $thread->getFirstPostNode();
		$this->root_node->setIsRead($this->root_node->isRead($this->root_node->getPosAuthorId()));

		$this->ctrl->setParameter($this->parent_obj, 'thr_pk', $this->thread->getId());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRootNode()
	{
		if(null === $this->root_node)
		{
			$this->root_node = $this->thread->getFirstPostNode();
		}

		return array(
			'pos_pk'              => $this->root_node->getId(),
			'pos_subject'         => $this->root_node->getSubject(),
			'pos_author_id'       => $this->root_node->getPosAuthorId(),
			'pos_display_user_id' => $this->root_node->getDisplayUserId(),
			'pos_usr_alias'       => $this->root_node->getUserAlias(),
			'pos_date'            => $this->root_node->getCreateDate(),
			'import_name'         => $this->root_node->getImportName(),
			'post_read'           => $this->root_node->isPostRead()
		);
	}

	/**
	 * Factory method for a new instance of a node template
	 * @return ilTemplate
	 */
	protected function getNodeTemplateInstance() {
		return new ilTemplate('tpl.tree_node_content.html', true, true, 'Modules/Forum');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getChildsOfNode($a_parent_node_id)
	{
		if($this->preloaded)
		{
			if(isset($this->preloaded_children[$a_parent_node_id]))
			{
				return $this->preloaded_children[$a_parent_node_id];
			}
		}

		return $this->thread->getNestedSetPostChildren($a_parent_node_id, 1);
	}

	/**
	 *
	 */
	public function preloadChildren()
	{
		$this->preloaded_children            = array();
		$this->node_id_to_parent_node_id_map = array();

		$children = $this->thread->getNestedSetPostChildren($this->root_node->getId());

		array_walk($children, function(&$a_node, $key) {
			$this->node_id_to_parent_node_id_map[(int)$a_node['pos_pk']] = (int)$a_node['parent_pos'];
			$this->preloaded_children[(int)$a_node['parent_pos']][$a_node['pos_pk']] = $a_node;
		});

		$this->preloaded = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNodeContent($a_node)
	{
		$tpl = $this->getNodeTemplateInstance();

		$tpl->setCurrentBlock('node-content-block');
		$tpl->setVariable('TITLE', $a_node['pos_subject']);
		$tpl->setVariable('TITLE_CLASSES', implode(' ', $this->getNodeTitleClasses($a_node)));
		$tpl->parseCurrentBlock();

		require_once 'Modules/Forum/classes/class.ilForumAuthorInformation.php';
		$authorinfo = new ilForumAuthorInformation(
			$a_node['pos_author_id'],
			$a_node['pos_display_user_id'],
			$a_node['pos_usr_alias'],
			$a_node['import_name']
		);

		$tpl->setCurrentBlock('unlinked-node-content-block');
		$tpl->setVariable('UNLINKED_CONTENT_CLASS', $this->getUnlinkedNodeContentClass());
		$tpl->setVariable('AUTHOR', $authorinfo->getAuthorShortName());
		$tpl->setVariable('DATE', ilDatePresentation::formatDate(new ilDateTime($a_node['pos_date'], IL_CAL_DATETIME)));
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}

	/**
	 * @param array $node_config
	 * @return array
	 */
	protected function getNodeTitleClasses(array $node_config)
	{
		$node_title_classes = array('ilForumTreeTitle');

		if(isset($node_config['post_read']) && !$node_config['post_read'])
		{
			$node_title_classes[] = 'ilForumTreeTitleUnread';
		}

		return $node_title_classes;
	}

	/**
	 * @return string
	 */
	protected function getUnlinkedNodeContentClass()
	{
		return 'ilForumTreeUnlinkedContent';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNodeHref($a_node)
	{
		$this->ctrl->setParameter($this->parent_obj, 'backurl', null);
		$this->ctrl->setParameter($this->parent_obj, 'pos_pk', $a_node['pos_pk']);

		if(isset($a_node['counter']))
		{
			$this->ctrl->setParameter($this->parent_obj, 'offset', floor($a_node['counter'] / $this->max_entries) * $this->max_entries);
		}

		if(isset($a_node['post_read']) && $a_node['post_read'])
		{
			return $this->ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd, $a_node['pos_pk']);
		}
		else
		{
			return $this->ctrl->getLinkTarget($this->parent_obj, 'markPostRead', $a_node['pos_pk']);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNodeId($a_node)
	{
		return $a_node['pos_pk'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNodeIcon($a_node)
	{
		return ilObject::_getIcon(0, 'tiny', 'frm');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHTML()
	{
		$this->preloadChildren();

		if(isset($_GET['post_created_below']) && (int)$_GET['post_created_below'] > 0)
		{
			$parent  = (int)$_GET['post_created_below'];
			do
			{
				$this->setNodeOpen((int)$parent);
			}
			while($parent = $this->node_id_to_parent_node_id_map[$parent]);
		}

		$html = parent::getHTML();

		$this->tpl->addOnLoadCode('il.ForumExplorer.init(' . json_encode(array(
			'selectors' => array(
				'container'        => '#' . $this->getContainerId(),
				'unlinked_content' => '.' . $this->getUnlinkedNodeContentClass()
			)
		)) . ');');

		$this->tpl->addJavascript($this->js_explorer_frm_path);

		return $html;
	}
}