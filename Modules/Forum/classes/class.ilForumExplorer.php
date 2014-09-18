<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/YUI/classes/class.ilYuiUtil.php';
include_once 'Services/JSON/classes/class.ilJsonUtil.php';


/**
 * Class ilForumExplorer
 * class for explorer view of forum posts
 * @author  Stefan Meyer <meyer@leifos.com>
 * @author  Nadia Ahmad <nahmad@databay.de>
 * @author  Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 * @ingroup ModulesForum
 */
class ilForumExplorer
{
	/**
	 * Template object
	 * @var ilTemplate
	 * @access protected

	 */
	protected $tpl;

	/**
	 * Current forum
	 * @var ilObjForumGUI
	 * @access protected

	 */
	protected $gui;

	/**
	 * Current topic
	 * @var ilForumTopic
	 * @access protected

	 */
	protected $topic;

	/**
	 * Property object for the current forum context
	 * @var ilForumProperties
	 * @access protected

	 */
	protected $properties;

	/**
	 * Root id of the thread tree
	 * @var integer
	 * @access protected

	 */
	protected $root_id;

	/**
	 * Constructor
	 * @access	public

	 */
	public function __construct(ilObjForumGUI $gui, ilForumTopic $topic, ilForumProperties $properties)
	{
		global $tpl, $ilCtrl;

		$this->gui        = $gui;
		$this->topic      = $topic;
		$this->properties = $properties;

		$this->tpl = new ilTemplate('tpl.frm_tree.html', true, true, 'Modules/Forum');

		ilYuiUtil::initConnection();
		$tpl->addJavaScript(ilYuiUtil::getLocalPath() . '/yahoo/yahoo-min.js');
		$tpl->addJavaScript(ilYuiUtil::getLocalPath() . '/event/event-min.js');
		$tpl->addJavaScript('./Modules/Forum/js/treeview.js');
		$tpl->addJavaScript('./Modules/Forum/js/treeview_extensions.js');
		$tpl->addCss('./Modules/Forum/css/forum_tree.css');

		// Set ref_id for urls
		$ilCtrl->setParameter($this->gui, 'thr_pk', $this->topic->getId());
		$ilCtrl->setParameter($this->gui, 'backurl', null);

		// Set urls for async commands
		$this->tpl->setVariable('THR_TREE_STATE_URL', $ilCtrl->getLinkTarget($this->gui, 'setTreeStateAsynch', '', true, false));
		$this->tpl->setVariable('THR_TREE_FETCH_CHILDREN_URL', $ilCtrl->getLinkTarget($this->gui, 'fetchTreeChildrenAsync', '', true, false));

		// Fetch root id of the thread node
		$this->root_id = $this->topic->getFirstPostNode()->getId();

		if(!is_array($_SESSION['frm'][$this->topic->getId()]['openTreeNodes']))
		{
			$_SESSION['frm'][(int)$this->topic->getId()]['openTreeNodes'] = array(0);
		}

		// Prevent key gaps
		shuffle($_SESSION['frm'][(int)$this->topic->getId()]['openTreeNodes']);
	}

	public function render()
	{
		$this->fillTreeTemplate();

		return $this;
	}

	public function fillTreeTemplate()
	{		
		$emptyOnLoad = false;

		$nodes_to_request = $_SESSION['frm'][(int)$this->topic->getId()]['openTreeNodes'];
		if(!$_SESSION['frm'][(int)$this->topic->getId()]['openTreeNodes'] ||
		   (count($_SESSION['frm'][(int)$this->topic->getId()]['openTreeNodes']) == 1 && $_SESSION['frm'][(int)$this->topic->getId()]['openTreeNodes'][0] == 0))
		{
			$emptyOnLoad = true;
			$nodes_to_request = array();
		}

		$objects = $this->topic->getNestedSetPostChildren(null, $nodes_to_request);

		$counter = 0;

		$onloadNodes              = array();
		$nodesFetchedWithChildren = array();

		$frm      = new ilForum();
		$pageHits = $frm->getPageHits();

		include_once 'Services/JSON/classes/class.ilJsonUtil.php';

		foreach($objects as $object)
		{
			if($object['pos_pk'] != $this->root_id &&
				!in_array($object['parent_pos'], $onloadNodes)
			)
			{
				continue;
			}

			if(in_array((int)$object['parent_pos'], $onloadNodes) &&
				!in_array((int)$object['parent_pos'], $nodesFetchedWithChildren)
			)
			{
				$nodesFetchedWithChildren[] = (int)$object['parent_pos'];
			}

			$html = self::getTreeNodeHtml(
				$object, $this->gui, $pageHits
			);

			$hasChildren = ($object['children'] >= 1);

			$node       = new stdClass();
			$node->html = $html;

			if($object['pos_pk'] == $this->root_id)
			{
				$this->tpl->setVariable('FRM_TREE_ROOT_NODE_VARIABLE', 'frmNode' . $object['pos_pk']);
				$this->tpl->setVariable('FRM_TREE_ROOT_NODE_LINK', ilJsonUtil::encode($node));
				$this->tpl->setVariable('FRM_TREE_ROOT_NODE_HAS_CHILDREN', $hasChildren ? 'true' : 'false');
			}
			else
			{
				$this->tpl->setCurrentBlock('frm_nodes');
				$this->tpl->setVariable('FRM_NODES_VARNAME', 'frmNode' . $object['pos_pk']);
				$this->tpl->setVariable('FRM_NODES_PARENT_VARNAME', 'frmNode' . $object['parent_pos']);
				$this->tpl->setVariable('FRM_NODES_LINK', ilJsonUtil::encode($node));
				$this->tpl->setVariable('FRM_NODES_HAS_CHILDREN', $hasChildren ? 'true' : 'false');
				$this->tpl->parseCurrentBlock();
			}

			$onloadNodes[] = (int)$object['pos_pk'];

			++$counter;
		}
		$this->tpl->setVariable('THR_ONLOAD_NODES', ilJsonUtil::encode($onloadNodes));
		$this->tpl->setVariable('THR_ONLOAD_NODES_FETCHED_WITH_CHILDREN', ilJsonUtil::encode($nodesFetchedWithChildren));

		if($emptyOnLoad)
		{
			$this->tpl->setVariable('THR_OPEN_NODES', ilJsonUtil::encode($onloadNodes));
			$_SESSION['frm'][(int)$this->topic->getId()]['openTreeNodes'] = array_unique(array_merge(array(0), $onloadNodes)); 
		}
		else
		{
			$this->tpl->setVariable('THR_OPEN_NODES', ilJsonUtil::encode($_SESSION['frm'][(int)$this->topic->getId()]['openTreeNodes']));
		}
	}

	/**
	 * Returns the html used for a single forum tree node
	 * @access	public
	 * @static

	 */
	public static function getTreeNodeHtml($object, ilObjForumGUI $gui, $pageHits)
	{
		global $ilCtrl;

		$html = '';

		// Set pos_pk for urls
		$ilCtrl->setParameter($gui, 'pos_pk', $object['pos_pk']);

		// Set offset
		$ilCtrl->setParameter($gui, 'offset', floor($object['counter'] / $pageHits) * $pageHits);

		// @todo: HTML in PHP used because of performance issues of ilTemplate an big forum trees
		if($object['post_read'])
		{
			$url  = $ilCtrl->getLinkTarget($gui, 'viewThread', $object['pos_pk']);
			$link = "<a class='small' href='" . $url . "'>" . stripslashes($object['pos_subject']) . "</a>";

			$html .= "<div class='frmTreeInfo'><span class='frmTitle' id='frm_node_" . $object['pos_pk'] . "'>" . $link . "</span><br />" .
				"<span id='frm_node_desc_" . $object['pos_pk'] . "' class='small'>";
		}
		else
		{
			$url  = $ilCtrl->getLinkTarget($gui, 'markPostRead', $object['pos_pk']);
			$link = "<a class='small' href='" . $url . "'>" . stripslashes($object['pos_subject']) . "</a>";

			$html .= "<div class='frmTreeInfo'><span class='frmTitleBold' id='frm_node_" . $object['pos_pk'] . "'>" . $link . "</span><br />" .
				"<span id='frm_node_desc_" . $object['pos_pk'] . "' class='small'>";
		}

		require_once 'Modules/Forum/classes/class.ilForumAuthorInformation.php';
		$authorinfo = new ilForumAuthorInformation(
			$object['pos_author_id'],
			$object['pos_display_user_id'],
			$object['pos_usr_alias'],
			$object['import_name']
		);
		$html .= $authorinfo->getAuthorShortName();
		$html .= ", " . ilDatePresentation::formatDate(new ilDateTime($object['pos_date'], IL_CAL_DATETIME)) . "</span></div>";

		return $html;
	}

	/**
	 * This method returns the tree html for the forum template
	 * @access	public
	 * @return	string

	 */
	public function getHtml()
	{
		return $this->tpl->get();
	}
}