<?php declare(strict_types=1);

use Pimple\Container;

/**
  * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
trait ilLSLocalDI
{
	public function getLSLocalDI(
		ilObjLearningSequence $object,
		ArrayAccess $dic
	): Container {
		$container = new Container();


		$ref_id = (int)$object->getRefId();
		$obj_id = (int)$object->getId();
		$obj_title = $object->getTitle();

		$current_user = $dic['ilUser'];
		$current_user_id = (int)$current_user->getId();

		$data_factory = new \ILIAS\Data\Factory();

		$container["obj.ref_id"] = $ref_id;
		$container["obj.obj_id"] = $obj_id;
		$container["obj.title"] = (string)$obj_title;

		$container["usr.id"] = $current_user_id;

		$container["obj.sorting"] = function($c): ilContainerSorting
		{
			return ilContainerSorting::_getInstance($c["obj.obj_id"]);
		};

		$container["db.filesystem"] = function($c): ilLearningSequenceFilesystem
		{
			 return new ilLearningSequenceFilesystem();
		};

		$container["db.settings"] = function($c) use ($dic): ilLearningSequenceSettingsDB
		{
			return new ilLearningSequenceSettingsDB(
				$dic["ilDB"],
				$c["db.filesystem"]
			);
		};

		$container["db.activation"] = function($c) use ($dic): ilLearningSequenceActivationDB
		{
			return new ilLearningSequenceActivationDB($dic["ilDB"]);
		};

		$container["db.states"] = function($c) use ($dic): ilLSStateDB
		{
			return new ilLSStateDB($dic["ilDB"]);
		};

		$container["db.postconditions"] = function($c) use ($dic): ilLSPostConditionDB
		{
			return new ilLSPostConditionDB($dic["ilDB"]);
		};

		$container["db.lsitems"] = function($c) use ($dic): ilLSItemsDB
		{
			$online_status = new LSItemOnlineStatus();
			return new ilLSItemsDB(
				$dic["tree"],
				$c["obj.sorting"],
				$c["db.postconditions"],
				$online_status
			);
		};

		$container["db.progress"] = function($c) use ($dic): ilLearnerProgressDB
		{
			 return new ilLearnerProgressDB(
				$c["db.lsitems"],
				$dic["ilAccess"]
			);
		};

		$container["learneritems"] = function($c): ilLSLearnerItemsQueries
		{
			return new ilLSLearnerItemsQueries(
				$c["db.progress"],
				$c["db.states"],
				$c["obj.ref_id"],
				$c["usr.id"]
			);
		};


		$container["gui.learner"] = function($c) use ($dic, $object): ilObjLearningSequenceLearnerGUI
		{
			$has_items = count($c["learneritems"]->getItems()) > 0;
			$first_access = $c["learneritems"]->getFirstAccess();

			return new ilObjLearningSequenceLearnerGUI(
				$c["obj.ref_id"],
				$has_items,
				$first_access,
				$c["usr.id"],
				$dic["ilAccess"],
				$dic["ilCtrl"],
				$dic["lng"],
				$dic["tpl"],
				$dic["ilToolbar"],
				$dic["ui.factory"],
				$dic["ui.renderer"],
				$c["roles"],
				$c["db.settings"]->getSettingsFor($c["obj.obj_id"]),
				$c["player.curriculumbuilder"],
				$c["player"]
			);
		};

		$container["gui.toc"] = function($c) use ($dic): ilLSTOCGUI
		{
			return new ilLSTOCGUI(
				$c["player.urlbuilder"],
				$dic["ilCtrl"]
			);
		};

		$container["gui.loc"] = function($c) use ($dic): ilLSLocatorGUI
		{
			return new ilLSLocatorGUI(
				$c["player.urlbuilder"],
				$dic["ui.factory"]
			);
		};

		$container["player.viewfactory"] = function($c) use ($dic): ilLSViewFactory
		{
			return new ilLSViewFactory(
				$dic['service.kiosk_mode'],
				$dic["lng"],
				$dic["ilAccess"]
			);
		};

		$container["player.urlbuilder"] = function($c) use ($dic, $data_factory): LSUrlBuilder
		{
			$player_base_url = $dic['ilCtrl']->getLinkTargetByClass(
				'ilObjLearningSequenceLearnerGUI',
				\ilObjLearningSequenceLearnerGUI::CMD_VIEW,
				'',	false, false
			);
			$player_base_url = $data_factory->uri(ILIAS_HTTP_PATH .'/'.$player_base_url);

			return new LSUrlBuilder($player_base_url);
		};

		$container["player.controlbuilder"] = function($c) use ($dic): LSControlBuilder
		{
			 return new LSControlBuilder(
			 	$dic["ui.factory"],
			 	$c["player.urlbuilder"],
			 	$dic["lng"]
			 );
		};

		$container["player.kioskrenderer"] = function($c) use ($dic): ilKioskPageRenderer
		{
			$kiosk_template = new ilTemplate("tpl.kioskpage.html", true, true, 'Modules/LearningSequence');
			$window_title = $dic['ilSetting']->get('short_inst_name');
			if($window_title === false) {
				$window_title = 'ILIAS';
			}

			return new ilKioskPageRenderer(
				$dic["tpl"],
				$dic->globalScreen()->layout()->meta(),
				$dic["ui.renderer"],
				$kiosk_template,
				$c["gui.toc"],
				$c["gui.loc"],
				$window_title
			);
		};

		$container["player.curriculumbuilder"] = function($c) use ($dic): ilLSCurriculumBuilder
		{
			return new ilLSCurriculumBuilder(
				$c["learneritems"],
				$dic["ui.factory"],
				$dic["lng"],
				ilLSPlayer::LSO_CMD_GOTO,
				$c["player.urlbuilder"]
			);
		};

		$container["player"] = function($c) use ($dic): ilLSPlayer
		{
			return new ilLSPlayer(
				$c["obj.title"],
				$c["learneritems"],
				$c["player.controlbuilder"],
				$c["player.urlbuilder"],
				$c["player.curriculumbuilder"],
				$c["player.viewfactory"],
				$c["player.kioskrenderer"],
				$dic["ui.factory"]
			);
		};

		$container["participants"] = function($c) use ($dic): ilLearningSequenceParticipants
		{
			return new ilLearningSequenceParticipants(
				$c["obj.obj_id"],
				$dic["ilLoggerFactory"]->getRootLogger(),
				$dic["ilAppEventHandler"],
				$dic["ilSetting"]
			);
		};

		$container["roles"] = function($c) use ($dic, $object, $current_user): ilLearningSequenceRoles
		{
			return new ilLearningSequenceRoles(
				$c["obj.ref_id"],
				$c["obj.obj_id"],
				$c["participants"],
				$dic["ilCtrl"],
				$dic["rbacadmin"],
				$dic["rbacreview"],
				$dic["ilDB"],
				$current_user
			);
		};

		return $container;
	}
}
