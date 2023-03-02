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
    protected ilStudyProgrammeUserTable $user_table;
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
        int $usr_id
    ) {
        $this->lng = $lng;
        $this->lng->loadLanguageModule('prg');
        $this->lng->loadLanguageModule('certificate');
        $this->access = $access;
        $this->setting = $setting;
        $this->factory = $factory;
        $this->renderer = $renderer;
        $this->ctrl = $ctrl;
        $this->user_table = $user_table;
        $this->usr_id = $usr_id;
    }

    public function getHTML(): string
    {
        //array ilStudyProgrammeUserTableRow[]
        $this->user_table->disablePermissionCheck(true);
        $rows = $this->user_table->fetchSingleUserRootAssignments($this->usr_id);
        $items = [];
        foreach ($rows as $row) {
            $prg = ilObjStudyProgramme::getInstanceByObjId($row->getNodeId());
            if (! $this->isReadable($prg)) {
                continue;
            }

            $min = $row->getPointsRequired();
            $max = $row->getPointsReachable();
            $cur = $row->getPointsCurrent();
            $required_string = $min;
            if ((float)$max < (float)$min) {
                $required_string .= ' ' . $this->txt('prg_dash_label_unreachable') . ' (' . $max . ')';
            }

            $properties = [
                [$this->txt('prg_dash_label_minimum') => $required_string],
                [$this->txt('prg_dash_label_gain') => $cur],
                [$this->txt('prg_dash_label_status') => $row->getStatus()],
            ];

            if (in_array($row->getStatusRaw(), [ilPRGProgress::STATUS_COMPLETED, ilPRGProgress::STATUS_ACCREDITED])) {
                $validity = $row->getExpiryDate() ? $row->getExpiryDate() : $row->getValidity();
                $properties[] = [$this->txt('prg_dash_label_valid') => $validity];
            } else {
                $properties[] = [$this->txt('prg_dash_label_finish_until') =>$row->getDeadline()];
            }

            $validator = new ilCertificateDownloadValidator();
            if ($validator->isCertificateDownloadable($row->getUsrId(), $row->getNodeId())) {
                $cert_url = "ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $prg->getRefId() . "&cmd=deliverCertificate";
                $cert_link = $this->factory->link()->standard($this->txt('download_certificate'), $cert_url);
                $properties[] = [$this->txt('certificate') => $this->renderer->render($cert_link)];
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
