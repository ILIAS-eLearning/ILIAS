<?php

class ilObjStudyProgrammeReferenceListGUI extends ilObjStudyProgrammeListGUI
{
	/** @var null|int */
	protected $reference_obj_id = null;
	/** @var null|int */
	protected $reference_ref_id = null;
	/** @var bool */
	protected $deleted = false;

	protected $ilAccess;
	protected $tree;
	protected $lng;

	public function __construct()
	{
		global $DIC;

		$this->ilAccess = $DIC['ilAccess'];
		$this->tree = $DIC['tree'];
		$this->lng = $DIC['lng'];
		$this->ilCtrl = $DIC['ilCtrl'];
		parent::__construct();
	}


	/**
	 * @return string
	 */
	public function getIconImageType() 
	{
		return 'prgr';
	}

	/**
	 * @inheritdoc
	 */
	public function getTypeIcon()
	{
		$reference_obj_id = ilObject::_lookupObjId($this->getCommandId());
		return ilObject::_getIcon(
			$reference_obj_id,
			'small'
		);
	}


	/**
	 * get command id
	 *
	 * @access public
	 * @return int|null
	 */
	public function getCommandId()
	{
		return $this->reference_ref_id;
	}
	
	/**
	 * no activation for links
	 * @return type
	 */
	public function insertTimingsCommand()
	{
		return;
	}
	
	
	
	/**
	* initialisation
	*/
	function init()
	{
		$this->copy_enabled = true;
		$this->static_link_enabled = false;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->info_screen_enabled = true;
		$this->type = "prg";
		$this->gui_class_name = "ilobjstudyprogrammegui";
		
		$this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
		if($this->substitutions->isActive())
		{
			$this->substitutions_enabled = true;
		}
	}
	
	
	
	/**
	* inititialize new item
	* Group reference inits the group item
	*
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	string		$a_title		title
	* @param	string		$a_description	description
	*/
	function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
	{

		
		$this->reference_ref_id = $a_ref_id;
		$this->reference_obj_id = $a_obj_id;

		$target_obj_id = ilContainerReference::_lookupTargetId($a_obj_id);
		
		$target_ref_ids = ilObject::_getAllReferences($target_obj_id);
		$target_ref_id = current($target_ref_ids);
		$target_title = ilContainerReference::_lookupTitle($a_obj_id);
		$target_description = ilObject::_lookupDescription($target_obj_id);

		$this->deleted = $this->tree->isDeleted($target_ref_id);
		
		$this->conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($target_ref_id,$target_obj_id);

		parent::initItem($target_ref_id, $target_obj_id,$target_title,$target_description);

		$this->commands = ilObjStudyProgrammeReferenceAccess::_getCommands($this->reference_ref_id);
		
		if($this->ilAccess->checkAccess('write','',$this->reference_ref_id) or $this->deleted)
		{
			$this->info_screen_enabled = false;
		}
		else
		{
			$this->info_screen_enabled = true;
		}
	}
	
	function getProperties()
	{

		$props = parent::getProperties();

		// offline
		if($this->deleted)
		{
			$props[] = array("alert" => true, "property" => $this->lng->txt("status"),
				"value" => $lng->txt("reference_deleted"));
		}

		return $props ? $props : array();
	}	
	
	/**
	 * 
	 * @param
	 * @return
	 */
	public function checkCommandAccess($a_permission,$a_cmd,$a_ref_id,$a_type,$a_obj_id="")
	{
		// Check edit reference against reference edit permission
		$target_obj_id = ilContainerReference::_lookupTargetId($a_obj_id);
		$target_ref_id = current(ilObject::_getAllReferences($target_obj_id));
		switch($a_cmd)
		{
			case 'editReference':
				return parent::checkCommandAccess($a_permission, $a_cmd, $this->getCommandId(), $a_type, $a_obj_id);
		}

		switch($a_permission)
		{
			case 'copy':
			case 'delete':
				// check against target ref_id
				return parent::checkCommandAccess($a_permission, $a_cmd, $target_ref_id, 'prg', $target_obj_id);

			default:

				// check against reference
				return parent::checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id);
		}
	}
	
	/**
	 * get command link
	 *
	 * @access public
	 * @param string $a_cmd
	 * @return string
	 */
	public function getCommandLink($a_cmd)
	{
		switch($a_cmd)
		{
			case 'editReference':
				$this->ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->getCommandId());
				$cmd_link = $this->ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
				$this->ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				return $cmd_link;

			default:
				$this->ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
				$cmd_link = $this->ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
				$this->ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				return $cmd_link;
		}
	}

	public function getListItemHTML(
		$a_ref_id,
		$a_obj_id,
		$a_title,
		$a_description,
        $a_use_asynch = false,
        $a_get_asynch_commands = false,
        $a_asynch_url = "",
        $a_context = self::CONTEXT_REPOSITORY
    )
	{
		$target_obj_id = ilContainerReference::_lookupTargetId($a_obj_id);
		$target_ref_id = current(ilObject::_getAllReferences($target_obj_id));
		$prg = new ilObjStudyProgramme($target_ref_id);
		$assignments = $prg->getAssignments();
		if($this->getCheckboxStatus() && count($assignments) > 0) {
			$this->setAdditionalInformation($this->lng->txt("prg_can_not_manage_in_repo"));
			$this->enableCheckbox(false);
		} else {
			$this->setAdditionalInformation(null);
		}
		return ilObjectListGUI::getListItemHTML(
			$a_ref_id,
			$a_obj_id,
			$a_title,
			$a_description,
			$a_use_asynch,
			$a_get_asynch_commands,
			$a_asynch_url,
			$a_context
		);
	}
}
