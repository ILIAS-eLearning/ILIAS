<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/classes/class.ilUIHookPluginGUI.php';

/**
 * Class ilAptarInterfaceLogOverviewUIHookGUI
 */
class ilAptarInterfaceLogOverviewUIHookGUI extends ilUIHookPluginGUI
{
	/**
	 * {@inheritdoc}
	 */
	public function getHTML($a_comp, $a_part, $a_par = array())
	{
		return array(
			'mode' => ilUIHookPluginGUI::KEEP,
			'html' => ''
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function gotoHook()
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $ilDB   ilDB
		 */
		global $ilCtrl, $ilDB;

		if(isset($_GET['target']))
		{
			if($_GET['target'] == 'ifppl_table')
			{
				$res = $ilDB->queryF("
					SELECT ref_id
					FROM object_data
					INNER JOIN object_reference ON object_reference.obj_id = object_data.obj_id
					WHERE object_data.type = %s
					",
					array('text'),
					array('cmps')
				);
				$row = $ilDB->fetchAssoc($res);
				if(is_array($row) && isset($row['ref_id']))
				{
					$ilCtrl->setTargetScript('ilias.php');
					$_GET['baseClass'] = 'ilAdministrationGUI';
					$ilCtrl->setCmdClass('ilAptarInterfaceLogOverviewConfigGUI');
					$ilCtrl->setParameterByClass('ilAptarInterfaceLogOverviewConfigGUI', "ref_id", $row['ref_id']);
					$ilCtrl->setParameterByClass('ilAptarInterfaceLogOverviewConfigGUI', "ctype", 'Services');
					$ilCtrl->setParameterByClass('ilAptarInterfaceLogOverviewConfigGUI', "cname", 'UIComponent');
					$ilCtrl->setParameterByClass('ilAptarInterfaceLogOverviewConfigGUI', "slot_id", 'uihk');
					$ilCtrl->setParameterByClass('ilAptarInterfaceLogOverviewConfigGUI', "pname", 'AptarInterfaceLogOverview');
					$ilCtrl->redirectByClass(
						array('ilAdministrationGUI', 'ilObjComponentSettingsGUI', 'ilAptarInterfaceLogOverviewConfigGUI'),
						'showDataTable'
					);
				}
			}
		}

		return parent::checkGotoHook();
	}
}