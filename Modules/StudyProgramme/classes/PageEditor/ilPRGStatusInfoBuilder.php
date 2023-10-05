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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;

/**
 * Builds PageContent "Status Information"
 */
class ilPRGStatusInfoBuilder
{
    protected UIFactory $ui_factory;
    protected Renderer $ui_renderer;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilTemplate $tpl;
    protected PRGAssignmentRepository $repo_assignment;
    protected ilStudyProgrammeSettingsRepository $repo_settings;
    protected ilCertificateDownloadValidator $cert_validator;
    protected int $usr_id;

    public function __construct(
        UIFactory $ui_factory,
        Renderer $ui_renderer,
        ilLanguage $lng,
        ilCtrl $ctrl,
        ilTemplate $tpl,
        PRGAssignmentRepository $repo_assignment,
        ilStudyProgrammeSettingsRepository $repo_settings,
        ilCertificateDownloadValidator $cert_validator,
        int $usr_id
    ) {
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->repo_assignment = $repo_assignment;
        $this->repo_settings = $repo_settings;
        $this->cert_validator = $cert_validator;
        $this->usr_id = $usr_id;
    }

    public function getStatusInfoFor(int $prg_obj_id): string
    {
        $ass = $this->getLatestAssignment($prg_obj_id);

        $status = 'pc_prgstatus_status_no_qualification';
        $status_txt = 'pc_prgstatus_text_no_qualification';
        $validity_txt = '';
        $icon = 'standard/icon_not_ok.svg';
        $restart_date = null;

        if ($ass) {
            $pgs = $ass->getProgressTree();
            $now = new DateTimeImmutable();
            if ($pgs->hasValidQualification($now)) {
                $status = 'pc_prgstatus_status_valid_qualification';
                $status_txt = 'pc_prgstatus_unlimited_validation';
                $icon = 'icon_ok.svg';
            }

            if ($validity = $pgs->getValidityOfQualification()) {
                $status_txt = 'pc_prgstatus_expiration_date';
                $validity_txt = ' ' . $validity->format('d.m.Y');

                $restart = $this->getRestartPeriodOfProgrammeNode($prg_obj_id);
                if (!is_null($restart)) {
                    $restart_date = $validity->sub($restart)->format('d.m.Y');
                }
            }
        }

        $icon = $this->ui_renderer->render(
            $this->ui_factory->symbol()->icon()->custom(
                \ilUtil::getImagePath($icon),
                $this->lng->txt($status_txt)
            )
            ->withSize('large')
        );
        $this->tpl->setVariable("ICON", $icon);
        $this->tpl->setVariable("HEADLINE", $this->lng->txt("pc_prgstatus_qualification_headline"));
        $this->tpl->setVariable("STATUS", $this->lng->txt($status));
        $this->tpl->setVariable("STATUS_TEXT", $this->lng->txt($status_txt) . $validity_txt);
        if ($certificate_link = $this->maybeGetCertificateLink($prg_obj_id)) {
            $this->tpl->setVariable("CERTIFICATE", $certificate_link);
        }
        if ($restart_date) {
            $this->tpl->setVariable("EDIT_QUALIFICATION", $this->lng->txt("pc_prgstatus_edit_qualification") . " " . $restart_date);
        }
        return $this->tpl->get();
    }

    protected function getLatestAssignment(int $prg_obj_id): ?ilPRGAssignment
    {
        $assignments = $this->repo_assignment->getForUserOnNode($this->usr_id, $prg_obj_id);
        usort(
            $assignments,
            fn (ilPRGAssignment $a, ilPRGAssignment $b)
            => $a->getProgressTree()->getAssignmentDate() <=> $b->getProgressTree()->getAssignmentDate()
        );
        $assignments = array_reverse($assignments);
        return $assignments ? current($assignments) : null;
    }

    protected function getRestartPeriodOfProgrammeNode(int $prg_obj_id): ?DateInterval
    {
        $settings = $this->repo_settings->get($prg_obj_id);
        $offset_days = $settings->getValidityOfQualificationSettings()->getRestartPeriod();
        if ($offset_days) {
            return new DateInterval('P' . $offset_days . 'D');
        }
        return null;
    }

    protected function maybeGetCertificateLink(int $prg_obj_id): ?string
    {
        if ($this->cert_validator->isCertificateDownloadable($this->usr_id, $prg_obj_id)) {
            $target = $this->ctrl->getLinkTargetByClass("ilObjStudyProgrammeGUI", "deliverCertificate");
            return $this->ui_renderer->render($this->ui_factory->link()->standard($this->lng->txt('download_certificate'), $target));
        }
        return null;
    }
}
