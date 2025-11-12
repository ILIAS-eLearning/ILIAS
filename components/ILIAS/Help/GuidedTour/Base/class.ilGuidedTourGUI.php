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

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;
use ILIAS\Services\Help\ScreenId\HelpScreenIdObserver;
use ILIAS\Help\GuidedTour\Settings\PermissionType;

/**
 * @ilCtrl_Calls ilGuidedTourGUI: ilGuidedTourPageGUI
 */
class ilGuidedTourGUI implements ilCtrlBaseClassInterface
{
    protected ilObjectDefinition $obj_definition;
    protected ilAccessHandler $access;
    protected ilObjUser $user;
    protected \ILIAS\Help\GuidedTour\UserFinished\UserFinishedManager $finish_manager;
    protected string $current_screen_id;
    protected \ILIAS\Help\GuidedTour\Settings\SettingsManager $settings_manager;
    protected \ILIAS\Help\GuidedTour\StandardGUIRequest $request;
    protected \ILIAS\Help\GuidedTour\Page\PageManager $page_manager;
    protected \ILIAS\Help\GuidedTour\Tour\TourManager $tour_manager;
    protected \ILIAS\Help\GuidedTour\Step\StepManager $step_manager;
    protected \ILIAS\Help\InternalGUIService $gui;
    protected \ILIAS\Help\InternalService $help;

    public function __construct()
    {
        global $DIC;
        $this->current_screen_id = $DIC->help()->getScreenId();
        $this->help = $DIC->help()->internal();
        $this->gui = $this->help->gui();
        $this->user = $this->help->domain()->user();
        $this->tour_manager = $this->help->domain()->guidedTour()->tour();
        $this->step_manager = $this->help->domain()->guidedTour()->step();
        $this->page_manager = $this->help->domain()->guidedTour()->page();
        $this->settings_manager = $this->help->domain()->guidedTour()->tourSettings();
        $this->finish_manager = $this->help->domain()->guidedTour()->userFinished();
        $this->request = $this->gui->guidedTour()->standardRequest();
        $this->access = $DIC->access();
        $this->obj_definition = $this->help->domain()->objectDefinition();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        switch ($next_class) {
            default:
                if (in_array($cmd, [
                    "getData",
                    "showStep",
                    "finishTour"
                ])) {
                    $this->$cmd();
                }
        }
    }

    public function init(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $ctrl = $this->gui->ctrl();

        if (!$this->tour_manager->anyActive()) {
            return;
        }

        // ensure popover js being loaded
        $r->render($f->popover()->standard($f->legacy()->content('')));

        $debug = false;
        $mt->addJavaScript("assets/js/repository.js");
        if ($debug) {
            $mt->addJavaScript("../components/ILIAS/Help/resources/guided-tour.js");
        } else {
            $mt->addJavaScript("assets/js/guided-tour.js");
        }
        $ctrl->setParameterByClass(self::class, "screen_id", rawurlencode($this->current_screen_id));
        $ctrl->setParameterByClass(self::class, "ref_id", (string) $this->request->getRefId());
        $target = $ctrl->getLinkTargetByClass(self::class, "", "", true);
        $mt->addOnloadCode("il.guidedTour.init('$target');");
    }

    public function registerTabLink(string $tab_id, \ILIAS\UI\Component\Link\Standard $link): \ILIAS\UI\Component\Link\Standard
    {
        if (!$this->tour_manager->anyActive()) {
            return $link;
        }
        return $link->withAdditionalOnLoadCode(static function (string $id) use ($tab_id): string {
            return "il.guidedTour.addMapping('$tab_id', '$id');";
        });
    }

    public function getData(): void
    {
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $ctrl = $this->gui->ctrl();
        $popover = $f->popover()->standard($f->legacy()->content(''));
        $current_screen_id = $this->help->gui()->standardRequest()->getScreenId();
        $data = new \stdClass();
        $popoverHtml = $r->renderAsync($popover);
        // current workaround to not
        $popoverHtml = str_replace("JSON.parse('{", "JSON.parse('{\"trigger\":\"manual\",", $popoverHtml);
        $data->popoverHtml = $popoverHtml;
        $data->popoverShowSignal = $popover->getShowSignal()->getId();
        $data->tour = [];
        $ref_id = $this->request->getRefId();
        foreach ($this->tour_manager->getAll() as $tour) {
            $settings = $this->settings_manager->getByObjId($tour->getId());
            // check active
            if (!$settings?->isActive()) {
                continue;
            }

            // check finished
            if ($this->finish_manager->hasFinished($tour->getId(), $this->user->getId())) {
                continue;
            }

            // check screen id
            $screen_ids = $settings?->getScreenIds();
            $found = true;
            if (trim($screen_ids) !== "") {
                $found = false;
                foreach (explode(",", $screen_ids) as $screen_id) {
                    if (trim($screen_id) === $current_screen_id) {
                        $found = true;
                    }
                }
            }
            if (!$found) {
                continue;
            }
            // check language
            if ($settings->getLanguage() !== "") {
                if ($this->user->getLanguage() !== $settings->getLanguage()) {
                    continue;
                }
            }
            // check permission
            if ($ref_id > 0) {
                if ($settings->getPermission() !== PermissionType::None) {
                    switch ($settings->getPermission()) {
                        case PermissionType::Read:
                            if (!$this->access->checkAccess("read", "", $ref_id)) {
                                continue 2;
                            }
                            break;
                        case PermissionType::Write:
                            if (!$this->access->checkAccess("write", "", $ref_id)) {
                                continue 2;
                            }
                            break;
                        case PermissionType::Create:
                            $current_type = ilObject::_lookupType($ref_id, true);
                            $subtypes = $this->obj_definition->getCreatableSubObjects(
                                $current_type,
                                ilObjectDefinition::MODE_REPOSITORY,
                                $ref_id
                            );
                            $can_create = false;
                            foreach ($subtypes as $key => $value) {
                                if (!$can_create && $this->access->checkAccess('create_' . $key, '', $ref_id, $current_type)) {
                                    $can_create = true;
                                }
                            }
                            if (!$can_create) {
                                continue 2;
                            }
                            break;
                    }
                }
            }

            $ctrl->setParameterByClass(self::class, "tour_id", $tour->getId());
            $data->tour[$tour->getId()] = [
                "name" => $tour->getTitle(),
                "finishUrl" => $ctrl->getLinkTargetByClass(ilGuidedTourPageGUI::class, "finishTour"),
            ];
            foreach ($this->step_manager->getStepsOfTour($tour->getId()) as $step) {
                $step_id = $step->getId();
                $ctrl->setParameterByClass(self::class, "step_id", $step_id);
                $data->tour[$tour->getId()]["steps"]["step_" . $step_id] = [
                    "id" => $step_id,
                    "type" => $step->getType(),
                    "elementId" => $step->getElementId(),
                    "url" => $ctrl->getLinkTargetByClass(ilGuidedTourPageGUI::class, "showStep"),
                ];
            }
        }
        $this->gui->httpUtil()->sendJson($data);
    }

    protected function showStep(): void
    {
        $tour_id = $this->request->getTourId();
        $step_id = $this->request->getStepId();
        $this->page_manager->printPage($step_id);
    }

    protected function finishTour(): void
    {
        $tour_id = $this->request->getTourId();
        $this->finish_manager->setFinished($tour_id, $this->user->getId());
        $data = new \stdClass();
        $data->finished = true;
        $this->gui->httpUtil()->sendJson($data);
    }
}
