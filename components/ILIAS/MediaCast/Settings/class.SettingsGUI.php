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

namespace ILIAS\MediaCast\Settings;

use ILIAS\MediaCast\InternalDomainService;
use ILIAS\MediaCast\InternalGUIService;
use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\MediaCast\InternalDataService;

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
        $settings = $this->domain->mediacastSettings()->getById($this->obj_id);
        $lng = $this->domain->lng();

        $form = $this->gui
            ->form(self::class, "save")
            ->section("general", $lng->txt("mcst_settings"))
            ->addStdTitleAndDescription($this->obj_id, "mcst")
            ->section("avail", $lng->txt("rep_activation_availability"))
            ->addOnline($this->obj_id, "mcst")
            ->section("presentation", $lng->txt("obj_presentation"))
            ->addStdTile($this->obj_id, "mcst")
            ->switch(
                "order",
                $lng->txt("mcst_ordering"),
                "",
                (string) $settings->getSortMode()
            )
            ->group((string) \ilObjMediaCast::ORDER_TITLE, $lng->txt("mcst_ordering_title"))
            ->group((string) \ilObjMediaCast::ORDER_CREATION_DATE_ASC, $lng->txt("mcst_ordering_creation_date_asc"))
            ->group((string) \ilObjMediaCast::ORDER_CREATION_DATE_DESC, $lng->txt("mcst_ordering_creation_date_desc"))
            ->group((string) \ilObjMediaCast::ORDER_MANUAL, $lng->txt("mcst_ordering_manual"))
            ->end()
            ->switch(
                "viewmode",
                $lng->txt("mcst_viewmode"),
                "",
                $settings->getViewMode()
            )
            ->group(\ilObjMediaCast::VIEW_IMG_GALLERY, $lng->txt("mcst_img_gallery"))
            ->group(\ilObjMediaCast::VIEW_PODCAST, $lng->txt("mcst_podcast"))
            ->group(\ilObjMediaCast::VIEW_VCAST, $lng->txt("mcst_video_cast"))
            ->select(
                "autoplaymode",
                $lng->txt("mcst_autoplay"),
                [
                    \ilObjMediaCast::AUTOPLAY_NO => $lng->txt("mcst_no_autoplay"),
                    \ilObjMediaCast::AUTOPLAY_ACT => $lng->txt("mcst_autoplay_active"),
                    \ilObjMediaCast::AUTOPLAY_INACT => $lng->txt("mcst_autoplay_inactive")
                ],
                "",
                (string) $settings->getAutoplayMode()
            )
            ->number(
                "nr_videos",
                $lng->txt("mcst_nr_videos"),
                "",
                max(1, $settings->getNumberInitialVideos()),
                1
            )
            ->end()
            ->checkbox(
                "downloadable",
                $lng->txt("mcst_downloadable"),
                $lng->txt("mcst_downloadable_info"),
                $settings->getDownloadable()
            );

        // Webfeed Section
        $news_set = new \ilSetting("news");
        if ($news_set->get("enable_rss_for_internal")) {
            $form = $form
                ->section("webfeed", $lng->txt("mcst_webfeed"))
                ->switch(
                    "defaultaccess",
                    $lng->txt("news_default_visibility"),
                    "",
                    (string) $settings->getDefaultAccess()
                )
                ->group(
                    "0",
                    $lng->txt("news_visibility_users"),
                    $lng->txt("news_news_item_def_visibility_users_info")
                )
                ->group(
                    "1",
                    $lng->txt("news_visibility_public"),
                    $lng->txt("news_news_item_def_visibility_public_info")
                )
                ->end();

            // Extra Feed
            $public_feed = \ilBlockSetting::_lookup("news", "public_feed", 0, $this->obj_id);
            $form = $form
                ->optional(
                    "extra_feed",
                    $lng->txt("news_public_feed"),
                    $lng->txt("news_public_feed_info"),
                    (bool) $public_feed
                )
                ->number(
                    "keep_rss_min",
                    $lng->txt("news_keep_minimal_x_items"),
                    $lng->txt("news_keep_minimal_x_items_info") . " (" . \ilNewsItem::_lookupRSSPeriod() . " " . ($lng->txt(\ilNewsItem::_lookupRSSPeriod() == 1 ? "day" : "days")) . ")",
                    (int) \ilBlockSetting::_lookup(
                        "news",
                        "keep_rss_min",
                        0,
                        $this->obj_id
                    ),
                    0,
                    100
                )->end();

            // Include Files in RSS
            $form = $form->checkbox(
                "public_files",
                $lng->txt("mcst_incl_files_in_rss"),
                $lng->txt("mcst_incl_files_in_rss_info"),
                $settings->getPublicFiles()
            );
        }

        // Learning Progress Section
        if (\ilLearningProgressAccess::checkAccess($this->ref_id)) {
            $form = $form->section("learning_progress", $lng->txt("learning_progress"))
                         ->checkbox(
                             "auto_det_lp",
                             $lng->txt("mcst_new_items_det_lp"),
                             $lng->txt("mcst_new_items_det_lp_info"),
                             $settings->getNewItemsInLearningProgress()
                         );
        }

        if (!$this->global_settings->get('disable_comments')) {
            $lng->loadLanguageModule("notes");
            // Features Section
            $form = $form->section("features", $lng->txt('obj_features'));
            $form = $form->checkbox(
                "comments",
                $lng->txt("notes_comments"),
                "",
                $this->domain->notes()->commentsActive($this->obj_id)
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

        $old_settings = $this->domain->mediacastSettings()->getById($this->obj_id);

        if ($form->isValid()) {

            $form->saveStdTitleAndDescription($this->obj_id, "mcst");
            $form->saveStdTile($this->obj_id, "mcst");
            $form->saveOnline($this->obj_id, "mcst");

            $settings = $this->data->settings(
                $this->obj_id,
                (bool) $form->getData("public_files"),
                (bool) $form->getData("downloadable"),
                (int) $form->getData("defaultaccess"),
                (int) $form->getData("order"),
                (string) $form->getData("viewmode"),
                (bool) $form->getData("autoplaymode"),
                (int) $form->getData("nr_videos"),
                \ilLearningProgressAccess::checkAccess($this->ref_id) && (bool) $form->getData("auto_det_lp")
            );
            $this->domain->mediacastSettings()->update($settings);

            if (!$this->global_settings->get('disable_comments')) {
                $this->domain->notes()->activateComments(
                    $this->obj_id,
                    $form->getData("comments")
                );
            }

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
