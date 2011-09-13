<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSetting.php';

/**
 * GUI class for didactic template settings inside repository objects
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 * @ilCtrl_IsCalledBy ilDidacticSettingsGUI: ilPermissionGUI
 */
class ilDidacticTemplateGUI
{
	private $parent_object;
	private $lng;

	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj)
	{
		global $lng;
		
		$this->parent_object = $a_parent_obj;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('didactic');
	}

	public function getParentObject()
	{
		return $this->parent_object;
	}

	/**
	 * Execute command
	 * @return <type> 
	 */
	public function executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = 'overview';
				}
				$this->$cmd();

				break;
		}
		return true;
	}

	public function appendToolbarSwitch(ilToolbarGUI $toolbar, $a_obj_type, $a_ref_id)
	{
		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSettings.php';
		$tpls = ilDidacticTemplateSettings::getInstanceByObjectType($a_obj_type)->getTemplates();

		if(!count($tpls))
		{
			return false;
		}

		// Add template switch
		$toolbar->addText($this->lng->txt('didactic_selected_tpl_option'));

		// Show template options
		$options = array(0 => $this->lng->txt('default'));
		foreach($tpls as $tpl)
		{
			$options[$tpl->getId()] = $tpl->getTitle();
		}

		include_once './Services/Form/classes/class.ilSelectInputGUI.php';
		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
		$tpl_selection = new ilSelectInputGUI(
			'',
			'tplid'
		);
		$tpl_selection->setOptions($options);
		$tpl_selection->setValue(ilDidacticTemplateObjSettings::lookupTemplateId(
			$this->getParentObject()->object->getRefId()
		));
		$toolbar->addInputItem($tpl_selection);

		// Apply templates switch
		$toolbar->addFormButton($this->lng->txt('change'),'applyTemplate');
		return true;
	}

}
?>