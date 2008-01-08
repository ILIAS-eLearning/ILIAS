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

/**
* Class ilObjiLincClassroomGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjiLincClassroomGUI:
*
* @extends ilObjectGUI
*/

include_once "./classes/class.ilObjectGUI.php";
include_once "./Modules/ILinc/classes/class.ilObjiLincClassroom.php";

class ilObjiLincClassroomGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	* 2 last parameters actually not used
	*/
	function ilObjiLincClassroomGUI($a_icla_id,$a_icrs_id,$a_call_by_reference = false,$a_prepare_output = false)
	{
		global $ilCtrl,$lng,$ilias,$objDefinition,$tpl,$tree,$ilErr;
		
		$this->type = "icla";
		$this->id = $a_icla_id;
		$this->parent = $a_icrs_id;
		$this->ilias =& $ilias;
		$this->objDefinition =& $objDefinition;
		$this->tpl =& $tpl;
		$this->html = "";
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->tree =& $tree;
		$this->ilErr =& $ilErr;

		//$this->ctrl->saveParameter($this,'parent');
		$this->lng->loadLanguageModule('ilinc');
		
		$this->formaction = array();
		$this->return_location = array();
		$this->target_frame = array();
		//$this->tab_target_script = "adm_object.php";
		$this->actions = "";
		$this->sub_objects = "";

		//prepare output
		if (false)
		{
			$this->prepareOutput();
		}
		
		$this->assignObject();
	}
	
	function assignObject()
	{
		$this->object = new ilObjiLincClassroom($this->id,$this->parent);
	}
	
	function create()
	{
		$this->prepareOutput();

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		// fill in saved values in case of error
		$data = array();
		$data["fields"] = array();
		$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
		$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
		//$data["fields"]["homepage"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["homepage"],true);
		//$data["fields"]["download"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["download"],true);

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.icla_edit.html","Modules/ILinc");
		
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TITLE", $data["fields"]["title"]);
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
		$this->tpl->setVariable("DESC", $data["fields"]["desc"]);

		// get all docents of course
		$docentlist = $this->object->getDocentList();
		
		$docent_options[0] = $this->lng->txt('please_choose');

		foreach ($docentlist as $id => $data)
		{
			$docent_options[$id] = $data['fullname'];
		}
		
		$sel_docents = ilUtil::formSelect("0","Fobject[instructoruserid]",$docent_options,false,true);
		
		$this->tpl->setVariable("TXT_DOCENT", $this->lng->txt(ILINC_MEMBER_DOCENT));
		$this->tpl->setVariable("SEL_DOCENT", $sel_docents);
		
		/*$docent = 0; $student = 0;

		if ($ilinc_status == ILINC_MEMBER_DOCENT)
		{
			$docent = 1;
		}
		elseif ($ilinc_status == ILINC_MEMBER_STUDENT)
		{
			$student = 1;
		}*/
		
		$radio1 = ilUtil::formRadioButton(1,"Fobject[alwaysopen]","1");
		$radio2 = ilUtil::formRadioButton(0,"Fobject[alwaysopen]","0");
		
		$this->tpl->setVariable("TXT_ACCESS", $this->lng->txt("access"));
		$this->tpl->setVariable("SEL_ACCESS", $radio1." ".$this->lng->txt("ilinc_classroom_open").$radio2." ".$this->lng->txt("ilinc_classroom_closed"));

		// display akclassvalues 
		if ($this->ilias->getSetting("ilinc_akclassvalues_active"))
		{
			$icrs_obj_id = ilObject::_lookupObjectId($this->parent);
			include_once('./Modules/ILinc/classes/class.ilObjiLincCourse.php');
			$akclassvalues = ilObjiLincCourse::_getAKClassValues($icrs_obj_id);
			
			$this->tpl->setVariable("TXT_AKCLASSVALUE1", $this->lng->txt("akclassvalue1"));
			$this->tpl->setVariable("TXT_AKCLASSVALUE2", $this->lng->txt("akclassvalue2"));
			
			$this->tpl->setVariable("AKCLASSVALUE1", $akclassvalues[0]);
			$this->tpl->setVariable("AKCLASSVALUE2", $akclassvalues[1]);
		}

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save",$this->ctrl->getFormAction($this)."&new_type=".$new_type));

		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	/**
	* save object
	* @access	public
	*/
	function save()
	{
		// akclassvalues 
		if ($this->ilias->getSetting("ilinc_akclassvalues_active"))
		{
			$icrs_obj_id = ilObject::_lookupObjectId($this->parent);
			include_once('./Modules/ILinc/classes/class.ilObjiLincCourse.php');
			$akclassvalues = ilObjiLincCourse::_getAKClassValues($icrs_obj_id);
			
			$_POST['Fobject']['akclassvalue1'] = $akclassvalues[0];
			$_POST['Fobject']['akclassvalue2'] = $akclassvalues[1];
		}
		
		$ilinc_course_id = ilObjiLincClassroom::_lookupiCourseId($this->parent);

		$this->object->ilincAPI->addClass($ilinc_course_id,$_POST['Fobject']);
		$response = $this->object->ilincAPI->sendRequest('addClass');
		
		if ($response->isError())
		{
			$this->ilErr->raiseError($response->getErrorMsg(),$this->ilErr->MESSAGE);
		}

		// always send a message
		ilUtil::sendInfo($response->getResultMsg(),true);
		
		$this->ctrl->redirectByClass("ilobjilinccoursegui");
	}
	
	function joinClassroom()
	{
		// join class
		$url = $this->object->joinClass($this->ilias->account,$_GET['class_id']);

		if (!$url)
		{
			$this->ilias->raiseError($this->object->getErrorMsg(),$this->ilias->error_obj->FATAL);
		}

		ilUtil::redirect(trim($url));
	}
	
	function editClassroom()
	{
		$fields = array();

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			$fields["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$fields["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
		}
		else
		{
			$fields["title"] = ilUtil::prepareFormOutput($this->object->getTitle());
			$fields["desc"] = ilUtil::stripSlashes($this->object->getDescription());
		}

		$this->displayEditForm($fields);
	}
	
	/**
	* display edit form (usually called by editObject)
	*
	* @access	private
	* @param	array	$fields		key/value pairs of input fields
	*/
	function displayEditForm($fields)
	{
		//$this->getTemplateFile("edit");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.icla_edit.html","Modules/ILinc");

		foreach ($fields as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}

		// get all docents of course
		$docentlist = $this->object->getDocentList();
		
		$docent_options[0] = $this->lng->txt('please_choose');

		foreach ($docentlist as $id => $data)
		{
			$docent_options[$id] = $data['fullname'];
		}
		
		$sel_docents = ilUtil::formSelect($this->object->getDocentId(),"Fobject[instructoruserid]",$docent_options,false,true);
		
		$this->tpl->setVariable("TXT_DOCENT", $this->lng->txt(ILINC_MEMBER_DOCENT));
		$this->tpl->setVariable("SEL_DOCENT", $sel_docents);
		
		
		$open = 0; $closed = 0;

		if ($this->object->getStatus())
		{
			$open = 1;
		}
		else
		{
			$closed = 1;
		}
		
		$radio1 = ilUtil::formRadioButton($open,"Fobject[alwaysopen]","1");
		$radio2 = ilUtil::formRadioButton($closed,"Fobject[alwaysopen]","0");
		
		$this->tpl->setVariable("TXT_ACCESS", $this->lng->txt("access"));
		$this->tpl->setVariable("SEL_ACCESS", $radio1." ".$this->lng->txt("ilinc_classroom_open").$radio2." ".$this->lng->txt("ilinc_classroom_closed"));

		// display akclassvalues 
		if ($this->ilias->getSetting("ilinc_akclassvalues_active"))
		{
			$icrs_obj_id = ilObject::_lookupObjectId($this->parent);
			include_once('./Modules/ILinc/classes/class.ilObjiLincCourse.php');
			$akclassvalues = ilObjiLincCourse::_getAKClassValues($icrs_obj_id);
			
			$this->tpl->setVariable("TXT_AKCLASSVALUE1", $this->lng->txt("akclassvalue1"));
			$this->tpl->setVariable("TXT_AKCLASSVALUE2", $this->lng->txt("akclassvalue2"));
			
			$this->tpl->setVariable("AKCLASSVALUE1", $akclassvalues[0]);
			$this->tpl->setVariable("AKCLASSVALUE2", $akclassvalues[1]);
		}

		$obj_str = "&class_id=".$this->object->id;
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("update",$this->ctrl->getFormAction($this).$obj_str));

		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "updateClassroom");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

	}
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		// tabs are defined manually here. The autogeneration via objects.xml will be deprecated in future
		// for usage examples see ilObjGroupGUI or ilObjSystemFolderGUI
	}
	
	/**
	* display deletion confirmation screen
	* only for referenced objects. For user,role & rolt overwrite this function in the appropriate
	* Object folders classes (ilObjUserFolderGUI,ilObjRoleFolderGUI)
	*
	* @access	public
 	*/
	function removeClassroom($a_error = false)
	{
		unset($this->data);
		$this->data["cols"] = array("type", "title", "last_change");

		$this->data["data"][$_GET['class_id']] = array(
											"type"        => $this->object->getType(),
											"title"       => $this->object->getTitle()."#separator#".$this->object->getDescription()." ",	// workaround for empty desc
											"last_update" => "n/a"
										);

		$this->data["buttons"] = array( "confirmedDeleteClassroom"  => $this->lng->txt("confirm"),
								  "cancelDeleteClassroom"  => $this->lng->txt("cancel"));

		$this->getTemplateFile("confirm");

		if(!$a_error)
		{
			ilUtil::sendInfo($this->lng->txt("info_delete_sure"));
		}

		$obj_str = "&class_id=".$this->object->id;
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("delete",$this->ctrl->getFormAction($this).$obj_str));

		// BEGIN TABLE HEADER
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach ($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach ($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if ($key == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				elseif ($key == "title")
				{
					$name_field = explode("#separator#",$cell_data);

					$this->tpl->setVariable("TEXT_CONTENT", "<b>".$name_field[0]."</b>");
						
					$this->tpl->setCurrentBlock("subtitle");
					$this->tpl->setVariable("DESC", $name_field[1]);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}

				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	* updates class room on ilinc server
	*
	* @access	public
	*/
	function updateClassroom()
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$this->object->setDocentId($_POST["Fobject"]["instructoruserid"]);
		$this->object->setStatus($_POST["Fobject"]["alwaysopen"]);
		
		//var_dump($_POST["Fobject"],$this->object->getStatus());exit;

		if (!$this->object->update())
		{
			$this->ilErr->raiseError($this->object->getErrorMsg(),$this->ilErr->MESSAGE);
		}
		
		//ilUtil::sendInfo($this->lng->txt("msg_icla_updated"),true);
		ilUtil::sendInfo($this->getResultMsg(),true);
		
		$this->ctrl->redirectByClass("ilobjilinccoursegui");
		//ilUtil::redirect($this->getReturnLocation("update",$this->ctrl->getLinkTarget($this)));
	}
	
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$this->$cmd();
				break;
		}

		return true;
	}
	
	/**
	* cancel deletion of classroom object
	*
	* @access	public
	*/
	function cancelDeleteClassroom()
	{
		session_unregister("saved_post");
		
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);

		$this->ctrl->redirectByClass("ilobjilinccoursegui");
	}
	
	/**
	* @access	public
	*/
	function confirmedDeleteClassroom()
	{
		if (!$this->object->delete())
		{
			$msg = $this->object->getErrorMsg();
		}
		else
		{
			$msg = $this->lng->txt('icla_deleted');
		}
		
		// Feedback
		ilUtil::sendInfo($msg,true);
		
		$this->ctrl->redirectByClass("ilobjilinccoursegui");
	}
	
	function getResultMsg()
	{
		return $this->object->result_msg;
	}
	
	/**
	* cancel is called when an operation is canceled, method links back
	* @access	public
	*/
	function cancel()
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);

		$this->ctrl->redirectByClass("ilobjilinccoursegui");
	}
} // END class.ilObjiLincClassroomGUI
?>
