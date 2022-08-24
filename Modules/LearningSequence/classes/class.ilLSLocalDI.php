<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use Pimple\Container;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;

/**
 * @author   Nils Haagen <nils.haagen@concepts-and-training.de>
 */

class ilLSLocalDI extends Container
{
    public function init(
        ArrayAccess $dic,
        ArrayAccess $lsdic,
        DataFactory $data_factory,
        ilObjLearningSequence $object
    ): void {
        $obj_id = (int) $object->getId();
//        $obj_title = $object->getTitle();

        $current_user = $dic['ilUser'];
        $current_user_id = (int) $current_user->getId();

        $this["obj.ref_id"] = function ($c) use ($object): int {
            $ref = $object->getRefId();
            if (!$ref) {
                $refs = ilObject::_getAllReferences($object->getId());
                $ref = array_shift($refs);
            }
            return (int) $ref;
        };

        $this["obj.obj_id"] = $obj_id;
//        $this["obj.title"] = (string) $obj_title;

        $this["usr.id"] = $current_user_id;

        $this["obj.sorting"] = function ($c): ilContainerSorting {
            return ilContainerSorting::_getInstance($c["obj.obj_id"]);
        };

        $this["db.lsitems"] = function ($c) use ($dic, $lsdic): ilLSItemsDB {
            $online_status = new LSItemOnlineStatus();
            return new ilLSItemsDB(
                $dic["tree"],
                $c["obj.sorting"],
                $lsdic["db.postconditions"],
                $online_status
            );
        };

        $this["db.progress"] = function ($c) use ($dic): ilLearnerProgressDB {
            return new ilLearnerProgressDB(
                $c["db.lsitems"],
                $dic["ilAccess"],
                $dic['ilObjDataCache']
            );
        };

        $this["learneritems"] = function ($c) use ($dic, $lsdic): ilLSLearnerItemsQueries {
            return new ilLSLearnerItemsQueries(
                $c["db.progress"],
                $lsdic["db.states"],
                $c["obj.ref_id"],
                $c["usr.id"]
            );
        };


        $this["get.params"] = function ($c) use ($dic): ArrayBasedRequestWrapper {
            return $dic["http"]->wrapper()->query();
        };


        $this["gui.learner"] = function ($c) use ($dic, $lsdic, $object): ilObjLearningSequenceLearnerGUI {
            $intro = $object->getContentPageHTML($object::CP_INTRO);
            $extro = $object->getContentPageHTML($object::CP_EXTRO);

            return new ilObjLearningSequenceLearnerGUI(
                $c["usr.id"],
                $dic["ilAccess"],
                $dic["ilCtrl"],
                $dic["lng"],
                $dic["tpl"],
                $dic["ilToolbar"],
                $dic["ui.factory"],
                $dic["ui.renderer"],
                $c["roles"],
                $lsdic["db.settings"]->getSettingsFor($c["obj.obj_id"]),
                $c["player.curriculumbuilder"],
                $c["player.launchlinksbuilder"],
                $c["player"],
                $intro,
                $extro,
                $c["get.params"]
            );
        };

        $this["player.urlbuilder.lp"] = function ($c) use ($dic, $data_factory): LSUrlBuilder {
            $player_base_url = $dic['ilCtrl']->getLinkTargetByClass(
                'ilObjLearningSequenceLPPollingGUI',
                \LSControlBuilder::CMD_CHECK_CURRENT_ITEM_LP,
                '',
                false,
                false
            );
            $player_base_url = $data_factory->uri(ILIAS_HTTP_PATH . '/' . $player_base_url);

            return new LSUrlBuilder($player_base_url);
        };
        $this["gui.learner.lp"] = function ($c) use ($dic): ilObjLearningSequenceLPPollingGUI {
            return new ilObjLearningSequenceLPPollingGUI(
                $dic["ilCtrl"],
                $c["usr.id"],
                $dic['ilObjDataCache'],
                $dic->refinery(),
                $dic->http()->wrapper()->query()
            );
        };

        $this["gui.toc"] = function ($c) use ($dic): ilLSTOCGUI {
            return new ilLSTOCGUI(
                $c["player.urlbuilder"],
                $dic["ilCtrl"]
            );
        };

        $this["gui.loc"] = function ($c) use ($dic): ilLSLocatorGUI {
            return new ilLSLocatorGUI(
                $c["player.urlbuilder"],
                $dic["ui.factory"]
            );
        };

        $this["player.viewfactory"] = function ($c) use ($dic): ilLSViewFactory {
            return new ilLSViewFactory(
                $dic['service.kiosk_mode'],
                $dic["lng"],
                $dic["ilAccess"]
            );
        };

        $this["player.urlbuilder"] = function ($c) use ($dic, $data_factory): LSUrlBuilder {
            $player_base_url = $dic['ilCtrl']->getLinkTargetByClass(
                'ilObjLearningSequenceLearnerGUI',
                \ilObjLearningSequenceLearnerGUI::CMD_VIEW,
                '',
                false,
                false
            );
            $player_base_url = $data_factory->uri(ILIAS_HTTP_PATH . '/' . $player_base_url);

            return new LSUrlBuilder($player_base_url);
        };

        $this["globalsetttings"] = function ($c) use ($dic): \LSGlobalSettings {
            $db = new ilLSGlobalSettingsDB($dic['ilSetting']);
            return $db->getSettings();
        };

        $this["player.controlbuilder"] = function ($c) use ($dic): LSControlBuilder {
            return new LSControlBuilder(
                $dic["ui.factory"],
                $c["player.urlbuilder"],
                $dic["lng"],
                $c["globalsetttings"],
                $c["player.urlbuilder.lp"]
            );
        };

        $this["player.kioskrenderer"] = function ($c) use ($dic): ilKioskPageRenderer {
            $kiosk_template = new ilTemplate("tpl.kioskpage.html", true, true, 'Modules/LearningSequence');
            $window_title = $dic['ilSetting']->get('short_inst_name', 'ILIAS');

            return new ilKioskPageRenderer(
                $dic["tpl"],
                $dic["global_screen"]->layout()->meta(),
                $dic["ui.factory"],
                $dic["ui.renderer"],
                $dic['lng'],
                $kiosk_template,
                $c["gui.toc"],
                $c["gui.loc"],
                $window_title
            );
        };

        $this["player.curriculumbuilder"] = function ($c) use ($dic): ilLSCurriculumBuilder {
            return new ilLSCurriculumBuilder(
                $c["learneritems"],
                $dic["ui.factory"],
                $dic["lng"],
                ilLSPlayer::LSO_CMD_GOTO,
                $c["player.urlbuilder"]
            );
        };

        $this["player.launchlinksbuilder"] = function ($c) use ($dic): ilLSLaunchlinksBuilder {
            $first_access = $c["learneritems"]->getFirstAccess();

            return new ilLSLaunchlinksBuilder(
                $dic["lng"],
                $dic["ilAccess"],
                $dic["ilCtrl"],
                $dic["ui.factory"],
                $c["obj.ref_id"],
                $c["usr.id"],
                $first_access,
                $c["roles"]
            );
        };

        $this["player"] = function ($c) use ($dic, $lsdic): ilLSPlayer {
            return new ilLSPlayer(
                $c["learneritems"],
                $c["player.controlbuilder"],
                $c["player.urlbuilder"],
                $c["player.curriculumbuilder"],
                $c["player.viewfactory"],
                $c["player.kioskrenderer"],
                $dic["ui.factory"],
                $lsdic["gs.current_context"],
                $dic["refinery"]
            );
        };

        $this["participants"] = function ($c) use ($dic): ilLearningSequenceParticipants {
            return new ilLearningSequenceParticipants(
                $c["obj.obj_id"],
                $dic["ilLoggerFactory"]->getRootLogger(),
                $dic["ilAppEventHandler"],
                $dic["ilSetting"]
            );
        };

        $this["roles"] = function ($c) use ($dic, $current_user): ilLearningSequenceRoles {
            return new ilLearningSequenceRoles(
                $c["obj.ref_id"],
                $c["obj.obj_id"],
                $c["participants"],
                $dic["ilCtrl"],
                $dic["rbacadmin"],
                $dic["rbacreview"],
                $dic["ilDB"],
                $current_user,
                $dic['lng']
            );
        };

        $this["ui.renderer"] = function ($c) use ($dic): ILIAS\UI\Renderer {
            return $dic['ui.renderer'];
        };
    }
}
