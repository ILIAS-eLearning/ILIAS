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

include_once('./Services/ContainerReference/classes/class.ilContainerReferenceGUI.php');
/**
 * 
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilObjCourseReferenceGUI: ilPermissionGUI, ilInfoScreenGUI, ilPropertyFormGUI
 * @ilCtrl_Calls ilObjCourseReferenceGUI: ilCommonActionDispatcherGUI
 * 
 * @ingroup ModulesCourseReference
 */
class ilObjCourseReferenceGUI extends ilContainerReferenceGUI
{
	/**
	 * @var \ilLogger | null
	 */
	private $logger = null;

	protected $target_type = 'crs';
	protected $reference_type = 'crsr';

	/**
	 * Constructor
	 * @param
	 * @return
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $DIC;

		$this->logger = $DIC->logger()->crsr();

		parent::__construct($a_data, $a_id, true, false);

		$this->lng->loadLanguageModule('crs');
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		parent::executeCommand();
	}
	/**
	 * @inheritdoc
	 */
	public function initForm($a_mode = self::MODE_EDIT)
	{
		$form = parent::initForm($a_mode);

		if($a_mode == self::MODE_CREATE) {
			return $form;
		}

		$path_info = \ilCourseReferencePathInfo::getInstanceByRefId($this->object->getRefId(), $this->object->getTargetRefId());


		// nothing todo if no parent course is in path
		if(!$path_info->hasParentCourse())
		{
			return $form;
		}

		$access = $path_info->checkManagmentAccess();

		$auto_update = new \ilCheckboxInputGUI($this->lng->txt('crs_ref_member_update'),'member_update');
		$auto_update->setChecked($this->object->isMemberUpdateEnabled());
		$auto_update->setInfo($this->lng->txt('crs_ref_member_update_info'));
		$auto_update->setDisabled(!$access);
		$form->addItem($auto_update);

		return $form;
	}

	/**
	 * @param \ilPropertyFormGUI $form
	 * @return bool
	 */
	protected function loadPropertiesFromSettingsForm(ilPropertyFormGUI $form): bool
	{
		$ok = true;
		$ok = parent::loadPropertiesFromSettingsForm($form);

		$path_info = ilCourseReferencePathInfo::getInstanceByRefId($this->object->getRefId(), $this->object->getTargetRefId());

		$auto_update = $form->getInput('member_update');
		if($auto_update && !$path_info->hasParentCourse()) {
			$ok = false;
			$form->getItemByPostVar('member_update')->setAlert($this->lng->txt('crs_ref_missing_parent_crs'));
		}
		if($auto_update && !$path_info->checkManagmentAccess()) {
			$ok = false;
			$form->getItemByPostVar('member_update')->setAlert($this->lng->txt('crs_ref_missing_access'));
		}

		// check manage members
		$this->object->enableMemberUpdate((bool) $form->getInput('member_update'));

		return $ok;
	}


	/**
	 * Support for goto php
	 *
	 * @return void
	 * @static
	 */
	public static function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
		$target_ref_id = ilContainerReference::_lookupTargetRefId(ilObject::_lookupObjId($a_target));

		include_once('./Modules/Course/classes/class.ilObjCourseGUI.php');
		ilObjCourseGUI::_goto($target_ref_id);
	}

}
?>