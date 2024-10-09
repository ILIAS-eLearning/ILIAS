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

namespace ILIAS\Glossary\Settings;

use ILIAS\Glossary\InternalDomainService;
use ILIAS\Glossary\InternalGUIService;
use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\Glossary\InternalDataService;

class SettingsGUI
{
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
        $lng = $this->domain->lng();
        $settings = $this->domain->glossarySettings()->getByObjId($this->obj_id);

        /* todo
        if (!empty($this->object->getGlossariesForCollection()) && $this->object->isVirtual()) {
            $glo_mode_group_1 = $glo_mode_group_1->disabled();
            $glo_mode_group_2 = $glo_mode_group_2->disabled();
            $form = $form->addInfo($lng->txt("glo_change_to_standard_unavailable_info"));
        }

        if (!empty(ilGlossaryTerm::getTermsOfGlossary($this->object->getId())) && !$this->object->isVirtual()) {
            $glo_mode_group_1 = $glo_mode_group_1->disabled();
            $glo_mode_group_2 = $glo_mode_group_2->disabled();
            $form = $form->addInfo($lng->txt("glo_change_to_collection_unavailable_info"));
        }*/

        $form = $this->gui
            ->form(self::class, "save")
            ->section("general", $lng->txt("cont_glo_properties"))
            ->addStdTitleAndDescription(
                $this->obj_id,
                "glo"
            )
            ->radio(
                "glo_mode",
                $lng->txt("glo_content_assembly"),
                $lng->txt("glo_mode_desc"),
                $settings->getVirtualMode()
            )
            ->radioOption(
                "none",
                $lng->txt("glo_mode_normal"),
                $lng->txt("glo_mode_normal_info")
            )->radioOption(
                "coll",
                $lng->txt("glo_collection"),
                $lng->txt("glo_collection_info")
            )
            ->section("avail", $lng->txt('rep_activation_availability'))
            ->addOnline($this->obj_id, "glo")
            ->addStdAvailability($this->ref_id, "glo")
            ->section("presentation", $lng->txt('cont_presentation'))
            ->addStdTile($this->obj_id, "glo")
            ->switch(
                "pres_mode",
                $lng->txt("glo_presentation_mode"),
                "",
                $settings->getPresentationMode()
            )
            ->group(
                "table",
                $lng->txt("glo_table_form"),
                $lng->txt("glo_table_form_info")
            )
            ->number(
                "snippet_length",
                $lng->txt("glo_text_snippet_length"),
                $lng->txt("characters") . " - " . $lng->txt("glo_text_snippet_length_info"),
                $settings->getSnippetLength(),
                100,
                300
            )
            ->group(
                "full_def",
                $lng->txt("glo_full_definitions"),
                $lng->txt("glo_full_definitions_info")
            )
            ->end()
            ->checkbox(
                "flash_active",
                $lng->txt("glo_flashcard_training"),
                $lng->txt("glo_flashcard_training_info"),
                $settings->getActiveFlashcards()
            )
            ->radio(
                "flash_mode",
                $lng->txt("glo_mode"),
                "",
                $settings->getFlashcardsMode()
            )
            ->radioOption("term", $lng->txt("glo_term_vs_def"), $lng->txt("glo_term_vs_def_info"))
            ->radioOption("def", $lng->txt("glo_def_vs_term"), $lng->txt("glo_def_vs_term_info"));

        $form = $form->addAdditionalFeatures(
            $this->obj_id,
            [
                \ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                \ilObjectServiceSettingsGUI::TAXONOMIES
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

        $old_settings = $this->domain->glossarySettings()->getByObjId($this->obj_id);

        if ($form->isValid()) {
            $form->saveStdTitleAndDescription(
                $this->obj_id,
                "glo"
            );
            $form->saveStdTile(
                $this->obj_id,
                "glo"
            );
            $form->saveOnline(
                $this->obj_id,
                "glo"
            );
            $form->saveStdAvailability(
                $this->ref_id
            );
            $form->saveAdditionalFeatures(
                $this->obj_id,
                [
                    \ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                    \ilObjectServiceSettingsGUI::TAXONOMIES
                ]
            );

            $settings = $this->data->settings(
                $this->obj_id,
                $form->getData("glo_mode"),
                $old_settings->getActiveGlossaryMenu(),      // obsolete?
                $form->getData("pres_mode"),
                $old_settings->getShowTaxonomy(),
                (int) $form->getData("snippet_length"),
                (bool) $form->getData("flash_active"),
                $form->getData("flash_mode")
            );

            $this->domain->glossarySettings()->update($settings);

            $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
            $ctrl->redirectByClass(self::class, "edit");
        } else {
            $mt = $this->gui->ui()->mainTemplate();
            $mt->setContent($form->render());
        }
    }
}
