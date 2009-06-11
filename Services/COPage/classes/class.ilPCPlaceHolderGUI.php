<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once("./Services/COPage/classes/class.ilPCPlaceHolder.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCPlaceHolderGUI
*
* User Interface for Place Holder Management
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id: class.ilPCListGUI.php 17506 2008-09-24 13:48:46Z akill $
*
* @ilCtrl_Calls ilPCPlaceHolderGUI: ilPCMediaObjectGUI
*
* @ingroup ServicesCOPage
*/


class ilPCPlaceHolderGUI extends ilPageContentGUI
{
	/**
	* Constructor
	* @access	public
	*/
	var $pg_obj;
	var $content_obj;
	var $hier_id;
	var $pc_id;
	var $styleid;
	
	function ilPCPlaceHolderGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->pg_obj = $a_pg_obj;
		$this->content_obj = $a_content_obj;
		$this->hier_id = $a_hier_id;
		$this->pc_id = $a_pc_id;
		
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
	}
	
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);
		// get current command
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case 'ilpcmediaobjectgui':  //special handling
				include_once("./Services/COPage/classes/class.ilPCMediaObjectGUI.php");
				$media_gui = new ilPCMediaObjectGUI($this->pg_obj,$this->content_obj,$this->hier_id,$this->pc_id);
				$ret = $ilCtrl->forwardCommand($media_gui);		
				break;
				
			default:
				$ret =& $this->$cmd();
				break;
		}
		
		
		return $ret;
	}
	
	
	/**
	* Handle Insert
	*/
	
	function insert() {
		$this->propertyGUI("create","Text","100px","insert");
	}
	
	
	/**
	* create new table in dom and update page in db
	*/
	function create()
	{
		if ($_POST["plach_height"]=="" || !preg_match("/[0-9]+/",$_POST["plach_height"])) {
			return $this->insert();
			exit;
		}
		
		$this->content_obj = new ilPCPlaceHolder($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
		$this->content_obj->setHeight($_POST["plach_height"]."px");
		$this->content_obj->setContentClass($_POST['plach_type']);
		$this->updated = $this->pg_obj->update();		
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{	
			$this->insert();
		}
	}
	
	/**
	* Handle Editing
	*/
	
	function edit() {
		
		if ($this->pg_obj->getLayoutMode() == true) {
			$this->edit_object();
		} else {
			$this->forward_edit();
		}
	}
	
	/**
	* Set Style Id.
	*
	* @param	int	$a_styleid	Style Id
	*/
	function setStyleId($a_styleid)
	{
		$this->styleid = $a_styleid;
	}

	/**
	* Get Style Id.
	*
	* @return	int	Style Id
	*/
	function getStyleId()
	{
		return $this->styleid;
	}
	

	/**
	* Handle Editing Private Methods
	*/
	
	private function edit_object() {
		$this->propertyGUI("saveProperties",$this->content_obj->getContentClass(),$this->content_obj->getHeight(),"save");
	}
	
	
	private function forward_edit() {
		global $ilCtrl;
		
		switch ($this->content_obj->getContentClass()) {
			case 'Media':
				include_once("./Services/COPage/classes/class.ilPCMediaObjectGUI.php");
				$ilCtrl->setCmdClass("ilpcmediaobjectgui");
				$ilCtrl->setCmd("insert");
				$media_gui = new ilPCMediaObjectGUI($this->pg_obj,$null);
				$ret = $ilCtrl->forwardCommand($media_gui);
    
				break;
			case 'Text':
				$this->textCOSelectionGUI();
				break;
			case 'Question':
				include_once("./Services/COPage/classes/class.ilPCQuestionGUI.php");
				$ilCtrl->setCmdClass("ilpcquestiongui");
				$ilCtrl->setCmd("insert");
				$question_gui = new ilPCQuestionGUI($this->pg_obj,$this->content_obj,$this->hier_id,$this->pc_id);
				$question_gui -> setSelfAssessmentMode(true);
				$ret = $ilCtrl->forwardCommand($question_gui);
				break;	
			default:
				break;
		}
	}
	
	
	/**
	* save placeholder properties in db and return to page edit screen
	*/
	function saveProperties()
	{
		
		if ($_POST["plach_height"]=="" || !preg_match("/[0-9]+/",$_POST["plach_height"])) {
			return $this->edit_object();
			exit;
		}
			
		$this->content_obj->setContentClass($_POST['plach_type']);
		$this->content_obj->setHeight($_POST["plach_height"]."px");
		
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->pg_obj->addHierIDs();
			$this->edit();
		}
	}
	
	/**
	* Object Property GUI
	*/
	private function propertyGUI($a_action,$a_type,$a_height,$a_mode) {
		global $ilCtrl, $lng;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));
		$this->form_gui->setTitle($lng->txt("cont_ed_plachprop"));

		include_once("Services/Form/classes/class.ilRadioMatrixInputGUI.php");
		$ttype_input = new ilRadioMatrixInputGUI($lng->txt("type"), "plach_type");
		$options =array("Text"=>$lng->txt("cont_ed_plachtext"),"Media"=>$lng->txt("cont_ed_plachmedia"),
						"Question"=>$lng->txt("cont_ed_plachquestion"));
		$ttype_input->setOptions($options);
		$ttype_input->setValue($a_type);
		$ttype_input->setRequired(true);
		$theight_input = new ilTextInputGUI($lng->txt("height"),"plach_height");
		$theight_input->setSize(4);
		$theight_input->setMaxLength(3);
		
		$a_height = preg_replace("/px/","",$a_height);
		$theight_input->setValue($a_height);
		$theight_input->setTitle($lng->txt("height")." (px)");
		$theight_input->setRequired(true);
				
		$this->form_gui->addItem($ttype_input);
		$this->form_gui->addItem($theight_input);
		
		$this->form_gui->addCommandButton($a_action, $lng->txt($a_mode));
		$this->form_gui->addCommandButton("cancelCreate", $lng->txt("cancel"));
		$this->tpl->setContent($this->form_gui->getHTML());
	}
	
	/**
	* Text Item Selection
	*/
	
	private function textCOSelectionGUI() {
		
		global $ilCtrl, $lng;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));
		$this->form_gui->setTitle($lng->txt("cont_ed_select_pctext"));

		// Select Question Type
		
		include_once("Services/Form/classes/class.ilRadioMatrixInputGUI.php");
		$ttype_input = new ilRadioMatrixInputGUI($lng->txt("cont_ed_textitem"), "pctext_type");
		$options = array($lng->txt("cont_ed_par"),$lng->txt("cont_ed_dtable"),
						 $lng->txt("cont_ed_atable"),$lng->txt("cont_ed_list"),$lng->txt("cont_ed_flist"));
		$ttype_input->setOptions($options);
		$this->form_gui->addItem($ttype_input);
		
		$this->form_gui->addCommandButton("insertPCText", $lng->txt("insert"));
		$this->form_gui->addCommandButton("cancelCreate", $lng->txt("cancel"));
		$this->tpl->setContent($this->form_gui->getHTML());
	}
	
	/**
	* Forwards Text Item Selection to GUI classes
	*/
	
	function insertPCText() {
		
		global $ilCtrl;
		switch ($_POST['pctext_type']) {
			
			case '0':  //Paragraph / Text
				include_once("./Services/COPage/classes/class.ilPCParagraphGUI.php");
				$ilCtrl->setCmdClass("ilpcparagraphgui");
				$ilCtrl->setCmd("insert");
				$paragraph_gui = new ilPCParagraphGUI($this->pg_obj,$this->content_obj,$this->hier_id,$this->pc_id);
				$paragraph_gui->setStyleId($this->getStyleId());
				$ret = $ilCtrl->forwardCommand($paragraph_gui);
				break;
				
			case '1':  //DataTable
				include_once("./Services/COPage/classes/class.ilPCDataTableGUI.php");
				$ilCtrl->setCmdClass("ilpcdatatablegui");
				$ilCtrl->setCmd("insert");
				$dtable_gui = new ilPCDataTableGUI($this->pg_obj,$this->content_obj,$this->hier_id,$this->pc_id);
				$ret = $ilCtrl->forwardCommand($dtable_gui);
				break;
				
			case '2':  //Advanced Table
				include_once("./Services/COPage/classes/class.ilPCTableGUI.php");
				$ilCtrl->setCmdClass("ilpctablegui");
				$ilCtrl->setCmd("insert");
				$atable_gui = new ilPCTableGUI($this->pg_obj,$this->content_obj,$this->hier_id,$this->pc_id);
				$ret = $ilCtrl->forwardCommand($atable_gui);
				break;
				
			case '3':  //Advanced List
				include_once("./Services/COPage/classes/class.ilPCListGUI.php");
				$ilCtrl->setCmdClass("ilpclistgui");
				$ilCtrl->setCmd("insert");
				$list_gui = new ilPCListGUI($this->pg_obj,$this->content_obj,$this->hier_id,$this->pc_id);
				$ret = $ilCtrl->forwardCommand($list_gui);
				break;
				
			case '4':  //File List
				include_once ("./Services/COPage/classes/class.ilPCFileListGUI.php");
				$ilCtrl->setCmdClass("ilpcfilelistgui");
				$ilCtrl->setCmd("insert");
				$file_list_gui = new ilPCFileListGUI($this->pg_obj,$this->content_obj,$this->hier_id,$this->pc_id);
				$file_list_gui->setStyleId($this->getStyleId());
				$ret = $this->ctrl->forwardCommand($file_list_gui);
				break;
				
			default:
				break;
		}
	}
	
	
}