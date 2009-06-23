<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Page.php");

/**
* Class ilPageLayoutGUI GUI class
* 
* @author Hendrik Holtmann <holtmann@me.com> 
* @version $Id$
*
* @ilCtrl_Calls ilPageLayoutGUI: ilPageEditorGUI, ilEditClipboardGUI
* @ilCtrl_Calls ilPageLayoutGUI: ilPublicUserProfileGUI, ilPageObjectGUI
*
*/
class ilPageLayoutGUI extends ilPageObjectGUI
{
	/**
	* Constructor
	*/
	
	var $layout_object = null;
	
	
	function __construct($a_parent_type, $a_id = 0, $a_old_nr = 0, $a_slm_id = 0)
	{
		global $tpl,$ilCtrl;
	
		parent::__construct($a_parent_type, $a_id, $a_old_nr);
		
		// content style
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$tpl->setVariable("LOCATION_ADDITIONAL_STYLESHEET",
			ilObjStyleSheet::getPlaceHolderStylePath());	
		$tpl->parseCurrentBlock();
		
		$this->setEnabledMaps(false);
		$this->setPreventHTMLUnmasking(false);
		$this->setEnabledInternalLinks(false);
		$this->setEnabledSelfAssessment(false);
		
		//set For GUI and associated object
		$this->setLayoutMode(true);
		$this->obj->setLayoutMode(true);
		
		//associated object
		include_once("./Services/Style/classes/class.ilPageLayout.php");
				
		$this->layout_object = new ilPageLayout($a_id);
		$this->layout_object->readObject();
		$this->slm_id = $a_slm_id;
		
	}
	
	
	

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case 'ilmdeditorgui':
				return parent::executeCommand();
				break;

			case "ilpageobjectgui":
				$page_gui = new ilPageObjectGUI("sahs",
					$this->getPageObject()->getId(), $this->getPageObject()->old_nr);
				$html = $ilCtrl->forwardCommand($page_gui);
				return $html;
				
			default:
				$html = parent::executeCommand();
				return $html;
		}
	}
	
	function create(){
		$this->properties("insert");
	}
	
	function properties($a_mode="save")
	{
		global $ilCtrl, $lng, $ilTabs;
	
		$ilTabs->setTabActive('properties');
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));
		$this->form_gui->setTitle($lng->txt("cont_ed_pglprop"));

		include_once("Services/Form/classes/class.ilRadioMatrixInputGUI.php");
	
		
		$title_input = new ilTextInputGUI($lng->txt("title"),"pgl_title");
		$title_input->setSize(50);
		$title_input->setMaxLength(128);
		$title_input->setValue($this->layout_object->title);
		$title_input->setTitle($lng->txt("title"));
		$title_input->setRequired(true);

		$desc_input = new ilTextAreaInputGUI($lng->txt("description"),"pgl_desc");
		$desc_input->setValue($this->layout_object->description);
		$desc_input->setRows(3);
		$desc_input->setCols(37);
				
		$desc_input->setTitle($lng->txt("description"));
		$desc_input->setRequired(false);
				
		$this->form_gui->addItem($title_input);
		$this->form_gui->addItem($desc_input);
		
		$this->form_gui->addCommandButton("updateProperties", $lng->txt($a_mode));
		$this->tpl->setContent($this->form_gui->getHTML());
	}
	
	function updateProperties()
	{
		global $lng;
		if($_POST["pgl_title"] == "")
		{
			$this->ilias->raiseError($this->lng->txt("no_title"),$this->ilias->error_obj->MESSAGE);
			$this->properties();
			exit;
		}
		$this->layout_object->setTitle($_POST['pgl_title']);
		$this->layout_object->setDescription($_POST['pgl_desc']);
		$this->layout_object->update();
		ilUtil::sendInfo($lng->txt("saved_successfully"),false);
		$this->properties();
	}
	
	/**
	* output tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $tpl, $lng;
		$ilCtrl->setParameterByClass("ilpagelayoutgui", "obj_id", $this->obj->id);
		$ilTabs->addTarget("properties",
			$ilCtrl->getLinkTarget($this, "properties"), array("properties","", ""), "", "");
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_pg_b.gif"));
		$tpl->setTitle($this->layout_object->getTitle());
		$tpl->setDescription("");
		//	$tpl->setTitle(
		//		$lng->txt("sahs_page").": ".$this->node_object->getTitle());
	}


}
?>
