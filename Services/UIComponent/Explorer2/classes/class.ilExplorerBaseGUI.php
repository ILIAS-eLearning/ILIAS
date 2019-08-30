<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Explorer base GUI class.
 *
 * The class is supposed to work on a hierarchie of nodes that are identified
 * by IDs. Whether nodes are represented by associative arrays or objects
 * is not defined by this abstract class.
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesUIComponent
 */
abstract class ilExplorerBaseGUI
{
	/**
	 * @var Logger
	 */
	protected $log;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	//protected static $js_tree_path = "./libs/bower/bower_components/jstree/jquery.jstree.js";
	protected static $js_tree_path = "./libs/bower/bower_components/jstree/dist/jstree.js";
	protected static $js_tree_path_css = "./libs/bower/bower_components/jstree/dist/themes/default/style.min.css";

	protected static $js_expl_path = "./Services/UIComponent/Explorer2/js/Explorer2.js";
	protected $skip_root_node = false;
	protected $ajax = false;
	protected $custom_open_nodes = array();
	protected $selected_nodes = array();
	protected $select_postvar = "";
	protected $offline_mode = false;
	protected $sec_highl_nodes = array();
	protected $enable_dnd = false;
	protected $search_term = "";

	/**
	 * @var int 
	 */
	protected $child_limit = 0;

	private $nodeOnclickEnabled;

	/**
	 * Constructor
	 */
	public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd)
	{
		global $DIC;

		$this->log = $DIC["ilLog"];
		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC["tpl"];
		$this->id = $a_expl_id;
		$this->parent_obj = $a_parent_obj;
		$this->parent_cmd = $a_parent_cmd;
		// get open nodes
		include_once("./Services/Authentication/classes/class.ilSessionIStorage.php");
		$this->store = new ilSessionIStorage("expl2");
		$open_nodes = $this->store->get("on_".$this->id);
		$this->open_nodes = unserialize($open_nodes);
		if (!is_array($this->open_nodes))
		{
			$this->open_nodes = array();
		}

		$this->nodeOnclickEnabled = true;
	}
	
	/**
	 * Set child limit
	 *
	 * @param int $a_val child limit	
	 */
	function setChildLimit($a_val)
	{
		$this->child_limit = $a_val;
	}
	
	/**
	 * Get child limit
	 *
	 * @return int child limit
	 */
	function getChildLimit()
	{
		return $this->child_limit;
	}
	
	/**
	 * Set search term
	 *
	 * @param string $a_val search term	
	 */
	function setSearchTerm($a_val)
	{
		$this->search_term = $a_val;
	}
	
	/**
	 * Get search term
	 *
	 * @return string search term
	 */
	function getSearchTerm()
	{
		return $this->search_term;
	}
	
	
	/**
	 * Set main template (that is responsible for adding js/css)
	 *
	 * @param ilTemplate|null $a_main_tpl
	 */
	function setMainTemplate(ilTemplate $a_main_tpl = null)
	{
		$this->tpl = $a_main_tpl;
	}
	

	/**
	 * Get local path of explorer js
	 */
	static function getLocalExplorerJsPath()
	{
		return self::$js_expl_path;
	}

	/**
	 * Get local path of jsTree js
	 */
	static function getLocalJsTreeJsPath()
	{
		return self::$js_tree_path;
	}

	/**
	 * Get local path of jsTree js
	 */
	static function getLocalJsTreeCssPath()
	{
		return self::$js_tree_path_css;
	}

	/**
	 * Create html export directories
	 *
	 * @param string $a_target_dir target directory
	 */
	static function createHTMLExportDirs($a_target_dir)
	{
		ilUtil::makeDirParents($a_target_dir."/Services/UIComponent/Explorer2/lib/jstree-v.pre1.0");
		ilUtil::makeDirParents($a_target_dir."/Services/UIComponent/Explorer2/js");
	}



	//
	// Abstract functions that need to be overwritten in derived classes
	//
	
	/**
	 * Get root node.
	 *
	 * Please note that the class does not make any requirements how
	 * nodes are represented (array or object)
	 *
	 * @return mixed root node object/array
	 */
	abstract function getRootNode();
	
	/**
	 * Get childs of node
	 *
	 * @param string $a_parent_id parent node id
	 * @return array childs
	 */
	abstract function getChildsOfNode($a_parent_node_id);
	
	/**
	 * Get content of a node
	 *
	 * @param mixed $a_node node array or object
	 * @return string content of the node
	 */
	abstract function getNodeContent($a_node);

	/**
	 * Get id of a node
	 *
	 * @param mixed $a_node node array or object
	 * @return string id of node
	 */
	abstract function getNodeId($a_node);

	
	//
	// Functions with standard implementations that may be overwritten
	//

	/**
	 * Get href for node
	 *
	 * @param mixed $a_node node object/array
	 * @return string href attribute
	 */
	function getNodeHref($a_node)
	{
		return "#";
	}

	/**
	 * Node has childs?
	 *
	 * Please note that this standard method may not
	 * be optimal depending on what a derived class does in isNodeVisible.
	 *
	 * @param
	 * @return
	 */
	function nodeHasVisibleChilds($a_node)
	{
		$childs = $this->getChildsOfNode($this->getNodeId($a_node));

		foreach ($childs as $child)
		{
			if ($this->isNodeVisible($child))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Sort childs
	 *
	 * @param array $a_childs array of child nodes
	 * @param mixed $a_parent_node parent node
	 *
	 * @return array array of childs nodes
	 */
	function sortChilds($a_childs, $a_parent_node_id)
	{
		return $a_childs;
	}

	/**
	 * Get node icon path
	 *
	 * @param mixed $a_node node object/array
	 * @return string image file path
	 */
	function getNodeIcon($a_node)
	{
		return "";
	}

	/**
	 * Get node icon alt attribute
	 *
	 * @param mixed $a_node node object/array
	 * @return string image alt attribute
	 */
	function getNodeIconAlt($a_node)
	{
		return "";
	}

	/**
	 * Get node target (frame) attribute
	 *
	 * @param mixed $a_node node object/array
	 * @return string target
	 */
	function getNodeTarget($a_node)
	{
		return "";
	}

	/**
	 * Get node onclick attribute
	 *
	 * @param mixed $a_node node object/array
	 * @return string onclick value
	 */
	function getNodeOnClick($a_node)
	{
		if ($this->select_postvar != "")
		{			
			return $this->getSelectOnClick($a_node);
		}
		return "";
	}

		/**
	 * Is node visible?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node visible true/false
	 */
	function isNodeVisible($a_node)
	{
		return true;
	}

	/**
	 * Is node highlighted?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node highlighted true/false
	 */
	function isNodeHighlighted($a_node)
	{
		return false;
	}

	/**
	 * Is node clickable?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node clickable true/false
	 */
	function isNodeClickable($a_node)
	{
		return true;
	}

	/**
	 * Is node selectable?
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node selectable true/false
	 */
	protected function isNodeSelectable($a_node)
	{
		return true;
	}

	
	//
	// Basic configuration / setter/getter
	//
	
	/**
	 * Get id of explorer element
	 *
	 * @return integer id
	 */
	function getId()
	{
		return $this->id;
	}
	
	/**
	 * Set skip root node
	 *
	 * If set to false, the top node will not be displayed.
	 *
	 * @param boolean $a_val skip root node	
	 */
	function setSkipRootNode($a_val)
	{
		$this->skip_root_node = $a_val;
	}
	
	/**
	 * Get skip root node
	 *
	 * @return boolean skip root node
	 */
	function getSkipRootNode()
	{
		return $this->skip_root_node;
	}
	
	/**
	 * Set ajax
	 *
	 * @param boolean $a_val ajax	
	 */
	function setAjax($a_val)
	{
		$this->ajax = $a_val;
	}
	
	/**
	 * Get ajax
	 *
	 * @return boolean ajax
	 */
	function getAjax()
	{
		return $this->ajax;
	}

	/**
	 * Set secondary (background) highlighted nodes
	 *
	 * @param array $a_val array of node ids
	 */
	function setSecondaryHighlightedNodes($a_val)
	{
		$this->sec_highl_nodes = $a_val;
	}

	/**
	 * Get secondary (background) highlighted nodes
	 *
	 * @return array array of node ids
	 */
	function getSecondaryHighlightedNodes()
	{
		return $this->sec_highl_nodes;
	}
	
	/**
	 * Set node to be opened (additional custom opened node, not standard expand behaviour)
	 *
	 * @param
	 * @return
	 */
	function setNodeOpen($a_id)
	{
		if (!in_array($a_id, $this->custom_open_nodes))
		{
			$this->custom_open_nodes[] = $a_id;
		}
	}	

	/**
	 * Get onclick attribute for node toggling
	 *
	 * @param
	 * @return
	 */
	final protected function getNodeToggleOnClick($a_node)
	{
		return "$('#".$this->getContainerId()."').jstree('toggle_node' , '#".
			$this->getDomNodeIdForNodeId($this->getNodeId($a_node))."'); return false;";
	}

	/**
	 * Get onclick attribute for selecting radio/checkbox
	 *
	 * @param
	 * @return
	 */
	final protected function getSelectOnClick($a_node)
	{
		$dn_id = $this->getDomNodeIdForNodeId($this->getNodeId($a_node));
		$oc = "il.Explorer2.selectOnClick(event, '".$dn_id."'); return false;";
		return $oc;
	}	
	
	/**
	 * Set select mode (to deactivate, pass an empty string as postvar)
	 *
	 * @param string $a_postvar variable used for post, a "[]" is added automatically
	 * @param boolean $a_multi multi select (checkboxes) or not (radio)
	 * @return
	 */
	function setSelectMode($a_postvar, $a_multi = false)
	{
		$this->select_postvar = $a_postvar;
		$this->select_multi = $a_multi;
	}
	
	/**
	 * Set node to be opened (additional custom opened node, not standard expand behaviour)
	 *
	 * @param
	 * @return
	 */
	function setNodeSelected($a_id)
	{
		if (!in_array($a_id, $this->selected_nodes))
		{
			$this->selected_nodes[] = $a_id;
		}
	}

	/**
	 * Set offline mode
	 *
	 * @param bool $a_val offline mode
	 */
	function setOfflineMode($a_val)
	{
		$this->offline_mode = $a_val;
	}

	/**
	 * Get offline mode
	 *
	 * @return bool offline mode
	 */
	function getOfflineMode()
	{
		return $this->offline_mode;
	}

	//
	// Standard functions that usually are not overwritten / internal use
	//

	/**
	 * Handle explorer internal command.
	 *
	 * @return boolean true, if an internal command has been performed.
	 */
	function handleCommand()
	{
		if ($_GET["exp_cmd"] != "" &&
			$_GET["exp_cont"] == $this->getContainerId())
		{
			$cmd = $_GET["exp_cmd"];
			if (in_array($cmd, array("openNode", "closeNode", "getNodeAsync")))
			{
				$this->$cmd();
			}
			
			return true;
		}
		return false;
	}
	
	/**
	 * Get container id
	 *
	 * @param
	 * @return
	 */
	function getContainerId()
	{
		return "il_expl2_jstree_cont_".$this->getId();
	}
	
	/**
	 * Open node
	 */
	function openNode()
	{
		$ilLog = $this->log;
		
		$id = $this->getNodeIdForDomNodeId($_GET["node_id"]);
		if (!in_array($id, $this->open_nodes))
		{
			$this->open_nodes[] = $id;
		}
		$this->store->set("on_".$this->id, serialize($this->open_nodes));
		exit;
	}
	
	/**
	 * Close node
	 */
	function closeNode()
	{
		$ilLog = $this->log;
		
		$id = $this->getNodeIdForDomNodeId($_GET["node_id"]);
		if (in_array($id, $this->open_nodes))
		{
			$k = array_search($id, $this->open_nodes);
			unset($this->open_nodes[$k]);
		}
		$this->store->set("on_".$this->id, serialize($this->open_nodes));
		exit;
	}
	
	/**
	 * Get node asynchronously
	 */
	function getNodeAsync()
	{
		$this->beforeRendering();

		$etpl = new ilTemplate("tpl.explorer2.html", true, true, "Services/UIComponent/Explorer2");

		$root = $this->getNodeId($this->getRootNode());
		if (!in_array($root, $this->open_nodes))
		{
			$this->open_nodes[] = $root;
		}

		if ($_GET["node_id"] != "")
		{
			$id = $this->getNodeIdForDomNodeId($_GET["node_id"]);
			$this->setSearchTerm(ilUtil::stripSlashes($_GET["searchterm"]));
			$this->renderChilds($id, $etpl);
		}
		else
		{
			$id = $this->getNodeId($this->getRootNode());
			$this->renderNode($this->getRootNode(), $etpl);
		}
		echo $etpl->get("tag");
		exit;
	}

	/**
	 * Before rendering
	 */
	function beforeRendering()
	{

	}

	/**
	 * Get all open nodes
	 *
	 * @param
	 * @return
	 */
	protected function isNodeOpen($node_id)
	{
		return ($this->getNodeId($this->getRootNode()) == $node_id
			|| in_array($node_id, $this->open_nodes)
			|| in_array($node_id, $this->custom_open_nodes));
	}


	/**
	 * Get on load code
	 *
	 * @param
	 * @return
	 */
	function getOnLoadCode()
	{
		$ilCtrl = $this->ctrl;

		$container_id = $this->getContainerId();
		$container_outer_id = "il_expl2_jstree_cont_out_".$this->getId();

		// collect open nodes
		$open_nodes = array($this->getDomNodeIdForNodeId($this->getNodeId($this->getRootNode())));
		foreach ($this->open_nodes as $nid)
		{
			$open_nodes[] = $this->getDomNodeIdForNodeId($nid);
		}
		foreach ($this->custom_open_nodes as $nid)
		{
			$dnode = $this->getDomNodeIdForNodeId($nid);
			if (!in_array($dnode, $open_nodes))
			{
				$open_nodes[] = $dnode;
			}
		}

		// ilias config options
		$url = "";
		if (!$this->getOfflineMode())
		{
			if (is_object($this->parent_obj))
			{
				$url = $ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd, "", true);
			}
			else
			{
				$url = $ilCtrl->getLinkTargetByClass($this->parent_obj, $this->parent_cmd, "", true);
			}
		}

		// secondary highlighted nodes
		$shn = array();
		foreach ($this->sec_highl_nodes as $sh)
		{
			$shn[] = $this->getDomNodeIdForNodeId($sh);
		}
		$config = array(
			"container_id" => $container_id,
			"container_outer_id" => $container_outer_id,
			"url" => $url,
			"second_hnodes" => $shn,
			"ajax" => $this->getAjax(),
		);


		// jstree config options
		$js_tree_config = array(
			"core" => array(
				"animation" => 0,
				"initially_open" => $open_nodes,
				"open_parents" => false,
				"strings" => array("loading" => "Loading ...", "new_node" => "New node"),
				"themes" => array("dots" => false, "icons" => false, "theme" => "")
			),
			"plugins" => $this->getJSTreePlugins(),
			"html_data" => array()
		);

		return 'il.Explorer2.init('.json_encode($config).', '.json_encode($js_tree_config).');';
	}

	protected function getJSTreePlugins() {
		$plugins = array("html_data", "themes", "json_data");
		if($this->isEnableDnd()) {
			$plugins[] = "dnd";
		}
		return $plugins;
	}


	/**
	 * Init JS
	 */
	static function init($a_main_tpl = null)
	{
		global $DIC;

		if ($a_main_tpl == null)
		{
			$tpl = $DIC["tpl"];
		}
		else
		{
			$tpl = $a_main_tpl;
		}

		include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		iljQueryUtil::initjQuery($tpl);

		$tpl->addJavascript(self::getLocalExplorerJsPath());
		$tpl->addJavascript(self::getLocalJsTreeJsPath());
		$tpl->addCss(self::getLocalJsTreeCssPath());
	}
	
	
	/**
	 * Get HTML
	 */
	function getHTML()
	{
		$tpl = $this->tpl;
		$ilCtrl = $this->ctrl;

		$root = $this->getNodeId($this->getRootNode());
		if (!in_array($root, $this->open_nodes))
		{
			$this->open_nodes[] = $root;
		}

		$this->beforeRendering();

		self::init($tpl);
		$container_id = $this->getContainerId();
		$container_outer_id = "il_expl2_jstree_cont_out_".$this->getId();

		if (!$ilCtrl->isAsynch())
		{
			$tpl->addOnLoadCode($this->getOnLoadCode());
		}

		$etpl = new ilTemplate("tpl.explorer2.html", true, true, "Services/UIComponent/Explorer2");

		// render childs
		$root_node = $this->getRootNode();
		
		if (!$this->getSkipRootNode() &&
			$this->isNodeVisible($this->getRootNode()))
		{
			$this->listStart($etpl);
			$this->renderNode($this->getRootNode(), $etpl);
			$this->listEnd($etpl);
		}
		else
		{		
			$childs = $this->getChildsOfNode($this->getNodeId($root_node));
			$childs = $this->sortChilds($childs, $this->getNodeId($root_node));
			$any = false;
			foreach ($childs as $child_node)
			{
				if ($this->isNodeVisible($child_node))
				{
					if (!$any)
					{
						$this->listStart($etpl);
						$any = true;
					}
					$this->renderNode($child_node, $etpl);
				}
			}
			if ($any)
			{
				$this->listEnd($etpl);
			}
		}
		
		$etpl->setVariable("CONTAINER_ID", $container_id);
		$etpl->setVariable("CONTAINER_OUTER_ID", $container_outer_id);

		$add = "";
		if ($ilCtrl->isAsynch())
		{
			$add = "<script>".$this->getOnLoadCode()."</script>";
		}

		$content = $etpl->get();
//echo $content.$add; exit;
		return $content.$add;
	}
	
	/**
	 * Render node
	 *
	 * @param
	 * @return
	 */
	function renderNode($a_node, $tpl)
	{
		$skip = ($this->getSkipRootNode()
			&& $this->getNodeId($this->getRootNode()) == $this->getNodeId($a_node));
		if (!$skip)
		{
			$this->listItemStart($tpl, $a_node);

			// select mode?
			if ($this->select_postvar != "" && $this->isNodeSelectable($a_node))
			{
				if ($this->select_multi)
				{
					$tpl->setCurrentBlock("cb");
					if (in_array($this->getNodeId($a_node), $this->selected_nodes))
					{
						$tpl->setVariable("CHECKED", 'checked="checked"');
					}
					$tpl->setVariable("CB_VAL", $this->getNodeId($a_node));
					$tpl->setVariable("CB_NAME", $this->select_postvar . "[]");
					$tpl->parseCurrentBlock();
				} else
				{
					$tpl->setCurrentBlock("rd");
					if (in_array($this->getNodeId($a_node), $this->selected_nodes))
					{
						$tpl->setVariable("SELECTED", 'checked="checked"');
					}
					$tpl->setVariable("RD_VAL", $this->getNodeId($a_node));
					$tpl->setVariable("RD_NAME", $this->select_postvar);
					$tpl->parseCurrentBlock();
				}
			}


			if ($this->isNodeHighlighted($a_node))
			{
				$tpl->touchBlock("hl");
			}
			$tpl->setCurrentBlock("content");
			if ($this->getNodeIcon($a_node) != "")
			{
				$tpl->setVariable("ICON", ilUtil::img($this->getNodeIcon($a_node), $this->getNodeIconAlt($a_node)) . " ");
			}
			$tpl->setVariable("CONTENT", $this->getNodeContent($a_node));
			if ($this->isNodeClickable($a_node))
			{
				$tpl->setVariable("HREF", $this->getNodeHref($a_node));
			}
			$target = $this->getNodeTarget($a_node);
			if ($target != "")
			{
				$targetRelatedParams = array(
					'target="' . $target . '"'
				);

				if ('_blank' === $target)
				{
					$targetRelatedParams[] = 'rel="noopener"';
				}

				$tpl->setVariable('TARGET', implode(' ', $targetRelatedParams));
			}
			if (!$this->isNodeOnclickEnabled() || !$this->isNodeClickable($a_node))
			{
				$tpl->setVariable("ONCLICK", 'onclick="return false;"');
				$tpl->setVariable("A_CLASS", 'class="disabled"');
			} else
			{
				$onclick = $this->getNodeOnClick($a_node);
				if ($onclick != "")
				{
					$tpl->setVariable("ONCLICK", 'onclick="' . $onclick . '"');
				}
			}
			$tpl->parseCurrentBlock();

			$tpl->touchBlock("tag");
		}
		
		if (!$this->getAjax() || in_array($this->getNodeId($a_node), $this->open_nodes)
			|| in_array($this->getNodeId($a_node), $this->custom_open_nodes))
		{
			$this->renderChilds($this->getNodeId($a_node), $tpl);
		}
		
		if (!$skip)
		{
			$this->listItemEnd($tpl);
		}
	}
	
	/**
	 * Render childs
	 *
	 * @param
	 * @return
	 */
	final function renderChilds($a_node_id, $tpl)
	{
		$childs = $this->getChildsOfNode($a_node_id);
		$childs = $this->sortChilds($childs, $a_node_id);

		if (count($childs) > 0 || $this->getSearchTerm() != "")
		{
			// collect visible childs

			$visible_childs = [];
			$cnt_child = 0;

			foreach ($childs as $child)
			{
				$cnt_child++;
				if ($this->getChildLimit() > 0 && $this->getChildLimit() < $cnt_child)
				{
					continue;
				}

				if ($this->isNodeVisible($child))
				{
					$visible_childs[] = $child;
				}
			}

			// search field, if too many childs
			$any = false;
			if ($this->getChildLimit() > 0 && $this->getChildLimit() < $cnt_child
				|| $this->getSearchTerm() != "")
			{
				if (!$any)
				{
					$this->listStart($tpl);
					$any = true;
				}
				$tpl->setCurrentBlock("list_search");
				$tpl->setVariable("SEARCH_CONTAINER_ID", $a_node_id);
				$tpl->setVariable("SEARCH_VAL", $this->getSearchTerm());
				$tpl->parseCurrentBlock();
				$tpl->touchBlock("tag");
			}

			// render visible childs
			foreach ($visible_childs as $child)
			{
				// check child limit
				$cnt_child++;

				if ($this->isNodeVisible($child))
				{
					if (!$any)
					{
						$this->listStart($tpl);
						$any = true;
					}
					$this->renderNode($child, $tpl);
				}
			}
			if ($any)
			{
				$this->listEnd($tpl);
			}
		}
	}

	/**
	 * Get DOM node id for node id
	 *
	 * @param
	 * @return
	 */
	function getDomNodeIdForNodeId($a_node_id)
	{
		return "exp_node_".$this->getId()."_".$a_node_id;
	}
	
	/**
	 * Get node id for dom node id
	 *
	 * @param
	 * @return
	 */
	function getNodeIdForDomNodeId($a_dom_node_id)
	{
		$i = strlen("exp_node_".$this->getId()."_");
		return substr($a_dom_node_id, $i);
	}
		
	/**
	 * List item start
	 *
	 * @param
	 * @return
	 */
	function listItemStart($tpl, $a_node)
	{
		$tpl->setCurrentBlock("list_item_start");
		if ($this->getAjax() && $this->nodeHasVisibleChilds($a_node) && !$this->isNodeOpen($this->getNodeId($a_node)))
		{
			$tpl->touchBlock("li_closed");
		}
		if ($this->isNodeOpen($this->getNodeId($a_node)))
		{
			$tpl->touchBlock("li_opened");
		}

		$tpl->setVariable("DOM_NODE_ID",
			$this->getDomNodeIdForNodeId($this->getNodeId($a_node)));
		$tpl->parseCurrentBlock();
		$tpl->touchBlock("tag");
	}

	/**
	 * List item end
	 *
	 * @param
	 * @return
	 */
	function listItemEnd($tpl)
	{
		$tpl->touchBlock("list_item_end");
		$tpl->touchBlock("tag");
	}

	/**
	 * List start
	 *
	 * @param
	 * @return
	 */
	function listStart($tpl)
	{
		$tpl->touchBlock("list_start");
		$tpl->touchBlock("tag");
	}

	/**
	 * List end
	 *
	 * @param
	 * @return
	 */
	function listEnd($tpl)
	{
		$tpl->touchBlock("list_end");
		$tpl->touchBlock("tag");
	}

	/**
	 * @return boolean
	 */
	public function isNodeOnclickEnabled()
	{
		return $this->nodeOnclickEnabled;
	}

	/**
	 * @param boolean $nodeOnclickEnabled
	 */
	public function setNodeOnclickEnabled($nodeOnclickEnabled)
	{
		$this->nodeOnclickEnabled = $nodeOnclickEnabled;
	}

	public function isEnableDnd() {
		return $this->enable_dnd;
	}

	/**
	 * Enable Drag & Drop functionality
	 * @param boolean $enable_dnd
	 */
	public function setEnableDnd($enable_dnd) {
		$this->enable_dnd = $enable_dnd;
	}
}

?>
