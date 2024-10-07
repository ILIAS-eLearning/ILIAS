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

namespace ILIAS\Portfolio\Settings;

use ILIAS\Portfolio\InternalDomainService;
use ILIAS\Portfolio\InternalGUIService;
use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\Portfolio\InternalDataService;

class SettingsGUI
{
    protected \ILIAS\Notes\DomainService $notes;
    protected \ilSetting $global_settings;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $obj_id,
        protected bool $in_repository,
        protected int $ref_id = 0
    ) {
        $this->notes = $domain->notes();
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
        $settings = $this->domain->portfolioSettings()->getById($this->obj_id);
        $lng = $this->domain->lng();

        $lng->loadLanguageModule("rep");

        $form = $this->gui
            ->form(self::class, "save")
            ->section("general", $lng->txt("prtf_edit_portfolio"));
        if ($this->in_repository) {
            $form = $form
                ->addStdTitleAndDescription($this->obj_id, "prtf");
        } else {
            $form = $form
                ->addStdTitle($this->obj_id, "prtf");
        }
        $form = $form
            ->section("avail", $lng->txt("rep_activation_availability"))
            ->addOnline($this->obj_id, "prtf");
        if ($this->in_repository) {
            $form = $form->addStdAvailability($this->ref_id);
        }
        $form = $form
            ->section("presentation", $lng->txt("obj_presentation"))
            ->checkbox(
                "ppic",
                $lng->txt("prtf_profile_picture"),
                "",
                $settings->getShowPersonalPicture()
            );
        if ($this->in_repository) {
            $form = $form
                ->addStdTile($this->obj_id, "prtf");
        }
        if ($this->in_repository) {
            $form = $form
                ->addAdditionalFeatures(
                    $this->obj_id,
                    [
                        \ilObjectServiceSettingsGUI::CUSTOM_METADATA
                    ]
                );
        }
        $form = $form
            ->checkbox(
                "comments",
                $lng->txt("prtf_public_comments"),
                "",
                $this->notes->commentsActive($this->obj_id)
            );

        return $form;
    }

    protected function save(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $form = $this->getEditForm();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();

        $old_settings = $this->domain->portfolioSettings()->getById($this->obj_id);

        if ($form->isValid()) {
            if ($this->in_repository) {
                $form->saveStdTitleAndDescription($this->obj_id, "prtf");
                $form->saveStdTile($this->obj_id, "mep");
                $form->saveAdditionalFeatures(
                    $this->obj_id,
                    [
                        \ilObjectServiceSettingsGUI::CUSTOM_METADATA
                    ]
                );
            } else {
                $form->saveStdTitle($this->obj_id, "prtf");
            }
            $form->saveOnline($this->obj_id, "mep");

            $this->notes->activateComments(
                $this->obj_id,
                $form->getData("comments")
            );

            $settings = $this->data->settings(
                $this->obj_id,
                (bool) $form->getData("ppic")
            );
            $this->domain->portfolioSettings()->update($settings);

            if ($this->in_repository) {
                $form->saveStdAvailability($this->ref_id);
            }

            $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
            $ctrl->redirectByClass(self::class, "edit");
        } else {
            $mt = $this->gui->ui()->mainTemplate();
            $mt->setContent($form->render());
        }
    }
}
