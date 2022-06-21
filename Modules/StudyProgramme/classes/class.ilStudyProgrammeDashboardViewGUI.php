<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

class ilStudyProgrammeDashboardViewGUI
{

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilSetting
     */
    protected $setting;

    /**
     * @var string
     */
    protected $visible_on_pd_mode;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * @var ILIAS\UI\Factory
     */
    protected $factory;

    /**
     * @var ILIAS\UI\Renderer
     */
    protected $renderer;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    public function __construct(
        ilLanguage $lng,
        ilObjUser $user,
        ilAccess $access,
        ilSetting $setting,
        ILIAS\UI\Factory $factory,
        ILIAS\UI\Renderer $renderer,
        ilCtrl $ctrl,
        ilLogger $log
    ) {
        $this->lng = $lng;
        $this->lng->loadLanguageModule('prg');
        $this->user = $user;
        $this->access = $access;
        $this->setting = $setting;
        $this->log = $log;
        $this->factory = $factory;
        $this->renderer = $renderer;
        $this->ctrl = $ctrl;
    }


    /**
     * @var ilStudyProgrammeProgressRepository | null
     */
    protected $progress_repository;
    protected function getProgressRepository() : ilStudyProgrammeProgressRepository
    {
        if (!$this->progress_repository) {
            $this->progress_repository = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserProgressDB'];
        }
        return $this->progress_repository;
    }

    /**
     * @var ilStudyProgrammeAssignmentRepository | null
     */
    protected $assignment_repository;
    protected function getAssignmentRepository() : ilStudyProgrammeAssignmentRepository
    {
        if (!$this->assignment_repository) {
            $this->assignment_repository = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB'];
        }
        return $this->assignment_repository;
    }

    /**
     * @return ilStudyProgrammeAssignment[]
     */
    protected function getUsersAssignments() : array
    {
        return $this->getAssignmentRepository()->getDashboardInstancesforUser($this->user->getId());
    }

    /**
     * @throws ilException
     */
    public function getHTML() : string
    {
        $items = [];
        $now = new DateTimeImmutable();

        foreach ($this->getUsersAssignments() as $assignments) {
            $properties = [];
            krsort($assignments);

            $assignment = current($assignments);
            if (!$this->isReadable($assignment)) {
                continue;
            }

            $progress = $this->getProgressRepository()->getRootProgressOf($assignment);

            $current_prg = ilObjStudyProgramme::getInstanceByObjId($assignment->getRootId());
            list($minimum_percents, $current_percents) = $this->calculatePercent(
                $current_prg,
                $progress->getCurrentAmountOfPoints()
            );

            $current_status = $progress->getStatus();
            $deadline = $progress->getDeadline();
            $restart_date = $assignment->getRestartDate();

            $properties[] = $this->fillMinimumCompletion($minimum_percents);
            $properties[] = $this->fillCurrentCompletion($current_percents);
            $properties[] = $this->fillStatus((string) $current_status);

            if ($progress->isSuccessful()) {
                $properties[] = $this->fillRestartFrom($restart_date);
                
                $valid = $progress->hasValidQualification($now);
                $validation_expiry_date = $progress->getValidityOfQualification();
                $properties[] = $this->fillValidation($valid, $validation_expiry_date);
            }
            if ($progress->isInProgress()) {
                $properties[] = $this->fillFinishUntil($deadline);
            }



            $items[] = $this->buildItem($current_prg, $properties);
        }

        if (count($items) == 0) {
            return "";
        }

        $group[] = $this->factory->item()->group("", $items);
        $panel = $this->factory->panel()->listing()->standard($this->lng->txt("dash_studyprogramme"), $group);

        return $this->renderer->render($panel);
    }



    protected function isInProgress(int $current_status) : bool
    {
        $status = [
            ilStudyProgrammeProgress::STATUS_IN_PROGRESS
        ];
        return in_array($current_status, $status);
    }

    protected function fillValidation(
        ?bool $valid,
        ?DateTimeImmutable $validation_expiry_date
    ) : array {
        if (!$valid) {
            $validation = $this->txt('no');
        }
        if ($valid && is_null($validation_expiry_date)) {
            $validation = $this->txt('yes');
        }
        if ($valid && !is_null($validation_expiry_date)) {
            $date = new ilDate($validation_expiry_date->format('Y-m-d'), IL_CAL_DATE);
            $validation = ilDatePresentation::formatDate($date);
        }

        return [
            $this->txt('prg_dash_label_valid') => $validation
        ];
    }

    protected function fillMinimumCompletion(float $value) : array
    {
        $title = $value . " " . $this->txt('percentage');
        return [
            $this->txt('prg_dash_label_minimum') => $title
        ];
    }

    protected function fillCurrentCompletion(float $value) : array
    {
        $title = $value . " " . $this->txt('percentage');
        return [
            $this->txt('prg_dash_label_gain') => $title
        ];
    }

    protected function fillStatus(string $status) : array
    {
        return [
            $this->txt('prg_dash_label_status') => $this->txt('prg_status_' . $status)
        ];
    }

    protected function fillFinishUntil(DateTimeImmutable $value = null) : array
    {
        $ret = [];
        if (!is_null($value)) {
            $date = new ilDate(
                $value->format('Y-m-d'),
                IL_CAL_DATE
            );
            $date_string = ilDatePresentation::formatDate($date);
            $ret[$this->txt('prg_dash_label_finish_until')] = $date_string;
        }
        return $ret;
    }

    protected function fillRestartFrom(DateTimeImmutable $value = null) : array
    {
        $ret = [];
        if (!is_null($value)) {
            $date = new ilDate(
                $value->format('Y-m-d'),
                IL_CAL_DATE
            );
            $date_string = ilDatePresentation::formatDate($date);
            $ret[$this->txt('prg_dash_label_restart_from')] = $date_string;
        }
        return $ret;
    }

    protected function getVisibleOnPDMode() : string
    {
        if (is_null($this->visible_on_pd_mode)) {
            $this->visible_on_pd_mode =
                $this->setting->get(
                    ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD,
                    ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_READ
                );
        }
        return $this->visible_on_pd_mode;
    }

    /**
     * @throws ilException
     */
    protected function hasPermission(
        ilStudyProgrammeAssignment $assignment,
        string $permission
    ) : bool {
        try {
            $prg = ilObjStudyProgramme::getInstanceByObjId($assignment->getRootId());
        } catch (\ilException $e) {
            return false;
        }
        return $this->access->checkAccess($permission, "", $prg->getRefId(), "prg", $prg->getId());
    }

    /**
     * @throws ilException
     */
    protected function isReadable(ilStudyProgrammeAssignment $assignment) : bool
    {
        if ($this->getVisibleOnPDMode() == ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_ALLWAYS) {
            return true;
        }

        return $this->hasPermission($assignment, "read");
    }

    protected function txt(string $code) : string
    {
        return $this->lng->txt($code);
    }

    protected function calculatePercent(ilObjStudyProgramme $prg, int $current_points) : array
    {
        $minimum_percents = 0;
        $current_percents = 0;

        if ($prg->hasLPChildren()) {
            $minimum_percents = 100;
            if ($current_points > 0) {
                $current_percents = 100;
            }
        }

        $children = $prg->getAllPrgChildren();
        if (count($children) > 0) {
            $max_points = 0;
            foreach ($children as $child) {
                $max_points += $child->getPoints();
            }

            if ($max_points > 0) {
                $prg_points = $prg->getPoints();
                $minimum_percents = round((100 * $prg_points / $max_points), 2);
            }
            if ($current_points > 0) {
                $current_percents = round((100 * $current_points / $max_points), 2);
            }
        }

        return [
            $minimum_percents,
            $current_percents
        ];
    }


    /**
     * @throws ilException
     */
    protected function findValidationValues(array $assignments) : array
    {
        $validation_date = $this->findValid($assignments);

        return [
            !is_null($validation_date) && $validation_date->format("Y-m-d") > date("Y-m-d"),
            $validation_date
        ];
    }


    protected function findValid(array $assignments)
    {
        $status = [
            ilStudyProgrammeProgress::STATUS_COMPLETED,
            ilStudyProgrammeProgress::STATUS_ACCREDITED
        ];
        foreach ($assignments as $key => $assignment) {
            $prg = ilObjStudyProgramme::getInstanceByObjId($assignment->getRootId());
            $progress = $prg->getProgressForAssignment($assignment->getId());
            
            if (in_array($progress->getStatus(), $status)) {
                return $progress->getValidityOfQualification();
            }
        }
        return null;
    }

    protected function buildItem(
        ilObjStudyProgramme $prg,
        array $properties
    ) : ILIAS\UI\Component\Item\Item {
        $title = $prg->getTitle();
        $link = $this->getDefaultTargetUrl((int) $prg->getRefId());
        $title_btn = $this->factory->button()->shy($title, $link);
        $description = $prg->getLongDescription() ?? "";
        $max = $this->setting->get("rep_shorten_description_length");
        if ($this->setting->get("rep_shorten_description") && $max) {
            $description = ilUtil::shortenText($description, $max, true);
        }

        $icon = $this->factory->symbol()->icon()->standard('prg', $title, 'medium');
        return $this->factory->item()->standard($title_btn)
            ->withProperties(array_merge(...$properties))
            ->withDescription($description)
            ->withLeadIcon($icon)
        ;
    }

    protected function getDefaultTargetUrl(int $prg_ref_id) : string
    {
        $this->ctrl->setParameterByClass(
            ilObjStudyProgrammeGUI::class,
            'ref_id',
            $prg_ref_id
        );
        $link = $this->ctrl->getLinkTargetByClass(
            [
                ilRepositoryGUI::class,
                ilObjStudyProgrammeGUI::class,
            ]
        );
        $this->ctrl->setParameterByClass(
            ilObjStudyProgrammeGUI::class,
            'ref_id',
            null
        );
        return $link;
    }
}
