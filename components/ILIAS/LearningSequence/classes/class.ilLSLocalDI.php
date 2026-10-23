<?php

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

declare(strict_types=1);

use Pimple\Container;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\LearningSequence\Player\LinearNavigator;
use ILIAS\LearningSequence\Player\AdaptiveNavigator;
use ILIAS\LearningSequence\LearningMap\LSOLearningMapPosition;
use ILIAS\LearningSequence\LearningMap\LSOLearningMapDataBuilder;
use ILIAS\LearningSequence\LearningMap\LSOLearningMapSequentialNavigator;
use ILIAS\LearningSequence\LearningMap\LSOLearningMapSequentialPosition;
use ILIAS\LearningSequence\Player\LSChoicePageBuilder;

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
            $intro = $object->getContentPageHTML(LSOPageType::INTRO);
            $extro = $object->getContentPageHTML(LSOPageType::EXTRO);

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
                $c["player.launchlinksbuilder"],
                $c["learneritems"],
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
                $dic["ilAccess"],
                $dic['learning_object_metadata']
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
            $kiosk_template = new ilTemplate("tpl.kioskpage.html", true, true, 'components/ILIAS/LearningSequence');
            $window_title = $dic['ilSetting']->get('short_inst_name', 'ILIAS');

            return new ilKioskPageRenderer(
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
                $c["roles"],
                $c["learneritems"]
            );
        };

        $this["player"] = function ($c) use ($dic, $lsdic, $object): ilLSPlayer {
            $mode = $lsdic["db.settings"]->getSettingsFor($c["obj.obj_id"])->getMode();
            $navigator = ($mode === ilLearningSequenceSettings::MODE_ADAPTIVE)
                ? new AdaptiveNavigator($lsdic["conditions.factory"])
                : new LinearNavigator();

            $item_path = $lsdic["db.item_path"];
            $boundaries = $lsdic["db.boundaries"];

            // Position ("where is the learner in the LSO") and the reusable
            // choice-page template are injected so that both can be reused
            // (e.g. by the upcoming map view) independently of the player.
            $position = new LSOLearningMapPosition(
                $navigator,
                $item_path,
                $boundaries,
                $c["obj.obj_id"],
                $c["usr.id"],
                $dic["ilDB"]
            );
            $choice_page_builder = new LSChoicePageBuilder(
                $dic["ui.factory"],
                $dic["lng"],
                $c["player.urlbuilder"],
                ilLSPlayer::LSO_CMD_GOTO
            );

            // Resolved here, before the player renders the current object: the
            // kiosk view of an ILIAS learning module changes the ilCtrl context,
            // and the player url of the map could not be built afterwards.
            $learning_map_data_builder = $c["learning_map.data_builder"];
            $learning_map = static fn(): string
                => $object->renderLearningMap($learning_map_data_builder, false);

            return new ilLSPlayer(
                $c["learneritems"],
                $c["player.controlbuilder"],
                $c["player.urlbuilder"],
                $c["player.viewfactory"],
                $c["player.kioskrenderer"],
                $dic["ui.factory"],
                $lsdic["gs.current_context"],
                $dic["refinery"],
                $dic["lng"],
                $dic["ilCtrl"],
                $navigator,
                $mode,
                $item_path,
                $boundaries,
                $c["obj.obj_id"],
                $c["usr.id"],
                $position,
                $choice_page_builder,
                $learning_map
            );
        };

        // Learning map data layer: assembles the object graph plus per-learner state
        // (LSOLearningMap/LSOLearningMapNode) reusing the position/navigator of the
        // respective operation mode. Adaptive: condition graph (AdaptiveNavigator plus
        // LSOLearningMapPosition with the configured start/end object). Sequential: the
        // plain chain in the configured order (LSOLearningMapSequentialNavigator plus
        // LSOLearningMapSequentialPosition, where first/last item are start/end).
        $this["learning_map.data_builder"] = function ($c) use ($dic, $lsdic): LSOLearningMapDataBuilder {
            $is_adaptive = $lsdic["db.settings"]->getSettingsFor($c["obj.obj_id"])->getMode()
                === ilLearningSequenceSettings::MODE_ADAPTIVE;

            $navigator = $is_adaptive
                ? new AdaptiveNavigator($lsdic["conditions.factory"])
                : new LSOLearningMapSequentialNavigator();
            $item_path = $lsdic["db.item_path"];
            $boundaries = $lsdic["db.boundaries"];

            $lso_obj_id = $c["obj.obj_id"];
            $ref_id = $c["obj.ref_id"];
            $db = $dic["ilDB"];
            $progress_db = $c["db.progress"];

            // Position and learner items are user-specific, so we hand the
            // builder two factories keyed by user_id instead of ready-made
            // objects. This way build($mode, $usr_id) can produce the map of any
            // learner (e.g. a future tutor view over all participants), while
            // the default (current) user stays $c["usr.id"].
            $position_factory = static function (int $usr_id) use ($is_adaptive, $navigator, $item_path, $boundaries, $lso_obj_id, $db): LSOLearningMapPosition {
                // The sequential mode derives start and end from the order of
                // the items, but still needs the visit log to determine when an
                // "always" post-condition has actually been fulfilled.
                if (!$is_adaptive) {
                    return new LSOLearningMapSequentialPosition(
                        $navigator,
                        $item_path,
                        $boundaries,
                        $lso_obj_id,
                        $usr_id,
                        $db
                    );
                }
                return new LSOLearningMapPosition(
                    $navigator,
                    $item_path,
                    $boundaries,
                    $lso_obj_id,
                    $usr_id,
                    $db
                );
            };
            // In the sequential mode the map is the chain of the objects in their
            // configured order, so the items are sorted by their order number.
            $items_factory = static function (int $usr_id) use ($is_adaptive, $progress_db, $ref_id): array {
                $items = $progress_db->getLearnerItems($usr_id, $ref_id);
                if (!$is_adaptive) {
                    usort(
                        $items,
                        static fn(LSLearnerItem $a, LSLearnerItem $b): int
                            => $a->getOrderNumber() <=> $b->getOrderNumber()
                    );
                }
                return $items;
            };

            return new LSOLearningMapDataBuilder(
                $navigator,
                $c["player.urlbuilder"],
                // the map addresses one specific object, which is not
                // necessarily a successor of the object worked on last
                ilLSPlayer::LSO_CMD_JUMP,
                $lso_obj_id,
                $c["usr.id"],
                $position_factory,
                $items_factory,
                !$is_adaptive
            );
        };

        $this["participants"] = function ($c) use ($dic): ilLearningSequenceParticipants {
            return new ilLearningSequenceParticipants(
                $c["obj.obj_id"],
                $dic["ilLoggerFactory"]->getComponentLogger('lso'),
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
