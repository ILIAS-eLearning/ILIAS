<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Description of ilDidacticTemplateSettingsTableGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplates
 */
class ilDidacticTemplateSettingsTableGUI extends ilTable2GUI
{

	/**
	 * Constructor
	 * @param object $a_parent_obj
	 * @param string $a_parent_cmd
	 */
	public function  __construct($a_parent_obj, $a_parent_cmd = "")
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setId('tbl_didactic_tpl_settings');
	}

	/**
	 * Init table
	 */
	public function init()
	{
		global $ilCtrl, $lng;

		$this->addColumn('','f','1px');
		$lng->loadLanguageModule('search');
		$this->addColumn($this->lng->txt('search_title_description'),'title','40%');
		$this->addColumn($this->lng->txt('didactic_applicable_for'),'applicable','20%');
		$this->addColumn($this->lng->txt('active'),'active','20%');
		$this->addColumn($this->lng->txt('actions'),'','20%');

		$this->setTitle($this->lng->txt('didactic_available_templates'));

		$this->addMultiCommand('activateTemplates', $this->lng->txt('activate'));
		$this->addMultiCommand('deactivateTemplates', $this->lng->txt('deactivate'));
		$this->addMultiCommand('confirmDelete',$this->lng->txt('delete'));



		$this->setRowTemplate('tpl.didactic_template_overview_row.html','Services/DidacticTemplate');
		$this->setDefaultOrderField('title');
		$this->setDefaultOrderDirection('asc');
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
		$this->setSelectAllCheckbox('tpls');
	}

	/**
	 * Parse didactic templates
	 */
	public function parse()
	{
		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSettings.php';
		$tpls = ilDidacticTemplateSettings::getInstance();
		$tpls->readInactive();

		$counter = 0;
		foreach($tpls->getTemplates() as $tpl)
		{
			/* @var $tpl ilDidacticTemplateSetting */
			$data[$counter]['id'] = $tpl->getId();
			$data[$counter]['title'] = $tpl->getTitle();
			$data[$counter]['description'] = $tpl->getDescription();
			$data[$counter]['info'] = $tpl->getInfo();
			$data[$counter]['enabled'] = (int) $tpl->isEnabled();
			$data[$counter]['assignments'] = $tpl->getAssignments();

			++$counter;
		}

		$this->setData((array) $data);
	}

	/**
	 * Fill row
	 * @param array $set
	 */
	public function fillRow($set)
	{
		global $ilCtrl;

		// @TODO: Check for system template and hide checkbox
		$this->tpl->setVariable('VAL_ID',$set['id']);
		$this->tpl->setVariable('VAL_TITLE', $set['title']);
		$this->tpl->setVariable('VAL_DESC', $set['description']);

		foreach((array) explode("\n", $set['info']) as $info)
		{
			$trimmed_info = trim($info);
			if($trimmed_info)
			{
				$this->tpl->setCurrentBlock('info');
				$this->tpl->setVariable('VAL_INFO',$trimmed_info);
				$this->tpl->parseCurrentBlock();
			}
		}


		$this->tpl->setVariable('VAL_IMAGE',
			$set['enabled'] ? 
			ilUtil::getImagePath('icon_ok.svg') :
			ilUtil::getImagePath('icon_not_ok.svg')
		);
		$this->tpl->setVariable('VAL_ENABLED_TXT',
			$set['enabled'] ?
			$this->lng->txt('active') :
			$this->lng->txt('inactive')
		);


		$atxt = '';
		foreach((array) $set['assignments'] as $obj_type)
		{
			$atxt .= ($this->lng->txt('objs_'.$obj_type).'<br/>');
		}
		$this->tpl->setVariable('VAL_APPLICABLE', $atxt);

		$ilCtrl->setParameterByClass(
			get_class($this->getParentObject()),
			'tplid',
			$set['id']
		);

		// Edit
		$this->tpl->setCurrentBlock('action_link');
		$this->tpl->setVariable(
			'A_LINK',
			$ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()),'editTemplate')
		);
		$this->tpl->setVariable('A_TEXT',$this->lng->txt('edit'));
		$this->tpl->parseCurrentBlock();

		// Copy
		$this->tpl->setCurrentBlock('action_link');
		$this->tpl->setVariable(
			'A_LINK',
			$ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()),'copyTemplate')
		);
		$this->tpl->setVariable('A_TEXT', $this->lng->txt('copy'));
		$this->tpl->parseCurrentBlock();

		// Export
		$this->tpl->setCurrentBlock('action_link');
		$this->tpl->setVariable(
			'A_LINK',
			$ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()),'exportTemplate')
		);
		$this->tpl->setVariable('A_TEXT', $this->lng->txt('didactic_do_export'));
		$this->tpl->parseCurrentBlock();
	}
}
?>
