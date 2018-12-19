<?php

declare(strict_types=1);

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
 * Class ilObjLearningSequenceAccess class
 *
 */
class ilObjLearningSequenceAccess extends ilObjectAccess
{
	static $using_code = false;

	public static function _getCommands(): array
	{
		$commands = array(
			[
				'cmd' => ilObjLearningSequenceGUI::CMD_VIEW,
				'permission' => 'visible',
				'lang_var' => 'show',
				'default' => true
			],
			[
				'cmd' => ilObjLearningSequenceGUI::CMD_LEARNER_VIEW,
				'permission' => 'visible',
				'lang_var' => 'show',
			],
			[
				'cmd' => ilObjLearningSequenceGUI::CMD_CONTENT,
				'permission' => 'write',
				'lang_var' => 'edit_content'
			],
			[
				'cmd' => ilObjLearningSequenceGUI::CMD_SETTINGS,
				'permission' => 'write',
				'lang_var' => 'settings'
			],
			[
				'cmd' => ilObjLearningSequenceGUI::CMD_UNPARTICIPATE,
				'permission' => 'unparticipate',
				'lang_var' => 'unparticipate'
			]
		);
		return $commands;
	}

	public function usingRegistrationCode()
	{
		return self::$using_code;
	}

	public static function _isOffline($obj_id)
	{
		$obj = ilObjectFactory::getInstanceByObjId($obj_id);
		return !$obj->getLSSettings()->getIsOnline();
	}

	protected function isOffline($ref_id) {
		$obj = ilObjectFactory::getInstanceByRefId($ref_id);
		return !$obj->getLSSettings()->getIsOnline();
	}

	public function _checkAccess($cmd, $permission, $ref_id, $obj_id, $usr_id = "")
	{
		list ($rbacsystem, $il_access, $lng) = $this->getDICDependencies();

		switch($permission)
		{
			case 'visible':
				$has_any_administrative_permission = (
					$rbacsystem->checkAccessOfUser($usr_id, 'write', $ref_id) ||
					$rbacsystem->checkAccessOfUser($usr_id, 'edit_members', $ref_id) ||
					$rbacsystem->checkAccessOfUser($usr_id, 'edit_learning_progress', $ref_id)
				);

				$is_offine = $this->isOffline($ref_id);

				if ($is_offine && !$has_any_administrative_permission) {
					$il_access->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
					return false;
				}
				return true;

			default:
				return $rbacsystem->checkAccessOfUser($usr_id, $permission, $ref_id);
		}
	}

	protected function getDICDependencies(): array
	{
		global $DIC;
		$rbacsystem = $DIC['rbacsystem'];
		$il_access = $DIC['ilAccess'];
		$lng = $DIC['lng'];

		return [
			$rbacsystem,
			$il_access,
			$lng
		];
	}

}
