<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Services/Export/classes/class.ilExportOptions.php';
include_once './Services/Export/classes/class.ilExportFileInfo.php';

/**
* Object selection for export
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesExport
*/
class ilExportSelectionTableGUI extends ilTable2GUI
{

	/**
	 * 
	 * @param object $a_parent_class
	 * @param string $a_parent_cmd
	 * @return 
	 */
	public function __construct($a_parent_class,$a_parent_cmd)
	{
		global $lng,$ilCtrl,$ilUser,$objDefinition;
		
		parent::__construct($a_parent_class,$a_parent_cmd);
		
		$this->lng = $lng;
		$this->lng->loadLanguageModule('export');
		$this->ctrl = $ilCtrl;
		
		$this->setTitle($this->lng->txt('export_select_resources'));
		
		
		$this->addColumn($this->lng->txt('title'),'');
		$this->addColumn($this->lng->txt('export_last_export'),'');
		$this->addColumn($this->lng->txt('export_last_export_file'),'');
		$this->addColumn($this->lng->txt('export_create_new_file'),'');
		$this->addColumn($this->lng->txt('export_omit_resource'),'');
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
		$this->setRowTemplate("tpl.export_item_selection_row.html", "Services/Export");
		$this->setEnableTitle(true);
		$this->setEnableNumInfo(true);
		$this->setLimit(999);
		
		$this->setFormName('cmd');
		
		$this->addCommandButton('saveItemSelection', $this->lng->txt('export_save_selection'));
		$this->addCommandButton($a_parent_cmd, $this->lng->txt('cancel'));
	}
	
	public function fillRow($s)
	{
		if($s['last'])
		{
			$this->tpl->setCurrentBlock('footer_export_e');
			$this->tpl->setVariable('TXT_EXPORT_E_ALL',$this->lng->txt('select_all'));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock('footer_export');
			$this->tpl->setVariable('TXT_EXPORT_ALL',$this->lng->txt('select_all'));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock('footer_omit');
			$this->tpl->setVariable('TXT_OMIT_ALL',$this->lng->txt('select_all'));
			$this->tpl->parseCurrentBlock();
			return true;
		}
		
		for($i = 0; $i < $s['depth']; $i++)
		{
			$this->tpl->touchBlock('padding');
			$this->tpl->touchBlock('end_padding');
		}
		$this->tpl->setVariable('TREE_IMG',ilUtil::getImagePath('icon_'.$s['type'].'.svg'));
		$this->tpl->setVariable('TREE_ALT_IMG',$this->lng->txt('obj_'.$s['type']));
		$this->tpl->setVariable('TREE_TITLE',$s['title']);
		
		
		if($s['last_export'])
		{
			$this->tpl->setVariable('VAL_LAST_EXPORT',ilDatePresentation::formatDate(new ilDateTime($s['last_export'],IL_CAL_UNIX)));
		}
		else
		{
			$this->tpl->setVariable('VAL_LAST_EXPORT',$this->lng->txt('no_date'));			
		}

		if($s['source'])
		{
			return true;
		}

		// Export existing
		if($s['perm_export'] and $s['last_export'])
		{
			$this->tpl->setCurrentBlock('radio_export_e');
			$this->tpl->setVariable('TXT_EXPORT_E',$this->lng->txt('export_existing'));
			$this->tpl->setVariable('NAME_EXPORT_E','cp_options['.$s['ref_id'].'][type]');
			$this->tpl->setVariable('VALUE_EXPORT_E',ilExportOptions::EXPORT_EXISTING);
			$this->tpl->setVariable('ID_EXPORT_E',$s['depth'].'_'.$s['type'].'_'.$s['ref_id'].'_export_e');
			$this->tpl->setVariable('EXPORT_E_CHECKED','checked="checked"');
			$this->tpl->parseCurrentBlock();
		}
		elseif(!$s['perm_export'])
		{
			$this->tpl->setCurrentBlock('missing_export_perm');
			$this->tpl->setVariable('TXT_MISSING_EXPORT_PERM',$this->lng->txt('missing_perm'));
			$this->tpl->parseCurrentBlock();
		}

		
		// Create new
		if($s['perm_export'] and $s['export'])
		{
			$this->tpl->setCurrentBlock('radio_export');
			$this->tpl->setVariable('TXT_EXPORT',$this->lng->txt('export'));
			$this->tpl->setVariable('NAME_EXPORT','cp_options['.$s['ref_id'].'][type]');
			$this->tpl->setVariable('VALUE_EXPORT',ilExportOptions::EXPORT_BUILD);
			$this->tpl->setVariable('ID_EXPORT',$s['depth'].'_'.$s['type'].'_'.$s['ref_id'].'_export');
			if(!$copy or !$perm_copy)
			{
				$this->tpl->setVariable('EXPORT_CHECKED','checked="checked"');
			}
			$this->tpl->parseCurrentBlock();
		}
		elseif($s['export'])
		{
			$this->tpl->setCurrentBlock('missing_export_perm');
			$this->tpl->setVariable('TXT_MISSING_EXPORT_PERM',$this->lng->txt('missing_perm'));
			$this->tpl->parseCurrentBlock();
		}
		
		// Omit
		$this->tpl->setCurrentBlock('omit_radio');
		$this->tpl->setVariable('TXT_OMIT',$this->lng->txt('omit'));
		$this->tpl->setVariable('NAME_OMIT','cp_options['.$s['ref_id'].'][type]');
		$this->tpl->setVariable('VALUE_OMIT',ilExportOptions::EXPORT_OMIT);
		$this->tpl->setVariable('ID_OMIT',$s['depth'].'_'.$s['type'].'_'.$s['ref_id'].'_omit');
		if((!$s['copy'] or !$s['perm_copy']) and (!$s['link']))
		{
			$this->tpl->setVariable('OMIT_CHECKED','checked="checked"');
		}
		$this->tpl->parseCurrentBlock();
		
		
	}
	
	/**
	 * parse tree
	 * @param object $a_source
	 * @return 
	 */
	public function parseContainer($a_source)
	{
		global $tree,$objDefinition, $ilAccess;
		
		$first = true;
		foreach($tree->getSubTree($root = $tree->getNodeData($a_source)) as $node)
		{
			if($node['type'] == 'rolf')
			{
				continue;
			}
			if(!$objDefinition->allowExport($node['type']))
			{
				#continue;
			}
			include_once("./Modules/File/classes/class.ilObjFileAccess.php");
			if ($node['type'] == "file" &&
				ilObjFileAccess::_isFileHidden($node['title']))
			{
				continue;
			}
			$r = array();

			if($last = ilExportFileInfo::lookupLastExport($node['obj_id'], 'xml'))
			{
				$r['last_export'] = $last->getCreationDate()->get(IL_CAL_UNIX);
			}
			else
			{
				$r['last_export'] = 0;
			}
			
			$r['last'] 	= false;
			$r['source']= $first;
			$r['ref_id']= $node['child'];
			$r['depth'] = $node['depth'] - $root['depth'];
			$r['type']	= $node['type'];
			$r['title']	= $node['title'];
			$r['export']	= $objDefinition->allowExport($node['type']);
			$r['perm_export'] = $ilAccess->checkAccess('write','',$node['child']);

			$rows[] = $r;
			
			$first = false;
		}
	
		$rows[] = array('last' => true);
		$this->setData((array) $rows);
	}	
	
}
?>