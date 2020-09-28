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
     * @throws ilException
     */
    public function getHTML() : string
    {
        $items = [];
        foreach ($this->getUsersAssignments() as $assignments) {
            $properties = [];
            krsort($assignments);
            /** @var ilStudyProgrammeUserAssignment $current */
            $current = current($assignments);
            if (!$this->isReadable($current)) {
                continue;
            }

            $current_prg = $current->getStudyProgramme();

            /** @var ilStudyProgrammeSettings $current_prg_settings */
            $current_prg_settings = $current_prg->getRawSettings();

            /** @var ilStudyProgrammeUserProgress $current_progress */
            $current_progress = $current->getRootProgress();

            list($valid, $validation_date) = $this->findValidationValues($assignments);

            list($minimum_percents, $current_percents) = $this->calculatePercent(
                $current_prg,
                $current_progress->getCurrentAmountOfPoints()
            );

            $current_status = $current_progress->getStatus();
            $validation_expires = $current_prg_settings->validationExpires();
            $deadline = $current_prg_settings->getDeadlineSettings()->getDeadlineDate();
            $restart_date = $current->getRestartDate();

            $properties[] = $this->fillMinimumCompletion($minimum_percents);
            $properties[] = $this->fillCurrentCompletion($current_percents);
            $properties[] = $this->fillStatus((string) $current_status);

            if ($this->isCompleted($current_status)) {
                $properties[] = $this->fillRestartFrom($restart_date);
            }

            if ($this->isInProgress($current_status)) {
                $properties[] = $this->fillFinishUntil($deadline);
            }

            if ($validation_expires && $valid) {
                $properties[] = $this->fillValidUntil($validation_date);
            } elseif (!$validation_expires && $valid) {
                $properties[] = $this->fillValid();
            } else {
                $properties[] = $this->fillNotValid();
            }

            $items[] = $this->buildItem($current->getStudyProgramme(), $properties);
        }

        if (count($items) == 0) {
            return "";
        }

        $group[] = $this->factory->item()->group("", $items);
        $panel = $this->factory->panel()->listing()->standard($this->lng->txt("dash_studyprogramme"), $group);

        return $this->renderer->render($panel);
    }

    protected function isCompleted(int $current_status) : bool
    {
        $status = [
            ilStudyProgrammeProgress::STATUS_ACCREDITED,
            ilStudyProgrammeProgress::STATUS_COMPLETED
        ];
        return in_array($current_status, $status);
    }

    protected function isInProgress(int $current_status) : bool
    {
        $status = [
            ilStudyProgrammeProgress::STATUS_IN_PROGRESS
        ];
        return in_array($current_status, $status);
    }

    protected function fillValidUntil(DateTime $value = null) : array
    {
        $date_string = "";
        if (!is_null($value)) {
            $date = new ilDate(
                $value->format('Y-m-d'),
                IL_CAL_DATE
            );
            $date_string = ilDatePresentation::formatDate($date);
        }
        return [
            $this->txt('prg_dash_label_valid') => $date_string
        ];
    }

    protected function fillNotValid() : array
    {
        return [
            $this->txt('prg_dash_label_valid') => $this->txt('no')
        ];
    }

    protected function fillValid() : array
    {
        return [
            $this->txt('prg_dash_label_valid') => $this->txt('yes')
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

    protected function fillFinishUntil(DateTime $value = null) : array
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

    protected function fillRestartFrom(DateTime $value = null) : array
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
        ilStudyProgrammeUserAssignment $assignment,
        string $permission
    ) : bool {
        $prg = $assignment->getStudyProgramme();
        return $this->access->checkAccess($permission, "", $prg->getRefId(), "prg", $prg->getId());
    }

    /**
     * @throws ilException
     */
    protected function isReadable(ilStudyProgrammeUserAssignment $assignment) : bool
    {
        if ($this->getVisibleOnPDMode() == ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_ALLWAYS) {
            return true;
        }

        return $this->hasPermission($assignment, "read");
    }

    /**
     * @return ilStudyProgrammeUserAssignment[]
     */
    protected function getUsersAssignments() : array
    {
        /** @var ilStudyProgrammeUserAssignmentDB $assignments_db */
        $assignments_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB'];
        return $assignments_db->getDashboardInstancesforUser($this->user->getId());
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

    /**
     * @return DateTime|null
     * @throws ilException
     * @throws ilStudyProgrammeDashboardException
     */
    protected function findValid(array $assignments)
    {
        $status = [
            ilStudyProgrammeProgress::STATUS_COMPLETED,
            ilStudyProgrammeProgress::STATUS_ACCREDITED
        ];
        /** @var ilStudyProgrammeUserAssignment $assignment */
        foreach ($assignments as $key => $assignment) {
            $progress = $assignment->getRootProgress();
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
        $link = $this->getInfoLink((int) $prg->getRefId());
        $title_btn = $this->factory->button()->shy($title, $link);

        $icon = $this->factory->symbol()->icon()->standard('prg', $title, 'medium');
        return $this->factory->item()->standard($title_btn)
            ->withProperties(array_merge(...$properties))
            ->withDescription($prg->getDescription() ?? "")
            ->withLeadIcon($icon)
        ;
    }

    protected function getInfoLink(int $prg_ref_id) : string
    {
        $this->ctrl->setParameterByClass(
            'ilinfoscreengui',
            'ref_id',
            $prg_ref_id
        );
        $link = $this->ctrl->getLinkTargetByClass(
            [
                'ilrepositorygui',
                'ilobjstudyprogrammegui',
                'ilinfoscreengui'
            ],
            'showSummary'
        );
        $this->ctrl->setParameterByClass(
            'ilinfoscreengui',
            'ref_id',
            null
        );
        return $link;
    }
}
