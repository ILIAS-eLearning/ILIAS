<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

use ILIAS\BookingManager\InternalDomainService;
use ILIAS\BookingManager\InternalGUIService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookBulkCreationGUI
{
    protected \ILIAS\BookingManager\Objects\ObjectsManager $objects_manager;
    protected ilObjBookingPool $pool;
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui,
        ilObjBookingPool $pool
    ) {
        $this->pool = $pool;
        $this->domain = $domain;
        $this->gui = $gui;
        $lng = $domain->lng();
        $lng->loadLanguageModule("book");
        $this->objects_manager = $domain
            ->objects($pool->getId());
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("showCreationForm");

        switch ($next_class) {
            default:
                if (in_array($cmd, [
                    "showCreationForm",
                    "showConfirmationScreen",
                    "cancelCreation",
                    "createObjects"
                ])) {
                    $this->$cmd();
                }
        }
    }

    public function modifyToolbar(ilToolbarGUI $toolbar): void
    {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $components = $this
            ->gui
            ->modal($lng->txt("book_bulk_creation"))
            ->getAsyncTriggerButtonComponents(
                $lng->txt("book_bulk_creation"),
                $ctrl->getLinkTarget($this, "showCreationForm", "", true),
                false
            );
        foreach ($components as $c) {
            $toolbar->addComponent($c);
        }
    }

    protected function showCreationForm(): void
    {
        $lng = $this->domain->lng();
        $this->gui
            ->modal($lng->txt("book_bulk_creation"))
            ->form($this->getCreationForm())
            ->send();
    }

    protected function getCreationForm(): \ILIAS\Repository\Form\FormAdapterGUI
    {
        $lng = $this->domain->lng();
        $schedule_manager = $this->domain->schedules($this->pool->getId());
        $schedules = $schedule_manager->getScheduleList();
        $form = $this
            ->gui
            ->form(self::class, "showConfirmationScreen")
            ->asyncModal()
            ->section("creation", $lng->txt("book_bulk_data"))
            ->textarea(
                "data",
                $lng->txt("book_title_description_nr"),
                $lng->txt("book_title_description_nr_info"),
            )
            ->required();

        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $form->select(
                "schedule_id",
                $lng->txt("book_schedule"),
                $schedules,
                "",
                (string) array_key_first($schedules)
            )
                ->required();
        }


        return $form;
    }

    protected function showConfirmationScreen(): void
    {
        $form = $this->getCreationForm();
        $lng = $this->domain->lng();
        if (!$form->isValid()) {
            $this->gui->modal($lng->txt("book_bulk_creation"))
                      ->form($form)
                      ->send();
        }

        $schedule_id = 0;
        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $schedule_id = (int) $form->getData("schedule_id");
        }
        $this->gui->modal($lng->txt("book_bulk_creation"))
                  ->legacy($this->renderConfirmation(
                      $form->getData("data"),
                      $schedule_id
                  ))
                  ->send();
    }

    protected function renderConfirmation(string $data, int $schedule_id = 0): string
    {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();

        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $button1 = $f->button()->standard(
            $lng->txt("book_create_objects"),
            "#"
        )->withAdditionalOnLoadCode(static function (string $id) {
            return <<<EOT
            const book_bulk_button = document.getElementById("$id");
            book_bulk_button.addEventListener("click", (event) => {
                book_bulk_button.closest(".modal").querySelector("form").submit();
            });
EOT;
        });
        $button2 = $f->button()->standard(
            $lng->txt("cancel"),
            $ctrl->getLinkTarget($this, "cancelCreation")
        );

        $mbox = $f->messageBox()->confirmation(
            $lng->txt("book_bulk_confirmation")
        )->withButtons([$button1]);

        $ctrl->setParameter($this, "schedule_id", $schedule_id);
        $table = new ilBookingBulkCreationTableGUI(
            $this,
            "renderConfirmation",
            $data,
            $this->pool->getId()
        );

        return $r->render($mbox) .
            $table->getHTML();
    }

    protected function createObjects(): void
    {
        $main_tpl = $this->gui->mainTemplate();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $request = $this->gui->standardRequest();

        $data = $request->getBulkCreationData();
        $schedule_id = 0;
        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $schedule_id = $request->getScheduleId();
        }
        $arr = $this->objects_manager->createObjectsFromBulkInputString($data, $schedule_id);
        $main_tpl->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
        $ctrl->returnToParent($this);
    }

    protected function cancelCreation(): void
    {
        $ctrl = $this->gui->ctrl();
        $ctrl->returnToParent($this);
    }
}
