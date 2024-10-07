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

namespace ILIAS\Blog\Settings;

use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\Blog\InternalDataService;

class SettingsGUI
{
    protected \ILIAS\Blog\ReadingTime\BlogSettingsGUI $reading_time_gui;
    protected \ilSetting $global_settings;
    protected \ILIAS\Notes\DomainService $notes;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $obj_id,
        protected bool $in_repository
    ) {
        $this->notes = $domain->notes();
        $this->global_settings = $domain->settings();
        $this->reading_time_gui = $gui->readingTime()->settingsGUI($obj_id);
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
        $settings = $this->domain->blogSettings()->getByObjId($this->obj_id);

        $lng = $this->domain->lng();
        $form = $this->gui->form(self::class, "save")
                          ->section("general", $lng->txt("blog_edit"))
                          ->addStdTitleAndDescription(
                              $this->obj_id,
                              "blog"
                          );

        if ($this->in_repository) {
            $form = $form->checkbox(
                "approval",
                $lng->txt("blog_enable_approval"),
                $lng->txt("blog_enable_approval_info"),
                $settings->getApproval()
            );
        }

        $form = $form->checkbox(
            "notes",
            $lng->txt("blog_enable_notes"),
            "",
            $this->notes->commentsActive($this->obj_id)
        );

        if ($this->global_settings->get('enable_global_profiles')) {
            $form = $form->checkbox(
                "rss",
                $lng->txt("blog_enable_rss"),
                "blog_enable_rss_info",
                $settings->getRss()
            );
        }

        $form = $form->section("availibility", $lng->txt("rep_activation_availability"))
            ->addOnline($this->obj_id, "blog");

        $form = $form->section("nav", $lng->txt("blog_settings_navigation"))
                     ->switch("nav_mode", $lng->txt("blog_nav_mode"), "", (string) $settings->getNavMode())
                     ->group(
                         (string) \ilObjBlog::NAV_MODE_LIST,
                         $lng->txt("blog_nav_mode_month_list"),
                         $lng->txt("blog_nav_mode_month_list_info")
                     )
                     ->number(
                         "nav_list_mon",
                         $lng->txt("blog_nav_mode_month_list_num_month"),
                         $lng->txt("blog_nav_mode_month_list_num_month_info"),
                         (int) $settings->getNavModeListMonths(),
                         1
                     )
                     ->number(
                         "nav_list_mon_with_post",
                         $lng->txt("blog_nav_mode_month_list_num_month_with_post"),
                         $lng->txt("blog_nav_mode_month_list_num_month_with_post_info"),
                         (int) $settings->getNavModeListMonthsWithPostings()
                     )
                     ->group(
                         (string) \ilObjBlog::NAV_MODE_MONTH,
                         $lng->txt("blog_nav_mode_month_single"),
                         $lng->txt("blog_nav_mode_month_single_info")
                     )
                     ->end();

        if ($this->in_repository) {
            $form = $form->checkbox(
                "nav_authors",
                $lng->txt("blog_enable_nav_authors"),
                $lng->txt("blog_enable_nav_authors_info"),
                $settings->getAuthors()
            );
        }

        $form = $form
            ->checkbox(
                "keywords",
                $lng->txt("blog_enable_keywords"),
                $lng->txt("blog_enable_keywords_info"),
                $settings->getKeywords()
            )
            ->section("presentation", $lng->txt("blog_presentation_frame"));

        if ($this->in_repository) {
            $form = $form->addStdTile(
                $this->obj_id,
                "blog"
            );
        }

        $info = ($this->in_repository)
            ? $lng->txt("blog_profile_picture_repository_info")
            : "";

        $form = $form->checkbox(
            "ppic",
            $lng->txt("blog_profile_picture"),
            $info,
            $settings->getProfilePicture()
        );

        $form = $this->reading_time_gui->addSettingToFormAdapter($form);

        $form = $form
            ->section("pres_overview", $lng->txt("blog_presentation_overview"))
            ->number(
                "ov_list_post_num",
                $lng->txt("blog_list_num_postings"),
                $lng->txt("blog_list_num_postings_info"),
                (int) $settings->getOverviewPostings()
            )
            ->optional(
                "abss",
                $lng->txt("blog_abstract_shorten"),
                "",
                $settings->getAbstractShorten()
            )
            ->number(
                "abssl",
                $lng->txt("blog_abstract_shorten_length"),
                $lng->txt("blog_abstract_shorten_characters"),
                $settings->getAbstractShortenLength()
            )->required()
            ->end()
            ->optional(
                "absi",
                $lng->txt("blog_abstract_image"),
                $lng->txt("blog_abstract_image_info"),
                $settings->getAbstractImage()
            )
            ->number(
                "absiw",
                $lng->txt("blog_abstract_image_width"),
                $lng->txt("blog_abstract_image_pixels"),
                $settings->getAbstractImageWidth(),
                32
            )->required()
            ->number(
                "absih",
                $lng->txt("blog_abstract_image_height"),
                $lng->txt("blog_abstract_image_pixels"),
                $settings->getAbstractImageHeight(),
                32
            )->required()
            ->end();

        return $form;
    }

    protected function save(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $form = $this->getEditForm();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();

        $old_settings = $this->domain->blogSettings()->getByObjId($this->obj_id);

        if ($form->isValid()) {
            $form->saveStdTitleAndDescription(
                $this->obj_id,
                "blog"
            );
            $form->saveStdTile(
                $this->obj_id,
                "blog"
            );
            $form->saveOnline(
                $this->obj_id,
                "blog"
            );

            $this->notes->activateComments($this->obj_id, (bool) $form->getData("notes"));

            $this->reading_time_gui->saveSettingFromFormAdapter($form);

            $settings = $this->data->settings(
                $this->obj_id,
                (bool) $form->getData("ppic"),
                "",
                "",
                $this->global_settings->get('enable_global_profiles')
                    ? (bool) $form->getData("ppic")
                    : false,
                $this->in_repository
                    ? (bool) $form->getData("approval")
                    : false,
                (bool) $form->getData("abss"),
                (bool) $form->getData("abss")
                    ? (int) $form->getData("abssl")
                    : 0,
                (bool) $form->getData("absi"),
                (bool) $form->getData("absi")
                    ? (int) $form->getData("absiw")
                    : 0,
                (bool) $form->getData("absi")
                    ? (int) $form->getData("absih")
                    : 0,
                (bool) $form->getData("keywords"),
                $this->in_repository
                    ? (bool) $form->getData("nav_authors")
                    : false,
                (int) $form->getData("nav_mode"),
                (int) $form->getData("nav_mode") === \ilObjBlog::NAV_MODE_LIST
                    ? (int) $form->getData("nav_list_mon_with_post")
                    : 0,
                (int) $form->getData("nav_mode") === \ilObjBlog::NAV_MODE_LIST
                    ? (int) $form->getData("nav_list_mon")
                    : 0,
                (int) $form->getData("ov_list_post_num"),
                $old_settings->getOrder()
            );

            $this->domain->blogSettings()->update($settings);

            $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
            $ctrl->redirectByClass(self::class, "edit");
        } else {
            $mt = $this->gui->ui()->mainTemplate();
            $mt->setContent($form->render());
        }
    }
}
