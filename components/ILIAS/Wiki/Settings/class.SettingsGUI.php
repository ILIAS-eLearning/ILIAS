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

namespace ILIAS\Wiki\Settings;

use ILIAS\Wiki\InternalDomainService;
use ILIAS\Wiki\InternalGUIService;
use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\Wiki\InternalDataService;

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
        $this->global_settings = $this->domain->settings();
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
        $settings = $this->domain->wikiSettings()->getById($this->obj_id);
        $start_page_options = $this->domain->wikiSettings()->getStartPageOptions($this->ref_id);
        $start_page_id = $this->domain->wikiSettings()->getStartPageId($settings);

        $lng = $this->domain->lng();
        $lng->loadLanguageModule("rating");

        $form = $this->gui
            ->form(self::class, "save")
            ->section("general", $lng->txt("wiki_settings"))
            ->addStdTitleAndDescription($this->obj_id, "wiki")
            ->textarea(
                "introduction",
                $lng->txt("wiki_introduction"),
                "",
                (string) $settings->getIntroduction()
            )
            ->select(
                "start_page",
                $lng->txt("wiki_start_page"),
                $start_page_options,
                "",
                $start_page_id ? (string) $start_page_id : null
            )->required()
            ->section("avail", $lng->txt("rep_activation_availability"))
            ->addOnline($this->obj_id, "wiki")
            ->addStdAvailability($this->ref_id)
            ->section("presentation", $lng->txt("obj_presentation"))
            ->addStdTile($this->obj_id, "wiki")
            ->checkbox(
                "page_toc",
                $lng->txt("wiki_page_toc"),
                $lng->txt("wiki_page_toc_info"),
                (bool) $settings->getPageToc()
            )
            ->addAdditionalFeatures(
                $this->obj_id,
                [
                    \ilObjectServiceSettingsGUI::CUSTOM_METADATA
                ]
            )
            ->checkbox(
                "rating_overall",
                $lng->txt("rating_activate_rating"),
                $lng->txt("rating_activate_rating_info"),
                (bool) $settings->getRatingOverall()
            )
            ->optional(
                "rating",
                $lng->txt("wiki_activate_rating"),
                "",
                (bool) $settings->getRating()
            )
            ->checkbox(
                "rating_new",
                $lng->txt("wiki_activate_new_page_rating"),
                "",
                (bool) $settings->getRatingForNewPages()
            )
            ->checkbox(
                "rating_ext",
                $lng->txt("wiki_activate_extended_rating"),
                "",
                (bool) $settings->getRatingCategories()
            )
            ->end();

        if(!$this->global_settings->get("disable_comments")) {
            $form = $form
                ->checkbox(
                    "public_notes",
                    $lng->txt("wiki_public_comments"),
                    "",
                    (bool) $settings->getPublicNotes()
                );
        }

        return $form;
    }

    protected function save(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $form = $this->getEditForm();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();

        $old_settings = $this->domain->wikiSettings()->getById($this->obj_id);

        if ($form->isValid()) {

            $form->saveStdTitleAndDescription($this->obj_id, "wiki");
            $form->saveStdTile($this->obj_id, "wiki");
            $form->saveOnline($this->obj_id, "wiki");
            $form->saveStdAvailability($this->ref_id);
            $form->saveAdditionalFeatures(
                $this->obj_id,
                [
                    \ilObjectServiceSettingsGUI::CUSTOM_METADATA
                ]
            );


            $settings = $this->data->settings(
                $this->obj_id,
                \ilWikiPage::lookupTitle((int) $form->getData("start_page")),
                $old_settings->getShortTitle(),  // obsolete?
                (bool) $form->getData("rating_overall"),
                (bool) $form->getData("rating"),
                $old_settings->getRatingAsBlock(),  // obsolete?
                (bool) $form->getData("rating_new"),
                (bool) $form->getData("rating_ext"),
                (bool) $form->getData("public_notes"),
                $form->getData("introduction"),
                (bool) $form->getData("page_toc"),
                $old_settings->getLinkMetadataValues(),  // obsolete?
                $old_settings->getEmptyPageTemplate()  // obsolete?
            );
            $this->domain->wikiSettings()->update($settings);

            $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
            $ctrl->redirectByClass(self::class, "edit");
        } else {
            $mt = $this->gui->ui()->mainTemplate();
            $mt->setContent($form->render());
        }
    }
}
