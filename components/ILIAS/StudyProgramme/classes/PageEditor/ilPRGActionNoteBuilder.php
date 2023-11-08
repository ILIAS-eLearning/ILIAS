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
 * Builds PageContent "Note"
 */
class ilPRGActionNoteBuilder
{
    protected UIFactory $ui_factory;
    protected Renderer $ui_renderer;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilTemplate $tpl;
    protected PRGAssignmentRepository $repo_assignment;
    protected ilStudyProgrammeSettingsRepository $repo_settings;
    protected int $usr_id;

    public function __construct(
        UIFactory $ui_factory,
        Renderer $ui_renderer,
        ilLanguage $lng,
        ilCtrl $ctrl,
        ilTemplate $tpl,
        PRGAssignmentRepository $repo_assignment,
        ilStudyProgrammeSettingsRepository $repo_settings,
        int $usr_id
    ) {
        global $DIC;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->lng = $DIC->language();
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->repo_assignment = $repo_assignment;
        $this->repo_settings = $repo_settings;
        $this->usr_id = $usr_id;
    }

    public function getNoteFor(int $prg_obj_id): string
    {
        $ass = $this->getLatestAssignment($prg_obj_id);
        $instruction = '';
        $icon = 'page_editor/icon_pean.svg';
        $dealine_str = '';

        if ($ass) {
            $progresses[] = $ass->getProgressTree();
            $now = new DateTimeImmutable();
            $deadline = null;
            $in_progress = array_filter($progresses, fn($pgs) => $pgs->isInProgress());
            $accredited_or_completed = array_filter($progresses, fn($pgs) => $pgs->isSuccessful());
            if (count($progresses) === 0) {
                $failed = array_filter($progresses, fn($pgs) => $pgs->isFailed());
                $failed = $this->sortByDeadline($failed);
                if (count($failed) > 0 || $accredited_or_completed > 0) {
                    $instruction = 'pc_prgactionnote_no_actions_required';
                    $deadline = array_shift($failed)->getDeadline();
                    $dealine_str = ' ' . $deadline->format('d.m.Y');
                }
            } elseif($accredited_or_completed) {
                $instruction = 'pc_prgactionnote_no_actions_required';
            } else {
                $instruction = 'pc_prgactionnote_complete_content';
                $in_progress_with_deadline = $this->sortByDeadline($in_progress);
                $in_progress_with_deadline = array_filter(
                    $in_progress_with_deadline,
                    fn($pgs) => $pgs->getDeadline()->format('Y-m-d') >= $now->format('Y-m-d')
                );
                if (count($in_progress_with_deadline) > 0) {
                    $deadline = array_shift($in_progress_with_deadline)->getDeadline();
                }

                if (!is_null($deadline)) {
                    $dealine_str = ' ' . $deadline->format('d.m.Y') . '.';
                    $instruction = 'pc_prgactionnote_complete_content_with_deadline';
                }
            }
        } else {
            $instruction = 'pc_prgactionnote_no_actions_required';
        }

        $icon = $this->ui_renderer->render(
            $this->ui_factory->symbol()->icon()->custom(
                ilUtil::getImagePath($icon),
                $this->lng->txt($instruction)
            )->withSize('large')
        );

        $this->tpl->setVariable("HEADLINE", $this->lng->txt('pc_prgactionnote_headline'));
        $this->tpl->setVariable("ICON", $icon);
        $this->tpl->setVariable("NOTE_TEXT", $this->lng->txt($instruction) . $dealine_str);
        return $this->tpl->get();
    }

    protected function getLatestAssignment(int $prg_obj_id): ?ilPRGAssignment
    {
        $assignments = $this->repo_assignment->getForUserOnNode($this->usr_id, $prg_obj_id);
        usort(
            $assignments,
            fn(ilPRGAssignment $a, ilPRGAssignment $b)
            => $a->getProgressTree()->getAssignmentDate() <=> $b->getProgressTree()->getAssignmentDate()
        );
        $assignments = array_reverse($assignments);
        return $assignments ? current($assignments) : null;
    }

    protected function sortByDeadline(array $progresses): array
    {
        $progresses = array_filter($progresses, fn($pgs) => $pgs->getDeadline());
        usort(
            $progresses,
            static function (ilPRGProgress $a, ilPRGProgress $b): int {
                $a_dat = $a->getDeadline();
                $b_dat = $b->getDeadline();
                if ($a_dat === $b_dat) {
                    return 0;
                }
                return ($a_dat < $b_dat) ? -1 : 1;
            }
        );
        return $progresses;
    }
}
