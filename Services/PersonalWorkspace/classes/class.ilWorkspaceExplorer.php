<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Repository/classes/class.ilRepositoryExplorer.php';

/*
 * Explorer for workspace tree (used in move action per item)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 */
class ilWorkspaceExplorer extends ilRepositoryExplorer
{
	const SEL_TYPE_CHECK = 1;
	const SEL_TYPE_RADIO = 2;
	
	public $root_id = 0;
	public $output = '';
	public $ctrl = null;
	public $access = null;
	
	private $checked_items = array();
	private $post_var = '';
	private $form_items = array();
	private $type = 0;
	
	protected $clickable = array();
	protected $custom_link_target;
	
	/**
	* Constructor
	* @access	public
	* @param	string	$a_target scriptname
	* @param	string	$a_session_variable session_variable
	* @param	ilWorkspaceTree	$a_tree workspace tree
	* @param	ilWorkspaceAccessHandler	$a_access_handler workspace access handler
	*/
	public function __construct($a_type, $a_target, $a_session_variable, ilWorkspaceTree $a_tree, ilWorkspaceAccessHandler $a_access_handler)
	{
		global $ilCtrl;

		$this->ctrl = $ilCtrl;
		$this->type = $a_type;
		$this->access = $a_access_handler;

		parent::__construct($a_target);
		
		// #11173
		if(!$a_tree->readRootId())
		{
			// create (workspace) root folder
			$root = ilObjectFactory::getClassByType("wsrt");
			$root = new $root(null);
			$root->create();

			$root_id = $a_tree->createReference($root->getId());
			$a_tree->addTree($a_tree->getTreeId(), $root_id);
			$a_tree->setRootId($root_id);
		}
		$this->tree = $a_tree;
		$this->root_id = $this->tree->readRootId();
		$this->order_column = 'title';
		$this->setSessionExpandVariable($a_session_variable);
		
		// reset filter
		$this->filter = array();
		
		$this->addFilter('wsrt');
		$this->addFilter('wfld');
		
		$this->addFormItemForType('wsrt');
		$this->addFormItemForType('wfld');
		
		$this->setFiltered(true);
		$this->setFilterMode(IL_FM_POSITIVE);
	}

	public function showChilds($a_ref_id, $a_obj_id = 0)
	{
		if ($a_ref_id == 0 ||
			$this->access->checkAccess('read', '', $a_ref_id))
		{
			return true;
		}
		return false;
	}
	
	public function isVisible($a_ref_id,$a_type)
	{
		return true;
	}

	public function sortNodes($a_nodes,$a_parent_obj_id)
	{
		return $a_nodes;
	}
	
	public function isClickable($a_type, $a_ref_id, $a_obj_id = 0)
	{
		if(is_array($this->clickable) && in_array($a_type, $this->clickable) &&
			$a_ref_id)
		{
			return true;
		}
		return false;
	}	
	
	public function addFormItemForType($type)
	{
		$this->form_items[$type] = true;
	}
	public function removeFormItemForType($type)
	{
		$this->form_items[$type] = false;
	}
	public function removeAllFormItemTypes()
	{
		$this->form_items = array();
	}
	public function setCheckedItems($a_checked_items = array())
	{
		$this->checked_items = $a_checked_items;
	}	
	public function isItemChecked($a_id)
	{
		return in_array($a_id, $this->checked_items) ? true : false;
	}
	public function setPostVar($a_post_var)
	{
		$this->post_var = $a_post_var;
	}
	public function getPostVar()
	{
		return $this->post_var;
	}
	
	public function buildFormItem($a_node_id, $a_type)
	{
		if(!array_key_exists($a_type, $this->form_items) || !$this->form_items[$a_type])
		{
			return;
		}

		switch($this->type)
		{
			case self::SEL_TYPE_CHECK:
				return ilUtil::formCheckbox((int)$this->isItemChecked($a_node_id), $this->post_var, $a_node_id);
				
			case self::SEL_TYPE_RADIO:
				return ilUtil::formRadioButton((int)$this->isItemChecked($a_node_id), $this->post_var, $a_node_id);
		}	
	}
	
	function formatObject(&$tpl, $a_node_id, $a_option, $a_obj_id = 0)
	{		
		global $lng;
		
		if (!isset($a_node_id) or !is_array($a_option))
		{
			$this->ilias->raiseError(get_class($this)."::formatObject(): Missing parameter or wrong datatype! ".
				"node_id: ".$a_node_id." options:".var_dump($a_option),$this->ilias->error_obj->WARNING);
		}

		$pic = false;
		foreach ($a_option["tab"] as $picture)
		{
			if ($picture == 'plus')
			{
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("EXP_DESC", $lng->txt("expand"));
				$target = $this->createTarget('+',$a_node_id);
				$tpl->setVariable("LINK_NAME", $a_node_id);
				$tpl->setVariable("LINK_TARGET_EXPANDER", $target);
				$tpl->setVariable("IMGPATH", $this->getImage("browser/plus.png"));
				$tpl->parseCurrentBlock();
				$pic = true;
			}

			if ($picture == 'minus' && $this->show_minus)
			{
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("EXP_DESC", $lng->txt("collapse"));
				$target = $this->createTarget('-',$a_node_id);
				$tpl->setVariable("LINK_NAME", $a_node_id);
				$tpl->setVariable("LINK_TARGET_EXPANDER", $target);
				$tpl->setVariable("IMGPATH", $this->getImage("browser/minus.png"));
				$tpl->parseCurrentBlock();
				$pic = true;
			}
		}
		
		if (!$pic)
		{
			$tpl->setCurrentBlock("blank");
			$tpl->setVariable("BLANK_PATH", $this->getImage("browser/blank.png"));
			$tpl->parseCurrentBlock();
		}

		if ($this->output_icons)
		{
			$tpl->setCurrentBlock("icon");
			$tpl->setVariable("ICON_IMAGE" , $this->getImage("icon_".$a_option["type"].".svg", $a_option["type"], $a_obj_id));
			
			$tpl->setVariable("TARGET_ID" , "iconid_".$a_node_id);
			$this->iconList[] = "iconid_".$a_node_id;
			$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
			$tpl->parseCurrentBlock();
		}		
		
		if(strlen($formItem = $this->buildFormItem($a_node_id, $a_option['type'])))
		{
			$tpl->setCurrentBlock('check');
			$tpl->setVariable('OBJ_CHECK', $formItem);
			$tpl->parseCurrentBlock();
		}

		if ($this->isClickable($a_option["type"], $a_node_id,$a_obj_id))	// output link
		{
			$tpl->setCurrentBlock("link");
			//$target = (strpos($this->target, "?") === false) ?
			//	$this->target."?" : $this->target."&";
			//$tpl->setVariable("LINK_TARGET", $target.$this->target_get."=".$a_node_id.$this->params_get);
			$tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($a_node_id, $a_option["type"]));
				
			$style_class = $this->getNodeStyleClass($a_node_id, $a_option["type"]);
			
			if ($style_class != "")
			{
				$tpl->setVariable("A_CLASS", ' class="'.$style_class.'" ' );
			}

			if (($onclick = $this->buildOnClick($a_node_id, $a_option["type"], $a_option["title"])) != "")
			{
				$tpl->setVariable("ONCLICK", "onClick=\"$onclick\"");
			}

			$tpl->setVariable("LINK_NAME", $a_node_id);
			$tpl->setVariable("TITLE", ilUtil::shortenText(
				$this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]),
				$this->textwidth, true));
			$tpl->setVariable("DESC", ilUtil::shortenText(
				$this->buildDescription($a_option["description"], $a_node_id, $a_option["type"]), $this->textwidth, true));
			$frame_target = $this->buildFrameTarget($a_option["type"], $a_node_id, $a_option["obj_id"]);
			if ($frame_target != "")
			{
				$tpl->setVariable("TARGET", " target=\"".$frame_target."\"");
			}
			$tpl->parseCurrentBlock();
		}
		else			// output text only
		{
			$tpl->setCurrentBlock("text");
			$tpl->setVariable("OBJ_TITLE", ilUtil::shortenText(
				$this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]), $this->textwidth, true));
			$tpl->setVariable("OBJ_DESC", ilUtil::shortenText(
				$this->buildDescription($a_option["desc"], $a_node_id, $a_option["type"]), $this->textwidth, true));			
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("list_item");
		$tpl->parseCurrentBlock();
		$tpl->touchBlock("element");
	}
	
	/*
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader(&$tpl, $a_obj_id,$a_option)
	{
		global $lng;
		
		// custom icons
		$path = ilObject::_getIcon($a_obj_id, "small", "wsrt");

		$tpl->setCurrentBlock("icon");
		$title = $this->tree->getNodeData($this->root_id);
		$title = $title["title"];
		if(!$title)
		{
			$title = $lng->txt("personal_workspace");
		}

		$tpl->setVariable("ICON_IMAGE", $path);
		$tpl->setVariable("TXT_ALT_IMG", $title);
		$tpl->parseCurrentBlock();		

		if(strlen($formItem = $this->buildFormItem($a_obj_id, $a_option['type'])))
		{
			$tpl->setCurrentBlock('check');
			$tpl->setVariable('OBJ_CHECK', $formItem);
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable('OBJ_TITLE', $title);
	}
	
	function setTypeClickable($a_type)
	{
		$this->clickable[] = $a_type;
	}
	
	function setCustomLinkTarget($a_target)
	{
		$this->custom_link_target = $a_target;		
	}
	
	function buildLinkTarget($a_node_id, $a_type)
	{
		if(!$this->custom_link_target)
		{
			return parent::buildLinkTarget($a_node_id, $a_type);
		}
		
		$link = $this->custom_link_target."&".$this->target_get."=".$a_node_id;
		return $link;		
	}
}
?>