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

namespace ILIAS\MediaPool\Settings;

use ILIAS\MediaPool\InternalDomainService;
use ILIAS\MediaPool\InternalGUIService;
use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\MediaPool\InternalDataService;

class SettingsGUI
{
    protected \ilSetting $global_settings;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $obj_id
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
        $settings = $this->domain->mediapoolSettings()->getById($this->obj_id);
        $lng = $this->domain->lng();

        $form = $this->gui
            ->form(self::class, "save")
            ->section("general", $lng->txt("mep_edit"))
            ->addStdTitleAndDescription($this->obj_id, "mep")
            ->section("avail", $lng->txt("rep_activation_availability"))
            ->addOnline($this->obj_id, "mep")
            ->section("presentation", $lng->txt("obj_presentation"))
            ->addStdTile($this->obj_id, "mep")
            ->number(
                "default_width",
                $lng->txt("mep_default_width"),
                "",
                $settings->getDefaultWidth(),
                0
            )
            ->number(
                "default_height",
                $lng->txt("mep_default_height"),
                $lng->txt("mep_default_width_height_info"),
                $settings->getDefaultHeight(),
                0
            )
            ->addAdditionalFeatures(
                $this->obj_id,
                [
                    \ilObjectServiceSettingsGUI::CUSTOM_METADATA
                ]
            );

        return $form;
    }

    protected function save(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $form = $this->getEditForm();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();

        $old_settings = $this->domain->mediapoolSettings()->getById($this->obj_id);

        if ($form->isValid()) {

            $form->saveStdTitleAndDescription($this->obj_id, "mep");
            $form->saveStdTile($this->obj_id, "mep");
            $form->saveOnline($this->obj_id, "mep");
            $form->saveAdditionalFeatures(
                $this->obj_id,
                [
                    \ilObjectServiceSettingsGUI::CUSTOM_METADATA
                ]
            );


            $settings = $this->data->settings(
                $this->obj_id,
                (int) $form->getData("default_width"),
                (int) $form->getData("default_height"),
                $old_settings->getForTranslation()
            );
            $this->domain->mediapoolSettings()->update($settings);

            $news_set = new \ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");

            if ($enable_internal_rss) {
                \ilBlockSetting::_write(
                    "news",
                    "public_feed",
                    (string) $form->getData("extra_feed"),
                    0,
                    $this->obj_id
                );

                \ilBlockSetting::_write(
                    "news",
                    "keep_rss_min",
                    (string) $form->getData("keep_rss_min"),
                    0,
                    $this->obj_id
                );
            }

            $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
            $ctrl->redirectByClass(self::class, "edit");
        } else {
            $mt = $this->gui->ui()->mainTemplate();
            $mt->setContent($form->render());
        }
    }
}
