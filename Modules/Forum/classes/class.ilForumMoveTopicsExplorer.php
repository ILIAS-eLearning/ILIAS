<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Repository/classes/class.ilRepositoryExplorer.php';

/*
* ilForumMoveTopicsExplorer
*
* @author Michael Jansen <mjansen@databay.de>
*
*/
class ilForumMoveTopicsExplorer extends ilRepositoryExplorer
{
	public $root_id = 0;
	public $output = '';
	public $ctrl = null;
	
	private $checked_item = null;
	private $post_var = '';
	private $form_items = array();
	private $excluded_obj_id = 0;
	
	/**
	* Constructor
	* @access	public
	* @param	string	$a_target scriptname
	* @param	string	$a_session_variable session_variable
	*/
	public function __construct($a_target, $a_session_variable)
	{
		global $tree, $ilCtrl;

		$this->ctrl = $ilCtrl;

		parent::__construct($a_target);
		$this->tree = $tree;
		$this->root_id = $this->tree->readRootId();
		$this->order_column = 'title';
		$this->setSessionExpandVariable($a_session_variable);
		
		// reset filter
		$this->filter = array();
		
		$this->addFilter('root');
		$this->addFilter('crs');				
		$this->addFilter('grp');
		$this->addFilter('cat');		
		$this->addFilter('fold');
		$this->addFilter('frm');
		
		$this->addFormItemForType('frm');
		
		$this->setFiltered(true);
		$this->setFilterMode(IL_FM_POSITIVE);
	}
	
	public function isClickable($a_type, $a_ref_id, $a_obj_id = 0)
	{
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
	public function setCheckedItem($a_checked_item)
	{
		$this->checked_item = $a_checked_item;
	}	
	public function isItemChecked($a_id)
	{
		return $this->checked_item == $a_id ? true : false;
	}
	public function setPostVar($a_post_var)
	{
		$this->post_var = $a_post_var;
	}
	public function getPostVar()
	{
		return $this->post_var;
	}
	
	public function excludeObjIdFromSelection($a_obj_id)
	{
		$this->excluded_obj_id = $a_obj_id;
		
		return $this;
	}
	
	public function getExcludeObjId()
	{
		return $this->excluded_obj_id;
	}
	
	public function buildFormItem($a_node_id, $a_type)
	{
		global $ilObjDataCache, $ilAccess;
		
		if(!array_key_exists($a_type, $this->form_items) || !$this->form_items[$a_type]) return '';
		
		if($ilObjDataCache->lookupObjId($a_node_id) == $this->getExcludeObjId()) return  '';
		
		if(!$ilAccess->checkAccess('moderate_frm', '', $a_node_id)) return '';

		return ilUtil::formRadioButton((int)$this->isItemChecked($a_node_id), $this->post_var, $a_node_id);
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
		global $lng, $ilias, $tree;

		// custom icons
		$path = ilObject::_getIcon($a_obj_id, "small", "root");
		

		$tpl->setCurrentBlock("icon");
		$nd = $tree->getNodeData(ROOT_FOLDER_ID);
		$title = $nd["title"];
		if ($title == "ILIAS")
		{
			$title = $lng->txt("repository");
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
}
?>