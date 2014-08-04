<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilCommonActionDispatcherGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilInfoScreenGUI.php 30682 2011-09-16 19:33:22Z akill $
*
* @ilCtrl_Calls ilCommonActionDispatcherGUI: ilNoteGUI, ilTaggingGUI, ilObjectActivationGUI
* @ilCtrl_Calls ilCommonActionDispatcherGUI: ilRatingGUI
*
* @ingroup ServicesObject
*/
class ilCommonActionDispatcherGUI
{	
	protected $obj_type; // [string]
	protected $node_id; // [int]
	protected $node_type; // [string]
	protected $obj_id; // [int]
	protected $sub_type; // [string]
	protected $sub_id; // [int]	
	protected $enable_comments_settings; // [bool]
	protected $rating_callback; // [array]
	
	const TYPE_REPOSITORY = 1;
	const TYPE_WORKSPACE = 2;
	
	/**
	 * Constructor
	 * 
	 * @param int $a_node_type
	 * @param object $a_access_handler
	 * @param string $a_obj_type
	 * @param int $a_node_id	
	 * @param int $a_obj_id 
	 * @return object
	 */
	function __construct($a_node_type, $a_access_handler, $a_obj_type, $a_node_id, $a_obj_id)
	{								
		$this->node_type = (int)$a_node_type;
		$this->access_handler = $a_access_handler;
		$this->obj_type = (string)$a_obj_type;
		$this->node_id = (int)$a_node_id;
		$this->obj_id = (int)$a_obj_id;		
	}
	
	/**
	 * Build ajax hash for current (object/node) properties
	 * 
	 * @return string
	 */
	function getAjaxHash()
	{
		return self::buildAjaxHash($this->node_type, $this->node_id, $this->obj_type,
			$this->obj_id, $this->sub_type, $this->sub_id);
	}
	
	/**
	 * Build ajax hash 
	 * 
	 * @param int $a_node_type
	 * @param int $a_node_id	
	 * @param string $a_obj_type
	 * @param int $a_obj_id
	 * @param type $a_sub_type
	 * @param type $a_sub_id
	 * @return string 
	 */
	static function buildAjaxHash($a_node_type, $a_node_id, $a_obj_type, $a_obj_id, $a_sub_type = null, $a_sub_id = null)
	{
		return $a_node_type.";".$a_node_id.";".$a_obj_type.";".
			$a_obj_id.";".$a_sub_type.";".$a_sub_id;
	}
	
	/**
	 * (Re-)Build instance from ajax call
	 * 
	 * @return object
	 */
	static function getInstanceFromAjaxCall()
	{
		global $ilAccess, $ilUser;
		
		if(isset($_GET["cadh"]))
		{
			$parts = explode(";", (string)$_GET["cadh"]);
			
			$node_type = $parts[0];
			$node_id = $parts[1];
			$obj_type = $parts[2];
			$obj_id = $parts[3];
			$sub_type = $parts[4];
			$sub_id = $parts[5];
			
			switch($node_type)
			{
				case self::TYPE_REPOSITORY:
					$access_handler = $ilAccess;
					break;
				
				case self::TYPE_WORKSPACE:
					include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
					$tree = new ilWorkspaceTree($ilUser->getId());
					include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
					$access_handler = new ilWorkspaceAccessHandler($tree);
					break;
				
				default:
					return null;
			}
			
			$dispatcher = new self($node_type, $access_handler, $obj_type, $node_id, $obj_id);
			
			if($sub_type && $sub_id)
			{
				$dispatcher->setSubObject($sub_type, $sub_id);
			}

            // poll comments have specific settings

			if($node_type == self::TYPE_REPOSITORY && $obj_type != "poll")
			{								
				$dispatcher->enableCommentsSettings(true);	
			}
			
			return $dispatcher;
		}		
	}
		
	function executeCommand()
	{
		global $ilCtrl, $ilSetting;

		// check access for object 
		if ($this->node_id && 
			!$this->access_handler->checkAccess("visible", "", $this->node_id) &&
			!$this->access_handler->checkAccess("read", "", $this->node_id))
		{
			exit();
		}
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
		$ilCtrl->saveParameter($this, "cadh");
		
		switch($next_class)
		{
			case "ilnotegui":
				
				$obj_type = $this->obj_type;
				if($this->sub_type)
				{
					$obj_type = $this->sub_type;
				}
				
				include_once "Services/Notes/classes/class.ilNoteGUI.php";
				$note_gui = new ilNoteGUI($this->obj_id, $this->sub_id, $obj_type);
				$note_gui->enablePrivateNotes(true);	
				
				$has_write = $this->access_handler->checkAccess("write", "", $this->node_id);
				if($has_write && $ilSetting->get("comments_del_tutor", 1))
				{
					$note_gui->enablePublicNotesDeletion(true);
				}
				
				// comments cannot be turned off globally
				if($this->enable_comments_settings)
				{
					// should only be shown if active or permission to toggle
					if($has_write ||
						$this->access_handler->checkAccess("edit_permissions", "", $this->node_id))
					{
						$note_gui->enableCommentsSettings();
					}
				}
				/* this is different to the info screen but we need this
				   for sub-object action menus, e.g. wiki page */
				else if($this->sub_id)
				{
					$note_gui->enablePublicNotes(true);
				}						 			 			 

				$ilCtrl->forwardCommand($note_gui);		
				break;

			case "iltagginggui":							
				include_once "Services/Tagging/classes/class.ilTaggingGUI.php";
				$tags_gui = new ilTaggingGUI($this->node_id);										
				$tags_gui->setObject($this->obj_id, $this->obj_type);
				$ilCtrl->forwardCommand($tags_gui);						
				break;
			
			case "ilobjectactivationgui":
				$ilCtrl->setParameter($this, "parent_id", (int)$_REQUEST['parent_id']);
				include_once 'Services/Object/classes/class.ilObjectActivationGUI.php';				
				$act_gui = new ilObjectActivationGUI((int)$_REQUEST['parent_id'],$this->node_id);
				$ilCtrl->forwardCommand($act_gui);
				break;	
			
			case "ilratinggui":
				include_once("./Services/Rating/classes/class.ilRatingGUI.php");
				$rating_gui = new ilRatingGUI();		
				if(!$_GET["rnsb"])
				{
					$rating_gui->setObject($this->obj_id, $this->obj_type, $this->sub_id, $this->sub_type);
				}
				else
				{
					// coming from headaction ignore sub-objects
					$rating_gui->setObject($this->obj_id, $this->obj_type);
				}
				$ilCtrl->forwardCommand($rating_gui);
				if($this->rating_callback)
				{
					// as rating in categories is form-based we need to redirect
					// somewhere after saving
					$ilCtrl->redirect($this->rating_callback[0], $this->rating_callback[1]);
				}
				break;
			
			default:				
				break;
		}
		
		exit();
	}
	
	/**
	 * Set sub object attributes
	 * 
	 * @param string $a_sub_obj_type
	 * @param int $a_sub_obj_id 
	 */
	function setSubObject($a_sub_obj_type, $a_sub_obj_id)
	{
		$this->sub_type = (string)$a_sub_obj_type;
		$this->sub_id = (int)$a_sub_obj_id;			
	}
	
	/**
	 * Toggle comments settings
	 * 
	 * @param bool $a_value
	 */
	function enableCommentsSettings($a_value)
	{
		$this->enable_comments_settings = (bool)$a_value;
	}
	
	/**
	 * Add callback for rating gui
	 * 
	 * @param object $a_gui
	 * @param string $a_cmd
	 */
	function setRatingCallback($a_gui, $a_cmd)
	{
		$this->rating_callback = array($a_gui, $a_cmd);
	}
	
	/**
	 * Set header action menu
	 */
	function initHeaderAction()
	{
		// check access for object 
		if ($this->node_id && 
			!$this->access_handler->checkAccess("visible", "", $this->node_id) &&
			!$this->access_handler->checkAccess("read", "", $this->node_id))
		{
			return;
		}
		
		include_once 'Services/Object/classes/class.ilObjectListGUIFactory.php';
		$this->header_action = ilObjectListGUIFactory::_getListGUIByType($this->obj_type);
		
		// remove all currently unwanted actions
		$this->header_action->enableCopy(false);
		$this->header_action->enableCut(false);
		$this->header_action->enableDelete(false);
		$this->header_action->enableLink(false);
		$this->header_action->enableInfoscreen(false);				
		$this->header_action->enablePayment(false);
		$this->header_action->enableTimings(false);
		
		switch($this->node_type)
		{
			case self::TYPE_REPOSITORY:
				$this->header_action->enableSubscribe(true);
				$context = ilObjectListGUI::CONTEXT_REPOSITORY;				
				break;
			
			case self::TYPE_WORKSPACE:
				$this->header_action->enableSubscribe(false);
				$context = ilObjectListGUI::CONTEXT_WORKSPACE;
				break;
		}
		
		$this->header_action->initItem($this->node_id, $this->obj_id, "", "", 
			$context);		
		$this->header_action->setHeaderSubObject($this->sub_type, $this->sub_id);		
		$this->header_action->setAjaxHash($this->getAjaxHash());
		
		return $this->header_action;
	}
}

?>