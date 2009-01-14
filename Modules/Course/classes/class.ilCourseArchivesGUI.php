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
* Class ilCourseArchivesGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObjectGUI
*/

class ilCourseArchivesGUI
{
	var $container_obj;
	var $course_obj;

	var $tpl;
	var $ctrl;
	var $lng;
	var $tabs_gui;

	/**
	* Constructor
	* @access public
	*/
	function ilCourseArchivesGUI(&$content_gui)
	{
		global $tpl,$ilCtrl,$lng,$ilObjDataCache,$ilTabs;

		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->tabs_gui =& $ilTabs;

		$this->content_gui =& $content_gui;
		$this->content_obj =& $this->content_gui->object;

		$this->__initCourseObject();
	}

		

	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		if (!$cmd = $this->ctrl->getCmd())
		{
			$cmd = "view";
		}
		$this->$cmd();
	}

	function view()
	{
		global $ilAccess,$ilErr;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$ilAccess->checkAccess("read",'',$this->course_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$ilErr->MESSAGE);
		}


		$this->is_tutor = $ilAccess->checkAccess('write','',$this->course_obj->getRefId());
		$this->download_allowed = ($this->is_tutor or $this->course_obj->getArchiveType() == $this->course_obj->ARCHIVE_DOWNLOAD);
		
		$this->course_obj->initCourseArchiveObject();
		$this->course_obj->archives_obj->initCourseFilesObject();
		

		$archives = $this->is_tutor ? 
			$this->course_obj->archives_obj->getArchives() :
			$this->course_obj->archives_obj->getPublicArchives();

		require_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		$this->__showArchivesMenu();
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		$this->tpl->addBlockfile("TBL_CONTENT","tbl_content","tpl.crs_archives_row.html",'Modules/Course');
		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		
		$tbl->setTitle($this->lng->txt("crs_header_archives"));


		if($this->download_allowed or $this->is_tutor)
		{
			$header_names = array('',
								  $this->lng->txt("type"),
								  $this->lng->txt("crs_file_name"),
								  $this->lng->txt("crs_size"),
								  $this->lng->txt("crs_create_date"),
								  $this->lng->txt("crs_archive_lang"));

			$header_vars = array("",
								 "type",
								 "name",
								 "size",
								 "date",
								 "lang");
			$column_width = array("1%","9%","30%","20%","20%","20%");
			$this->tpl->setVariable("COLUMN_COUNTS",6);
		}
		else
		{
			$header_names = array($this->lng->txt("type"),
								  $this->lng->txt("crs_file_name"),
								  $this->lng->txt("crs_create_date"),
								  $this->lng->txt("crs_size"),
								  $this->lng->txt("crs_archive_lang"));
			
			$header_vars = array("type",
								 "name",
								 "date",
								 "size",
								 "lang");
			$column_width = array("10%","30%","20%","20%","20%");
			$this->tpl->setVariable("COLUMN_COUNTS",5);
		}
		
		$tbl->setHeaderNames($header_names);
		$tbl->setHeaderVars($header_vars,
							array("ref_id" => $this->course_obj->getRefId(),
								  "cmd" => "view",
								  "cmdClass" => strtolower(get_class($this))));
		$tbl->setColumnWidth($column_width);


		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->disable("sort");

		if($this->download_allowed)
		{
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
			if($this->is_tutor)
			{
				// delete
				$this->tpl->setCurrentBlock("tbl_action_btn");
				$this->tpl->setVariable("BTN_NAME", "confirmDeleteArchives");
				$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
				$this->tpl->parseCurrentBlock();
			}
			
			// download
			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("BTN_NAME", "downloadArchives");
			$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("download"));
			$this->tpl->parseCurrentBlock();
		}

		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setMaxCount(count($archives));
		$archives = array_slice($archives,$_GET['offset'],$_GET['limit']);
		$tbl->render();

		if(!count($archives))
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS",6);
			$this->tpl->parseCurrentBlock();
		}


		$counter = 0;
		foreach($archives as $archive_data)
		{
			$this->tpl->setCurrentBlock("tbl_content");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));


			if($this->download_allowed)
			{
				$this->tpl->setVariable("VAL_CHECK",ilUtil::formCheckbox(0,"archives[]",$archive_data['archive_id']));
			}
			// Type
			switch($archive_data["archive_type"])
			{
				case $this->course_obj->archives_obj->ARCHIVE_XML:
					$type = $this->lng->txt("crs_xml");
					break;

				case $this->course_obj->archives_obj->ARCHIVE_HTML:
					$type = $this->lng->txt("crs_html");
					break;

				case $this->course_obj->archives_obj->ARCHIVE_PDF:
					$type = $this->lng->txt("crs_pdf");
					break;
			}
			$this->tpl->setVariable("VAL_TYPE",$type);

			// Name
			if($archive_data['archive_type'] == $this->course_obj->archives_obj->ARCHIVE_HTML)
			{
				$link = '<a href="'.$this->course_obj->archives_obj->course_files_obj->getOnlineLink($archive_data['archive_name']).'"'.
					' target="_blank">'.$archive_data["archive_name"].'</a>';
			}
			else
			{
				$link = $archive_data['archive_name'];
			}

			$this->tpl->setVariable("VAL_NAME",$link);
			$this->tpl->setVariable("VAL_SIZE",$archive_data['archive_size']);
			$this->tpl->setVariable('VAL_DATE',ilDatePresentation::formatDate(new ilDateTime($archive_data['archive_name'],IL_CAL_UNIX)));
			
			if($archive_data["archive_lang"])
			{
				$this->tpl->setVariable("VAL_LANG",$this->lng->txt('lang_'.$archive_data["archive_lang"]));
			}
			else
			{
				$this->tpl->setVariable("VAL_LANG",$this->lng->txt('crs_not_available'));
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->parseCurrentBlock();
	}


	function confirmDeleteArchives()
	{
		global $ilAccess,$ilErr;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$ilAccess->checkAccess("write",'',$this->course_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$ilErr->MESSAGE);
		}

		if(!$_POST['archives'])
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_archives_selected"));
			$this->view();
			return false;
		}
		$_SESSION["crs_archives"] = $_POST["archives"];
		ilUtil::sendInfo($this->lng->txt("crs_sure_delete_selected_archives"));
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.crs_confirm_delete_archives.html','Modules/Course');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_ARCHIVE_NAME",$this->lng->txt('crs_file_name'));

		$this->course_obj->initCourseArchiveObject();
		$counter = 0;

		foreach($_POST['archives'] as $archive_id)
		{
			$archive = $this->course_obj->archives_obj->getArchive($archive_id);
			$this->tpl->setCurrentBlock("archives");

			$this->tpl->setVariable("ARCHIVE_NAME",$archive['archive_name']);
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("DELETE",$this->lng->txt('delete'));
		$this->tpl->setVariable("CANCEL",$this->lng->txt('cancel'));

		return true;
	}

	function delete()
	{
		global $ilAccess,$ilErr;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$ilAccess->checkAccess("write",'',$this->course_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$ilErr->MESSAGE);
		}

		if(!$_SESSION['crs_archives'])
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_archives_selected"));
			$this->view();
		}
		
		$this->course_obj->initCourseArchiveObject();
		foreach($_SESSION['crs_archives'] as $archive_id)
		{
			$this->course_obj->archives_obj->delete($archive_id);
		}
		
		ilUtil::sendInfo($this->lng->txt('crs_archives_deleted'));
		unset($_SESSION["crs_archives"]);

		$this->view();
		return true;
	}

	/**
	* Select items for archive
	*/
	function selectXMLArchiveItems()
	{
		global $tpl;
		
		include_once("./Services/Export/classes/class.ilSubItemSelectionTableGUI.php");
		$sel_table = new ilSubItemSelectionTableGUI($this, "selectXMlArchiveItems",
			$this->course_obj->getRefId(), "addXMLArchive",
			$this->lng->txt("crs_add_archive_xml"));
		$tpl->setContent($sel_table->getHTML());
	}
	
	function addXMLArchive()
	{
		global $ilAccess;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$ilAccess->checkAccess("write",'',$this->course_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$ilErr->MESSAGE);
		}

		$this->course_obj->initCourseArchiveObject();
		$this->course_obj->archives_obj->addXML();
		
		ilUtil::sendInfo($this->lng->txt("crs_added_new_archive"));
		$this->view();

		return true;
	}

	function selectArchiveLanguage()
	{
		global $ilAccess;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$ilAccess->checkAccess("write",'',$this->course_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$ilErr->MESSAGE);
		}

		foreach($this->lng->getInstalledLanguages() as $lang_code)
		{
			$actions["$lang_code"] = $this->lng->txt('lang_'.$lang_code);

			if($this->lng->getLangKey() == $lang_code)
			{
				$selected = $lang_code;
			}
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_selectLanguage.html",'Modules/Course');

		$this->tpl->setVariable("LANGUAGE_SELECTION",$this->lng->txt('crs_archive_language_selection'));
		$this->tpl->setVariable("LANGUAGE",$this->lng->txt('obj_lng'));
		$this->tpl->setVariable("INFO_TXT",$this->lng->txt('crs_select_archive_language'));
		$this->tpl->setVariable("SELECT_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("LANG_SELECTOR",ilUtil::formSelect($selected,'lang',$actions,false,true));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt('crs_add_html_archive'));

		return true;
	}

	function addHTMLArchive()
	{
		global $ilAccess;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$ilAccess->checkAccess("write",'',$this->course_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$ilErr->MESSAGE);
		}
		
		$this->course_obj->initCourseArchiveObject();
		$this->course_obj->archives_obj->setLanguage($_POST['lang']);
		$this->course_obj->archives_obj->addHTML();

		ilUtil::sendInfo($this->lng->txt("crs_added_new_archive"));
		$this->view();

		return true;
	}

	function downloadArchives()
	{
		global $ilAccess;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$ilAccess->checkAccess("read",'',$this->course_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$ilErr->MESSAGE);
		}

		$_POST["archives"] = $_POST["archives"] ? $_POST["archives"] : array();

		if(!count($_POST['archives']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_archive_selected'));
			$this->view();

			return false;
		}
		if(count($_POST['archives']) > 1)
		{
			ilUtil::sendInfo($this->lng->txt('crs_select_one_archive'));
			$this->view();

			return false;
		}

		$this->course_obj->initCourseArchiveObject();
		
		$abs_path = $this->course_obj->archives_obj->getArchiveFile((int) $_POST['archives'][0]);
		$basename = basename($abs_path);

		ilUtil::deliverFile($abs_path,$basename);
	}
		

	function __showArchivesMenu()
	{
		if(!$this->is_tutor)
		{
			return false;
		}
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		if(!PATH_TO_ZIP)
		{
			ilUtil::sendInfo($this->lng->txt('zip_test_failed'));
			return true;
		}

		// create xml archive button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "selectXMLArchiveItems"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("crs_add_archive_xml"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, "selectArchiveLanguage"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("crs_add_archive_html"));
		$this->tpl->parseCurrentBlock();
		
		return true;
	}

	function __initCourseObject()
	{
		global $tree;

		if($this->content_obj->getType() == 'crs')
		{
			// Container is course
			$this->course_obj =& $this->content_obj;
		}
		else
		{
			$course_ref_id = $tree->checkForParentType($this->content_obj->getRefId(),'crs');
			$this->course_obj =& ilObjectFactory::getInstanceByRefId($course_ref_id);
		}
		return true;
	}

	
} // END class.ilCourseArchivesGUI
?>
