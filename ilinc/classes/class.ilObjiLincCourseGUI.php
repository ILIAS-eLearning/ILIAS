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
* Class ilObjiLincCourseGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
* 
* @extends ilObjectGUI
* 
* @ilCtrl_Calls ilObjiLincCourseGUI: ilObjiLincClassroomGUI
* 
* @package ilias-core
*/

require_once "./classes/class.ilObjectGUI.php";

class ilObjiLincCourseGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjiLincCourseGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "icrs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		
		$this->ctrl->saveParameter($this,'ref_id');
	}
	
	/**
	* create new object form
	*
	* @access	public
	*/
	function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		/*if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else*/
		{
			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
			$data["fields"]["homepage"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["homepage"],true);
			$data["fields"]["download"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["download"],true);

			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.icrs_edit.html","ilinc");
			
			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
			$this->tpl->setVariable("TITLE", $data["fields"]["title"]);
			$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
			$this->tpl->setVariable("DESC", $data["fields"]["desc"]);
			$this->tpl->setVariable("TXT_HOMEPAGE_URL", $this->lng->txt("homepage_url"));
			$this->tpl->setVariable("HOMEPAGE_URL", $data["fields"]["homepage"]);
			$this->tpl->setVariable("TXT_DOWNLOAD_RESOURCES_URL", $this->lng->txt("download_resources_url"));
			$this->tpl->setVariable("DOWNLOAD_RESOURCES_URL", $data["fields"]["download"]);
			$this->tpl->setVariable("TXT_NOT_YET", $this->lng->txt("not_implemented_yet"));

			$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".
																	   $_GET["ref_id"]."&new_type=".$new_type));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
	}
	
	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;
		
		include_once "class.ilnetucateXMLAPI.php";
		$ilinc = new ilnetucateXMLAPI();
		$ilinc->addCourse($_POST["Fobject"]);
		$response = $ilinc->sendRequest();
		
		if ($response->isError())
		{
			$this->ilErr->raiseError($response->getErrorMsg(),$this->ilErr->MESSAGE);
		}

		// create and insert forum in objecttree
		$iCrsObj = parent::saveObject();
		
		$iCrsObj->saveID($response->getFirstID());

		// always send a message
		sendInfo($response->getResultMsg(),true);
		
		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
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
} // END class.ilObjiLincCourseGUI
?>
