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

namespace ILIAS\SurveyQuestionPool\Settings;

use ILIAS\SurveyQuestionPool\InternalDomainService;
use ILIAS\SurveyQuestionPool\InternalGUIService;
use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\SurveyQuestionPool\InternalDataService;

class SettingsGUI
{
    protected \ilSetting $global_settings;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $obj_id,
        protected int $ref_id
    ) {
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();
        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("edit");

        switch ($next_class) {
            default:
                if (in_array($cmd, ["edit", "save"])) {
                    $this->$cmd();
                }
        }
    }

    protected function edit(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $form = $this->getEditForm();
        $mt->setContent($form->render());
    }

    protected function getEditForm(): FormAdapterGUI
    {
        //$settings = $this->domain->mediapoolSettings()->getById($this->obj_id);
        $lng = $this->domain->lng();

        $form = $this->gui
            ->form(self::class, "save")
            ->section("general", $lng->txt("properties"))
            ->addStdTitleAndDescription($this->obj_id, "spl")
            ->section("avail", $lng->txt("rep_activation_availability"))
            ->addOnline($this->obj_id, "spl")
            ->addStdAvailability($this->ref_id, "spl")
            ->section("presentation", $lng->txt("obj_presentation"))
            ->addStdTile($this->obj_id, "spl");
        return $form;
    }

    protected function save(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $form = $this->getEditForm();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();

        if ($form->isValid()) {

            $form->saveStdTitleAndDescription($this->obj_id, "spl");
            $form->saveStdTile($this->obj_id, "spl");
            $form->saveOnline($this->obj_id, "spl");
            $form->saveStdAvailability($this->ref_id);

            // we still need the svy_qpl record for now
            // but it may be abandoned in the future, if it does not contain new properties
            $pool = new \ilObjSurveyQuestionPool($this->ref_id);
            $pool->saveToDb();

            $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
            $ctrl->redirectByClass(self::class, "edit");
        } else {
            $mt = $this->gui->ui()->mainTemplate();
            $mt->setContent($form->render());
        }
    }
}
