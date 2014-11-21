<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';

/**
 * Selection of subitems
 * 
 * @version $Id$
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjectCopySelectionTableGUI extends ilTable2GUI
{
	private $type = '';
	private $selected_reference = null;
	
	/**
	 * 
	 * @param object $a_parent_class
	 * @param string $a_parent_cmd
	 * @return 
	 */
	public function __construct($a_parent_class,$a_parent_cmd,$a_type,$a_back_cmd)
	{
		global $lng,$ilCtrl,$ilUser,$objDefinition;
		
		parent::__construct($a_parent_class,$a_parent_cmd);
		$this->type = $a_type;
		
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		
		$this->setTitle($this->lng->txt($this->type.'_wizard_page'));
		
		
		$this->addColumn($this->lng->txt('title'),'','55%');
		$this->addColumn($this->lng->txt('copy'),'','15%');
		$this->addColumn($this->lng->txt('link'),'','15%');
		$this->addColumn($this->lng->txt('omit'),'','15%');
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
		$this->setRowTemplate("tpl.obj_copy_selection_row.html", "Services/Object");
		$this->setEnableTitle(true);
		$this->setEnableNumInfo(true);
		$this->setLimit(999999);
		
		$this->setFormName('cmd');
		
		$this->addCommandButton('copyContainer', $this->lng->txt('obj_'.$this->type.'_duplicate'));
		$this->addCommandButton($a_back_cmd, $this->lng->txt('btn_back'));
	}
	
	/**
	 * Get object type of source
	 * @return 
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * 
	 * @param object $a_source
	 * @return 
	 */
	public function parseSource($a_source)
	{
		global $tree,$objDefinition, $ilAccess;
		
		$first = true;
		foreach($tree->getSubTree($root = $tree->getNodeData($a_source)) as $node)
		{
			if($node['type'] == 'rolf')
			{
				continue;
			}
			if(!$ilAccess->checkAccess('visible,read','',$node['child']))
			{
				continue;
			}
			
			
			$r = array();
			$r['last'] 	= false;
			$r['source']= $first;
			$r['ref_id']= $node['child'];
			$r['depth'] = $node['depth'] - $root['depth'];
			$r['type']	= $node['type'];
			$r['title']	= $node['title'];
			$r['copy']	= $objDefinition->allowCopy($node['type']);
			$r['perm_copy'] = $ilAccess->checkAccess('copy','',$node['child']);
			$r['link']	= $objDefinition->allowLink($node['type']);
			$r['perm_link'] = true;
			
			// #11905
			if(!trim($r['title']) && $r['type'] == 'sess')
			{
				// use session date as title if no object title
				include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
				$app_info = ilSessionAppointment::_lookupAppointment($node["obj_id"]); 	
				$r['title'] = ilSessionAppointment::_appointmentToString($app_info['start'], $app_info['end'],$app_info['fullday']);
			}
			
			$rows[] = $r;
			
			$first = false;
		}
		$rows[] = array('last' => true);
		$this->setData((array) $rows);
	}

	/**
	 * @see ilTable2GUI::fillRow()
	 */
	protected function fillRow($s)
	{
		if($s['last'])
		{
			$this->tpl->setCurrentBlock('footer_copy');
			$this->tpl->setVariable('TXT_COPY_ALL',$this->lng->txt('copy_all'));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock('footer_link');
			$this->tpl->setVariable('TXT_LINK_ALL',$this->lng->txt('link_all'));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock('footer_omit');
			$this->tpl->setVariable('TXT_OMIT_ALL',$this->lng->txt('omit_all'));
			$this->tpl->parseCurrentBlock();
			return true;
		}
		
		
		for($i = 0; $i < $s['depth']; $i++)
		{
			$this->tpl->touchBlock('padding');
			$this->tpl->touchBlock('end_padding');
		}
		$this->tpl->setVariable('TREE_IMG',ilObject::_getIcon(ilObject::_lookupObjId($s['ref_id']), "tiny", $s['type']));
		$this->tpl->setVariable('TREE_ALT_IMG',$this->lng->txt('obj_'.$s['type']));
		$this->tpl->setVariable('TREE_TITLE',$s['title']);
		
		if($s['source'])
		{
			return true;
		}

		// Copy
		if($s['perm_copy'] and $s['copy'])
		{
			$this->tpl->setCurrentBlock('radio_copy');
			$this->tpl->setVariable('TXT_COPY',$this->lng->txt('copy'));
			$this->tpl->setVariable('NAME_COPY','cp_options['.$s['ref_id'].'][type]');
			$this->tpl->setVariable('VALUE_COPY',ilCopyWizardOptions::COPY_WIZARD_COPY);
			$this->tpl->setVariable('ID_COPY',$s['depth'].'_'.$s['type'].'_'.$s['ref_id'].'_copy');
			$this->tpl->setVariable('COPY_CHECKED','checked="checked"');
			$this->tpl->parseCurrentBlock();
		}
		elseif($s['copy'])
		{
			$this->tpl->setCurrentBlock('missing_copy_perm');
			$this->tpl->setVariable('TXT_MISSING_COPY_PERM',$this->lng->txt('missing_perm'));
			$this->tpl->parseCurrentBlock();
		}

		
		// Link
		if($s['perm_link'] and $s['link'])
		{
			$this->tpl->setCurrentBlock('radio_link');
			$this->tpl->setVariable('TXT_LINK',$this->lng->txt('link'));
			$this->tpl->setVariable('NAME_LINK','cp_options['.$s['ref_id'].'][type]');
			$this->tpl->setVariable('VALUE_LINK',ilCopyWizardOptions::COPY_WIZARD_LINK);
			$this->tpl->setVariable('ID_LINK',$s['depth'].'_'.$s['type'].'_'.$s['ref_id'].'_link');
			if(!$s['copy'] or !$s['perm_copy'])
			{
				$this->tpl->setVariable('LINK_CHECKED','checked="checked"');
			}
			$this->tpl->parseCurrentBlock();
		}
		elseif($s['link'])
		{
			$this->tpl->setCurrentBlock('missing_link_perm');
			$this->tpl->setVariable('TXT_MISSING_LINK_PERM',$this->lng->txt('missing_perm'));
			$this->tpl->parseCurrentBlock();
		}
		
		// Omit
		$this->tpl->setCurrentBlock('omit_radio');
		$this->tpl->setVariable('TXT_OMIT',$this->lng->txt('omit'));
		$this->tpl->setVariable('NAME_OMIT','cp_options['.$s['ref_id'].'][type]');
		$this->tpl->setVariable('VALUE_OMIT',ilCopyWizardOptions::COPY_WIZARD_OMIT);
		$this->tpl->setVariable('ID_OMIT',$s['depth'].'_'.$s['type'].'_'.$s['ref_id'].'_omit');
		if((!$s['copy'] or !$s['perm_copy']) and (!$s['link']))
		{
			$this->tpl->setVariable('OMIT_CHECKED','checked="checked"');
		}
		$this->tpl->parseCurrentBlock();
		
	}

	
	
}
?>