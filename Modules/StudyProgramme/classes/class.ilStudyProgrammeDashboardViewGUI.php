<?php

declare(strict_types=1);

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

class ilStudyProgrammeDashboardViewGUI
{
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ilSetting $setting;
    protected ILIAS\UI\Factory $factory;
    protected ILIAS\UI\Renderer $renderer;
    protected ilCtrl $ctrl;
    protected ilStudyProgrammeUserTable $usr_table;
    protected int $usr_id;

    protected ?string $visible_on_pd_mode = null;


    public function __construct(
        ilLanguage $lng,
        ilAccess $access,
        ilSetting $setting,
        ILIAS\UI\Factory $factory,
        ILIAS\UI\Renderer $renderer,
        ilCtrl $ctrl,
        ilStudyProgrammeUserTable $user_table,
        int $usr_id,
    ) {
        $this->lng = $lng;
        $this->lng->loadLanguageModule('prg');
        $this->access = $access;
        $this->setting = $setting;
        $this->factory = $factory;
        $this->renderer = $renderer;
        $this->ctrl = $ctrl;
        $this->user_table = $user_table;
        $this->usr_id = $usr_id;
    }

    /**
     * @throws ilException
     */
    public function getHTML(): string
    {
        //array ilStudyProgrammeUserTableRow[]
        $this->user_table->disablePermissionCheck(true);
        $rows = $this->user_table->fetchSingleUserRootAssignments($this->usr_id);
        foreach ($rows as $row) {
            $prg = ilObjStudyProgramme::getInstanceByObjId($row->getNodeId());
            if (! $this->isReadable($prg)) {
                continue;
            }
            $properties = [];

            /*
                        $progress = $assignment->getProgressTree();
                        [$minimum_percents, $current_percents] = $this->calculatePercent(
                            $current_prg,
                            $progress->getCurrentAmountOfPoints()
                        );
            */
            $properties[] = [$this->txt('prg_dash_label_status') => $row->getStatus()];



            //$properties[] = $this->fillMinimumCompletion($minimum_percents);
            //$properties[] = $this->fillCurrentCompletion($current_percents);

            if (in_array($row->getStatusRaw(), [ilPRGProgress::STATUS_COMPLETED, ilPRGProgress::STATUS_ACCREDITED])) {
                //$restart_date = $assignment->getRestartDate();
                //$properties[] = $this->fillRestartFrom($restart_date);


                $validity = $row->getValidity();
                if (true || $row->getExpiryDate()) {
                    $validity .= ' (' . $row->getExpiryDate() . ')';
                }
                $properties[] = [$this->txt('prg_dash_label_valid') => $validity];
            } else {
                $properties[] = [$this->txt('prg_dash_label_finish_until') =>$row->getDeadline()];
            }

            $items[] = $this->buildItem($prg, $properties);
        }

        if (count($items) === 0) {
            return '';
        }
        $group[] = $this->factory->item()->group("", $items);
        $panel = $this->factory->panel()->listing()->standard($this->lng->txt("dash_studyprogramme"), $group);

        return $this->renderer->render($panel);
    }

    protected function fillValidation(?bool $valid, ?DateTimeImmutable $validation_expiry_date): array
    {
        $validation = '';
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

        return [$this->txt('prg_dash_label_valid') => $validation];
    }

    protected function fillMinimumCompletion(float $value): array
    {
        $title = $value . " " . $this->txt('percentage');
        return [$this->txt('prg_dash_label_minimum') => $title];
    }

    protected function fillCurrentCompletion(float $value): array
    {
        $title = $value . " " . $this->txt('percentage');
        return [$this->txt('prg_dash_label_gain') => $title];
    }


    protected function fillFinishUntil(DateTimeImmutable $value = null): array
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

    protected function fillRestartFrom(DateTimeImmutable $value = null): array
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

    protected function getVisibleOnPDMode(): string
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

    protected function isReadable(ilObjStudyProgramme $prg): bool
    {
        if ($this->getVisibleOnPDMode() === ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_ALLWAYS) {
            return true;
        }
        return $this->access->checkAccess('read', "", $prg->getRefId(), "prg", $prg->getId());
    }

    protected function txt(string $code): string
    {
        return $this->lng->txt($code);
    }

    protected function calculatePercent(ilObjStudyProgramme $prg, int $current_points): array
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

    protected function buildItem(
        ilObjStudyProgramme $prg,
        array $properties
    ): ILIAS\UI\Component\Item\Item {
        $title = $prg->getTitle();
        $link = $this->getDefaultTargetUrl($prg->getRefId());
        $title_btn = $this->factory->button()->shy($title, $link);
        $description = $prg->getLongDescription() ?? "";
        $max = (int) $this->setting->get("rep_shorten_description_length");
        if ($this->setting->get("rep_shorten_description") && $max !== 0) {
            $description = ilStr::shortenTextExtended($description, $max, true);
        }

        $icon = $this->factory->symbol()->icon()->standard('prg', $title, 'medium');
        return $this->factory->item()->standard($title_btn)
            ->withProperties(array_merge(...$properties))
            ->withDescription($description)
            ->withLeadIcon($icon)
        ;
    }

    protected function getDefaultTargetUrl(int $prg_ref_id): string
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
