<?php declare(strict_types = 1);

use Pimple\Container;

class ilStudyProgrammeDIC
{
	public static $dic;
	public static function dic() : Container
	{
		if(!self::$dic) {
			self::$dic = self::buildDIC();
		}
		return self::$dic;
	}

	protected static function buildDIC() : Container
	{
		global $DIC;

		$dic = new Container();

		$dic['ilStudyProgrammeEvents'] = function($dic) use ($DIC) {
			return new ilStudyProgrammeEvents(
				$DIC['ilAppEventHandler'],
				$dic['model.Assignment.ilStudyProgrammeAssignmentRepository']
			);
		};
		$dic['model.Settings.ilStudyProgrammeSettingsRepository'] = function($dic) use ($DIC) {
			return new ilStudyProgrammeSettingsDBRepository($DIC['ilDB']);
		};
		$dic['model.Progress.ilStudyProgrammeProgressRepository'] = function($dic) use ($DIC) {
			return new ilStudyProgrammeProgressDBRepository($DIC['ilDB']);
		};
		$dic['model.Assignment.ilStudyProgrammeAssignmentRepository'] = function($dic) use ($DIC) {
			return new ilStudyProgrammeAssignmentDBRepository($DIC['ilDB']);
		};
		$dic['model.Type.ilStudyProgrammeTypeRepository'] = function($dic) use ($DIC) {
			return new ilStudyProgrammeTypeDBRepository(
				$DIC['ilDB'],
				$dic['model.Settings.ilStudyProgrammeSettingsRepository'],
				$DIC->filesystem()->web(),
				$DIC['ilUser'],
				$DIC['ilPluginAdmin'],
				$DIC['lng']
			);
		};
		$dic['ilObjStudyProgrammeSettingsGUI'] = function($dic) use ($DIC) {
			return new ilObjStudyProgrammeSettingsGUI(
				$DIC['tpl'],
				$DIC['ilCtrl'],
				$DIC['lng'],
				$DIC->ui()->factory()->input(),
				$DIC->ui()->renderer(),
				$DIC->http()->request(),
				$dic['TransformationFactory'],
				$dic['ValidationFactory'],
				$dic['DataFactory'],
				$dic['model.Type.ilStudyProgrammeTypeRepository']
			);
		};
		$dic['ilObjStudyProgrammeMembersGUI'] = function($dic) use ($DIC) {
			return new ilObjStudyProgrammeMembersGUI(
				$DIC['tpl'],
				$DIC['ilCtrl'],
				$DIC['ilToolbar'],
				$DIC['lng'],
				$DIC['ilUser'],
				$dic['ilStudyProgrammeUserProgressDB'],
				$dic['ilStudyProgrammeUserAssignmentDB'],
				$dic['ilStudyProgrammeRepositorySearchGUI'],
				$dic['ilObjStudyProgrammeIndividualPlanGUI']
			);
		};
		$dic['ilObjStudyProgrammeTreeGUI'] = function($dic) use ($DIC) {
			return new ilObjStudyProgrammeTreeGUI(
				$DIC['tpl'],
				$DIC['ilCtrl'],
				$DIC['ilAccess'],
				$DIC['ilToolbar'],
				$DIC['lng'],
				$DIC['ilLog'],
				$DIC['ilias'],
				$DIC['ilSetting']
			);
		};
		$dic['ilStudyProgrammeTypeGUI'] = function($dic) use ($DIC) {
			return new ilStudyProgrammeTypeGUI(
				$DIC['tpl'],
				$DIC['ilCtrl'],
				$DIC['ilAccess'],
				$DIC['ilToolbar'],
				$DIC['lng'],
				$DIC['ilias'],
				$DIC['ilTabs'],
				$dic['model.Type.ilStudyProgrammeTypeRepository']
			);
		};
		$dic['ilStudyProgrammeRepositorySearchGUI'] = function($dic) {
			return new ilStudyProgrammeRepositorySearchGUI();
		};
		$dic['ilObjStudyProgrammeIndividualPlanGUI'] = function($dic) use ($DIC) {
			return new ilObjStudyProgrammeIndividualPlanGUI(
				$DIC['tpl'],
				$DIC['ilCtrl'],
				$DIC['lng'],
				$DIC['ilUser'],
				$dic['ilStudyProgrammeUserProgressDB'],
				$dic['ilStudyProgrammeUserAssignmentDB']
			);
		};
		$dic['ilObjStudyProgrammeAutoCategoriesGUI'] = function($dic) use ($DIC) {
			return new ilObjStudyProgrammeAutoCategoriesGUI(
				$DIC['tpl'],
				$DIC['ilCtrl'],
				$DIC['ilToolbar'],
				$DIC['lng']
			);
		};
		$dic['TransformationFactory'] = function($dic) use ($DIC) {
			return new \ILIAS\Transformation\Factory();
		};
		$dic['DataFactory'] = function($dic) use ($DIC) {
			return new \ILIAS\Data\Factory();
		};
		$dic['ValidationFactory'] = function($dic) use ($DIC) {
			return new \ILIAS\Validation\Factory($dic['DataFactory'], $DIC['lng']);
		};
		$dic['ilStudyProgrammeUserProgressDB'] = function($dic) use ($DIC) {
			return new ilStudyProgrammeUserProgressDB(
				$dic['model.Progress.ilStudyProgrammeProgressRepository'],
				$dic['model.Assignment.ilStudyProgrammeAssignmentRepository'],
				$DIC['lng'],
				$dic['ilStudyProgrammeEvents']
			);
		};
		$dic['ilStudyProgrammeUserAssignmentDB'] = function($dic) use ($DIC) {
			return new ilStudyProgrammeUserAssignmentDB(
				$dic['ilStudyProgrammeUserProgressDB'],
				$dic['model.Assignment.ilStudyProgrammeAssignmentRepository'],
				$dic['model.Progress.ilStudyProgrammeProgressRepository'],
				$DIC['tree'],
				$DIC['ilLog']
			);
		};
		return $dic;
	}

}