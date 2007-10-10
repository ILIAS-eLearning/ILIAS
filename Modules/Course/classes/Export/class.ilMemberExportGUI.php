<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once('Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php');
include_once('Modules/Course/classes/Export/class.ilExportUserSettings.php');
include_once('Modules/Course/classes/Export/class.ilMemberExport.php');
include_once('Modules/Course/classes/class.ilFileDataCourse.php');
include_once('Modules/Course/classes/class.ilFSStorageCourse.php');
include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
include_once('Services/User/classes/class.ilUserDefinedFields.php');

/**  
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilMemberExportGUI:
* @ingroup ModulesCourse 
*/
class ilMemberExportGUI
{
	private $ref_id;
	private $obj_id;
	private $ctrl;
	private $tpl;
	private $lng;
	
	private $fields_info;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_ref_id)
	{
		global $ilCtrl,$tpl,$lng,$ilUser,$ilObjDataCache;
		
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('ps');
	 	$this->ref_id = $a_ref_id;
	 	$this->obj_id = $ilObjDataCache->lookupObjId($this->ref_id);
	 	
	 	$this->fields_info = ilExportFieldsInfo::_getInstance();
	}
	
	/**
	 * Execute Command
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = 'show';
				}
				$this->$cmd();
				break;
		}	 	
	}
	
	/**
	 * Show list of export files
	 *
	 * @access public
	 * 
	 */
	public function show($a_deliver_file = false)
	{
		global $ilUser;
		
		$this->showFileList();		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.member_export.html','Modules/Course');
		$this->tpl->setVariable('FORM_ACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TXT_EXPORT_SETTINGS',$this->lng->txt('ps_export_settings'));
		$this->tpl->setVariable('TXT_USER_SELECTION',$this->lng->txt('ps_user_selection'));
		$this->tpl->setVariable('TXT_EXPORT_ADMIN',$this->lng->txt('ps_export_admin'));
		$this->tpl->setVariable('TXT_EXPORT_TUTOR',$this->lng->txt('ps_export_tutor'));
		$this->tpl->setVariable('TXT_EXPORT_MEMBER',$this->lng->txt('ps_export_member'));
		$this->tpl->setVariable('TXT_EXPORT_WAIT',$this->lng->txt('ps_export_wait'));
		$this->tpl->setVariable('TXT_EXPORT_SUB',$this->lng->txt('ps_export_sub'));
		
		// Check user selection
	 	$this->exportSettings = new ilExportUserSettings($ilUser->getId(),$this->obj_id);
		
	 	$this->tpl->setVariable('CHECK_EXPORT_ADMIN',ilUtil::formCheckbox($this->exportSettings->enabled('admin'),'export_members[admin]',1));
	 	$this->tpl->setVariable('CHECK_EXPORT_TUTOR',ilUtil::formCheckbox($this->exportSettings->enabled('tutor'),'export_members[tutor]',1));
	 	$this->tpl->setVariable('CHECK_EXPORT_MEMBER',ilUtil::formCheckbox($this->exportSettings->enabled('member'),'export_members[member]',1));
	 	$this->tpl->setVariable('CHECK_EXPORT_SUB',ilUtil::formCheckbox($this->exportSettings->enabled('subscribers'),'export_members[subscribers]',1));
	 	$this->tpl->setVariable('CHECK_EXPORT_WAIT',ilUtil::formCheckbox($this->exportSettings->enabled('waiting_list'),'export_members[waiting_list]',1));
		
		$this->tpl->setVariable('TXT_EXPORT',$this->lng->txt('ps_perform_export'));

		// User Data
		$this->tpl->setVariable('TXT_USER_DATA_SELECTION',$this->lng->txt('ps_export_data'));
		$this->tpl->setVariable('TXT_EXPORT_USER_DATA_HEADER',$this->lng->txt('ps_export_user_data'));
		
		
		$fields = $this->fields_info->getFieldsInfo();
		foreach($fields as $field => $exportable)
		{
			if(!$exportable)
			{
				continue;
			}
			$this->tpl->setCurrentBlock('user_data_row');
			$this->tpl->setVariable('CHECK_EXPORT_USER_DATA',ilUtil::formCheckbox($this->exportSettings->enabled($field),'export_members['.$field.']',1));
			$this->tpl->setVariable('TXT_EXPORT_USER_DATA',$this->lng->txt($field));
			$this->tpl->parseCurrentBlock();
		}
		
		$udf = ilUserDefinedFields::_getInstance();
		foreach($course_exp = $udf->getCourseExportableFields() as $field_id => $udf_data)
		{
			$this->tpl->setCurrentBlock('user_data_row');
			$this->tpl->setVariable('CHECK_EXPORT_USER_DATA',ilUtil::formCheckbox($this->exportSettings->enabled('udf_'.$field_id),
				'export_members[udf_'.$field_id.']',1));
			$this->tpl->setVariable('TXT_EXPORT_USER_DATA',$udf_data['field_name']);
			$this->tpl->parseCurrentBlock();
		}
		
		
		$cdf_fields = ilCourseDefinedFieldDefinition::_getFields($this->obj_id);
		foreach($cdf_fields as $field_obj)
		{
			$this->tpl->setCurrentBlock('cdf_row');
			$this->tpl->setVariable('CHECK_CDF_DATA',ilUtil::formCheckbox($this->exportSettings->enabled('cdf_'.$field_obj->getId()),
																		'export_members[cdf_'.$field_obj->getId().']',
																		1));
			$this->tpl->setVariable('TXT_CDF_NAME',$field_obj->getName());
			$this->tpl->parseCurrentBlock();
		}
		if(count($cdf_fields))
		{
			$this->tpl->setCurrentBlock('cdf_fields');
			$this->tpl->setVariable('TXT_CDF_SELECTION',$this->lng->txt('ps_crs_user_fields'));
			$this->tpl->parseCurrentBlock();
		}
		
		if($a_deliver_file and 0)
		{
			$this->tpl->setCurrentBlock('iframe');
			$this->tpl->setVariable('SOURCE',$this->ctrl->getLinkTarget($this,'deliverData'));
		}
	}
	
	/**
	 * Deliver Data
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function deliverData()
	{
		$this->fss_export = new ilFSStorageCourse($this->obj_id); 
		
		foreach($this->fss_export->getMemberExportFiles() as $file)
		{
			if($file['name'] == $_SESSION['member_export_filename'])
			{
				$content = $this->fss_export->getMemberExportFile($_SESSION['member_export_filename']);
				ilUtil::deliverData($content,date('Y_m_d_H-i',$file['timest']).
				'_member_export_'.
				$this->obj_id.
				'.csv','text/csv');
			}
		}
	}
	
	/**
	 * Show file list of available export files
	 *
	 * @access public
	 * 
	 */
	public function showFileList()
	{
		global $ilUser;
		
		$this->fss_export = new ilFSStorageCourse($this->obj_id);
		if(!count($files = $this->fss_export->getMemberExportFiles()))
		{
			return false;
		}
		
	 	$a_tpl = new ilTemplate('tpl.table.html',true,true);
		$a_tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.member_export_file_row.html", "Modules/Course");
		$a_tpl->setVariable('FORMACTION',$this->ctrl->getFormaction($this));

		include_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load template for table content data

		$tbl->setTitle($this->lng->txt("ps_export_files"));

		$tbl->setHeaderNames(array("", $this->lng->txt("type"),
				$this->lng->txt("ps_size"),
				$this->lng->txt("date") ));

		$cols = array("", "type","size", "date");

		$header_params = $this->ctrl->getParameterArray($this,'show');
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("1%", "9%", "45%", "45%"));
		
		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($ilUser->getPref('hits_per_page',9999));
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($files));
		$tbl->disable("sort");
		$a_tpl->setVariable("COLUMN_COUNTS",4);
		
	 	$files = array_reverse($files);
		$files = array_slice($files, $_GET["offset"], $_GET["limit"]);
		$num = 0;
		$i=0;
		foreach($files as $exp_file)
		{
			$a_tpl->setCurrentBlock("tbl_content");
			$a_tpl->setVariable("TXT_FILENAME", $exp_file["file"]);

			$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
			$a_tpl->setVariable("CSS_ROW", $css_row);

			$a_tpl->setVariable("TXT_SIZE",$exp_file['size']);
			$a_tpl->setVariable("TXT_TYPE", strtoupper($exp_file["type"]));
			$a_tpl->setVariable("CHECKBOX_ID",$exp_file["timest"]);
			$a_tpl->setVariable("TXT_DATE", date("Y-m-d H:i",$exp_file['timest']));
			$a_tpl->parseCurrentBlock();
		}
		

		// delete button
		$a_tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$a_tpl->setCurrentBlock("tbl_action_btn");
		$a_tpl->setVariable("BTN_NAME", "confirmDeleteExportFile");
		$a_tpl->setVariable("BTN_VALUE", $this->lng->txt("delete"));
		$a_tpl->parseCurrentBlock();

		$a_tpl->setCurrentBlock("tbl_action_btn");
		$a_tpl->setVariable("BTN_NAME", "downloadExportFile");
		$a_tpl->setVariable("BTN_VALUE", $this->lng->txt("download"));
		$a_tpl->parseCurrentBlock();

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		$tbl->setTemplate($a_tpl);
		$tbl->render();
		
		$this->tpl->setCurrentBlock('file_list');
		$this->tpl->setVariable('FILE_LIST_TABLE',$a_tpl->get());
		$this->tpl->parseCurrentBlock();
		
	}
	
	/**
	 * Download export file
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function downloadExportFile()
	{
	 	if(count($_POST['files']) != 1)
	 	{
	 		ilUtil::sendInfo($this->lng->txt('ps_select_one'));
	 		$this->show();
	 		return true;
	 	}
		$this->fss_export = new ilFSStorageCourse($this->obj_id);
		foreach($this->fss_export->getMemberExportFiles() as $file)
		{
			if(!in_array($file['timest'],$_POST['files']))
			{
				continue;
			}
			$contents = $this->fss_export->getMemberExportFile($file['timest'].'_participant_export_'.
				$file['type'].'_'.$this->obj_id.'.'.$file['type']);
			ilUtil::deliverData($contents,date('Y_m_d_H-i'.$file['timest']).
				'_member_export_'.
				$this->obj_id.
				'.csv','text/csv');
			return true;
			
		}
	 	
	 	
	}
	
	/**
	 * Confirm deletion of export files
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function confirmDeleteExportFile()
	{
	 	if(!count($_POST['files']))
	 	{
	 		ilUtil::sendInfo($this->lng->txt('ps_select_one'));
	 		$this->show();
	 		return false;
	 	}
	 	$_SESSION['il_del_member_export'] = $_POST['files'];
		ilUtil::sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.member_export_confirm_delete.html','Modules/Course');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable('TEXT',$this->lng->txt('ps_delete_export_files'));
		
		
		$this->fss_export = new ilFSStorageCourse($this->obj_id);
		$counter = 0;
		foreach($this->fss_export->getMemberExportFiles() as $file)
		{
			if(!in_array($file['timest'],$_POST['files']))
			{
				continue;
			}
			$this->tpl->setCurrentBlock('table_row');
			$this->tpl->setVariable('CSS_ROW',ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$this->tpl->setVariable('TEXT_TYPE',strtoupper($file['type']));
			$this->tpl->setVariable('DATE',ilFormat::formatUnixTime($file['timest'],true));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock('operation_btn');
		$this->tpl->setVariable('BTN_NAME','deleteExportFile');
		$this->tpl->setVariable('BTN_VALUE',$this->lng->txt('delete'));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock('operation_btn');
		$this->tpl->setVariable('BTN_NAME','show');
		$this->tpl->setVariable('BTN_VALUE',$this->lng->txt('cancel'));
		$this->tpl->parseCurrentBlock();
				
	}
	
	/**
	 * Delete member export files
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function deleteExportFile()
	{
	 	if(!is_array($_SESSION['il_del_member_export']))
	 	{
	 		$this->show();
	 		return false;
	 	}
		$this->fss_export = new ilFSStorageCourse($this->obj_id);
		$counter = 0;
		foreach($this->fss_export->getMemberExportFiles() as $file)
		{
			if(!in_array($file['timest'],$_SESSION['il_del_member_export']))
			{
				continue;
			}
			$this->fss_export->deleteMemberExportFile($file['timest'].'_participant_export_'.$file['type'].'_'.$this->obj_id.'.'.$file['type']);
		}
		ilUtil::sendInfo($this->lng->txt('ps_files_deleted'));
		$this->show();
	}
	
	
	

	/**
	 * Export Create member export file and store it in data directory.
	 *
	 * @access public
	 * 
	 */
	public function export()
	{
		global $ilUser;

		// Save settings
	 	$this->exportSettings = new ilExportUserSettings($ilUser->getId(),$this->obj_id);
		$this->exportSettings->set($_POST['export_members']);
		$this->exportSettings->store();
		
		$this->export = new ilMemberExport($this->ref_id);
		$this->export->create();
	 	
	 	$this->fss_export = new ilFSStorageCourse($this->obj_id);
	 	$filename = time().'_participant_export_csv_'.$this->obj_id.'.csv';
		$this->fss_export->addMemberExportFile($this->export->getCSVString(),$filename);

		$_SESSION['member_export_filename'] = $filename;
		
	 	$this->show(true);
	}
	
}
?>