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

namespace ILIAS\BookingManager\Settings;

use ILIAS\BookingManager\InternalDomainService;
use ILIAS\BookingManager\InternalGUIService;
use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\BookingManager\InternalDataService;
use ilDidacticTemplateGUI;

/**
 * @ilCtrl_Calls ILIAS\BookingManager\Settings\SettingsGUI: ilDidacticTemplateGUI
 */
class SettingsGUI
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $obj_id,
        protected int $ref_id,
        protected bool $creation_mode,
        protected object $parent_gui
    ) {
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();
        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("edit");

        switch ($next_class) {
            case strtolower(ilDidacticTemplateGUI::class):
                $ctrl->setReturn($this, 'edit');
                $did = new ilDidacticTemplateGUI(
                    $this->parent_gui,
                    $this->getEditForm()->getDidacticTemplateIdFromRequest()
                );
                $ctrl->forwardCommand($did);
                break;

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
        $settings = $this->domain->bookingSettings()->getByObjId($this->obj_id);
        $form = $this->gui->form(self::class, "save")
                          ->section("general", $lng->txt("book_edit"))
                          ->addStdTitleAndDescription(
                              $this->obj_id,
                              "book"
                          );
        $form = $form->addDidacticTemplates(
            "book",
            $this->ref_id,
            $this->creation_mode
        );

        $form = $form
            ->switch(
                "stype",
                $lng->txt("book_schedule_type"),
                "",
                (string) $settings->getScheduleType()
            );
        $form = $form
            ->group(
                (string) \ilObjBookingPool::TYPE_FIX_SCHEDULE,
                $lng->txt("book_schedule_type_fixed"),
                $lng->txt("book_schedule_type_fixed_info")
            )
            ->number(
                "period",
                $lng->txt("book_reservation_filter_period"),
                $lng->txt("days") . " - " .
                $lng->txt("book_reservation_filter_period_info"),
                $settings->getReservationPeriod()
            )
            ->checkbox(
                "rmd",
                $lng->txt("book_reminder_setting"),
                "",
                (bool) $settings->getReminderStatus()
            )
            ->number(
                "rmd_day",
                $lng->txt("book_reminder_day"),
                "",
                max($settings->getReminderDay(), 1)
            )
            ->group(
                (string) \ilObjBookingPool::TYPE_NO_SCHEDULE,
                $lng->txt("book_schedule_type_none_direct"),
                $lng->txt("book_schedule_type_none_direct_info")
            )
            ->number(
                "limit",
                $lng->txt("book_bookings_per_user") . " - " .
                $lng->txt("book_overall_limit"),
                "",
                $settings->getOverallLimit()
            );

        $pref_options_disabled = false;
        if ($settings->getScheduleType() === \ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES) {
            $pref_manager = $this->domain->preferences(
                new \ilObjBookingPool($this->ref_id)
            );
            $pref_options_disabled = $pref_manager->hasRun();
        }

        $form = $form
            ->group(
                (string) \ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES,
                $lng->txt("book_schedule_type_none_preference"),
                $lng->txt("book_schedule_type_none_preference_info")
            );
        $form = $form
            ->number(
                "preference_nr",
                $lng->txt("book_nr_of_preferences"),
                $lng->txt("book_nr_preferences") . " - " .
                $lng->txt("book_nr_of_preferences_info"),
                $settings->getPreferenceNr()
            );
        if ($pref_options_disabled) {
            $form = $form->disabled();
        }
        $form = $form
            ->dateTime(
                "pref_deadline",
                $lng->txt("book_pref_deadline"),
                $lng->txt("book_pref_deadline_info"),
                $settings->getPrefDeadline()
                    ? new \ilDateTime($settings->getPrefDeadline(), IL_CAL_UNIX) : null
            );

        // #14478
        $mode_disabled = (count(\ilBookingObject::getList($this->obj_id)));

        if ($pref_options_disabled) {
            $form = $form->disabled();
        } else {
            if (!$mode_disabled) {
                $form = $form->required();
            }
        }
        $form->end();

        if ($mode_disabled) {
            $form = $form->disabled(true);
        }

        $form = $form
            ->checkbox(
                "public",
                $lng->txt("book_public_log"),
                $lng->txt("book_public_log_info"),
                $settings->getPublicLog()
            )
            ->checkbox(
                "messages",
                $lng->txt("book_messages"),
                $lng->txt("book_messages_info"),
                $settings->getMessages()
            );

        $lng->loadLanguageModule("rep");

        $form = $form
            ->section(
                "rep",
                $lng->txt('rep_activation_availability')
            )
            ->addOnline(
                $this->obj_id,
                "book"
            );

        $form = $form
            ->section(
                "obj_presentation",
                $lng->txt('obj_presentation')
            )->addStdTile(
                $this->obj_id,
                "book"
            );

        $form = $form->addAdditionalFeatures(
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

        $old_settings = $this->domain->bookingSettings()->getByObjId($this->obj_id);

        if ($form->isValid()) {
            $form->saveStdTitleAndDescription(
                $this->obj_id,
                "book"
            );
            $form->saveStdTile(
                $this->obj_id,
                "book"
            );
            $form->saveOnline(
                $this->obj_id,
                "book"
            );
            $form->saveAdditionalFeatures(
                $this->obj_id,
                [
                    \ilObjectServiceSettingsGUI::CUSTOM_METADATA
                ]
            );

            $settings = $this->data->settings(
                $this->obj_id,
                (bool) $form->getData("public"),
                (int) $form->getData("stype"),
                (int) $form->getData("limit"),
                (int) $form->getData("period"),
                (bool) $form->getData("rmd"),
                (int) $form->getData("rmd_day"),
                $form->getData("pref_deadline")
                    ? (int) $form->getData("pref_deadline")->getUnixTime()
                    : 0,
                (int) $form->getData("preference_nr"),
                (bool) $form->getData("messages")
            );

            $this->domain->bookingSettings()->update($settings);


            // check if template is changed
            $form->redirectToDidacticConfirmationIfChanged(
                $this->ref_id,
                "book",
                static::class
            );

            $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
            $ctrl->redirectByClass(self::class, "edit");
        } else {
            $mt = $this->gui->ui()->mainTemplate();
            $mt->setContent($form->render());
        }
    }
}
