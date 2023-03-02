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

use ILIAS\StudyProgramme\Assignment\Zipper;

/**
 * This trait is for (physical) separation of code only;
 * it is actually just part of an ilPRGAssignment and MUST not be used anywhere else.
 */
trait ilPRGAssignmentActions
{
    abstract public function getEvents(): StudyProgrammeEvents;

    protected function getProgressIdString(int $node_id): string
    {
        return sprintf(
            '%s, progress-id (%s/%s)',
            $this->user_info->getFullname(),
            $this->getId(),
            (string) $node_id
        );
    }

    protected function getNow(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    protected function getRefIdFor(int $obj_id): int
    {
        $refs = ilObject::_getAllReferences($obj_id);
        if (count($refs) < 1) {
            throw new ilException("Could not find ref_id for programme with obj_id $obj_id");
        }
        return (int) array_shift($refs);
    }

    protected function getCourseReferencesInNode(int $node_obj_id): array
    {
        global $DIC; //TODO!!
        $tree = $DIC['tree']; //ilTree
        $node_ref = $this->getRefIdFor($node_obj_id);
        $children = $tree->getChildsByType($node_ref, "crsr");
        $children = array_filter(
            $children,
            fn ($c) => ilObject::_exists((int)$c['ref_id'], true)
                && is_null(ilObject::_lookupDeletedDate((int)$c['ref_id']))
                && ! is_null(ilContainerReference::_lookupTargetRefId((int) $c['obj_id']))
                && is_null(ilObject::_lookupDeletedDate(
                    (int) ilContainerReference::_lookupTargetRefId((int) $c['obj_id'])
                ))
        );
        return $children;
    }

    protected function hasCompletedCourseChild(ilPRGProgress $pgs): ?int
    {
        foreach ($this->getCourseReferencesInNode($pgs->getNodeId()) as $child) {
            $crs_id = ilContainerReference::_lookupTargetId((int)$child["obj_id"]);
            if (ilLPStatus::_hasUserCompleted($crs_id, $this->getUserId())) {
                return (int)$child["obj_id"];
            }
        }
        return null;
    }

    protected function recalculateProgressStatus(
        ilStudyProgrammeSettingsRepository $settings_repo,
        ilPRGProgress $progress
    ): ilPRGProgress {
        if (!$progress->isRelevant()) {
            return $progress;
        }
        $node_settings = $settings_repo->get($progress->getNodeId());
        $completion_mode = $node_settings->getLPMode();

        switch ($completion_mode) {
            case ilStudyProgrammeSettings::MODE_UNDEFINED:
            case ilStudyProgrammeSettings::MODE_LP_COMPLETED:
                return $progress;
                break;
            case ilStudyProgrammeSettings::MODE_POINTS:
                $completing_crs_id = ilPRGProgress::COMPLETED_BY_SUBNODES;
                $achieved_points = $progress->getAchievedPointsOfChildren();
                break;
        }

        $progress = $progress->withCurrentAmountOfPoints($achieved_points);
        $this->notifyScoreChange($progress);

        $required_points = $progress->getAmountOfPoints();
        $successful = ($achieved_points >= $required_points);

        if ($successful && !$progress->isSuccessful()) {
            $progress = $progress
                ->withStatus(ilPRGProgress::STATUS_COMPLETED)
                ->withCompletion($completing_crs_id, $this->getNow());

            $this->notifyProgressSuccess($progress);
        }

        if (!$successful && $progress->isSuccessful()
            && $progress->getStatus() !== ilPRGProgress::STATUS_ACCREDITED
        ) {
            $progress = $progress
                ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS)
                ->withCompletion(null, null)
                ->withValidityOfQualification(null);

            $this->notifyValidityChange($progress);
            $this->notifyProgressRevertSuccess($progress);
        }

        return $progress;
    }

    protected function updateParentProgresses(
        ilStudyProgrammeSettingsRepository $settings_repo,
        Zipper $zipper
    ): Zipper {
        while (!$zipper->isTop()) {
            $zipper = $zipper->toParent()
                ->modifyFocus(
                    function ($pgs) use ($settings_repo) {
                        $today = $this->getNow();
                        $format = ilPRGProgress::DATE_FORMAT;
                        $deadline = $pgs->getDeadline();
                        if (!is_null($deadline)
                            && $deadline->format($format) <= $today->format($format)
                        ) {
                            return $pgs;
                        }
                        return $this->recalculateProgressStatus($settings_repo, $pgs);
                    }
                );
        }
        return $zipper;
    }


    protected function updateProgressValidityFromSettings(
        ilStudyProgrammeValidityOfAchievedQualificationSettings $settings,
        ilPRGProgress $progress
    ): ilPRGProgress {
        $cdate = $progress->getCompletionDate();
        if (!$cdate
            || $progress->isSuccessful() === false
        ) {
            return $progress;
        }
        $period = $settings->getQualificationPeriod();
        $date = $settings->getQualificationDate();

        if ($period) {
            $date = $cdate->add(new DateInterval('P' . $period . 'D'));
        }

        $validity = is_null($date) || $date->format(ilPRGProgress::DATE_FORMAT) >= $this->getNow()->format(ilPRGProgress::DATE_FORMAT);
        $this->notifyValidityChange($progress);
        return $progress->withValidityOfQualification($date)
            ->withInvalidated(!$validity);
    }

    protected function updateProgressDeadlineFromSettings(
        ilStudyProgrammeDeadlineSettings $settings,
        ilPRGProgress $progress
    ): ilPRGProgress {
        $period = $settings->getDeadlinePeriod();
        $date = $settings->getDeadlineDate();

        if ($period) {
            $date = $progress->getAssignmentDate();
            $date = $date->add(new DateInterval('P' . $period . 'D'));
        }
        $this->notifyDeadlineChange($progress);
        return $progress->withDeadline($date);
    }

    protected function updateProgressRelevanceFromSettings(
        ilStudyProgrammeSettingsRepository $settings_repo,
        ilPRGProgress $pgs
    ): ilPRGProgress {
        $programme_status = $settings_repo->get($pgs->getNodeId())->getAssessmentSettings()->getStatus();
        $active = $programme_status === ilStudyProgrammeSettings::STATUS_ACTIVE;

        if ($active && !$pgs->isRelevant()) {
            $pgs = $pgs->withStatus(ilPRGProgress::STATUS_IN_PROGRESS);
        }
        if (!$active && $pgs->isInProgress()) {
            $pgs = $pgs->withStatus(ilPRGProgress::STATUS_NOT_RELEVANT);
        }
        return $pgs;
    }


    protected function applyProgressDeadline(
        ilStudyProgrammeSettingsRepository $settings_repo,
        ilPRGProgress $progress,
        int $acting_usr_id = null,
        bool $recalculate = true
    ): ilPRGProgress {
        $today = $this->getNow();
        $format = ilPRGProgress::DATE_FORMAT;
        $deadline = $progress->getDeadline();

        if (is_null($acting_usr_id)) {
            throw new Exception('no acting user.');
            $acting_usr_id = $this->getLoggedInUserId(); //TODO !
        }

        switch ($progress->getStatus()) {
            case ilPRGProgress::STATUS_IN_PROGRESS:
                if (!is_null($deadline)
                    && $deadline->format($format) < $today->format($format)
                ) {
                    $progress = $progress->markFailed($this->getNow(), $acting_usr_id);
                    $this->notifyProgressRevertSuccess($progress);
                } else {
                    $node_settings = $settings_repo->get($progress->getNodeId());
                    $completion_mode = $node_settings->getLPMode();
                    if ($recalculate || $completion_mode !== ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
                        $progress = $this->recalculateProgressStatus($settings_repo, $progress);
                    }
                }
                break;

            case ilPRGProgress::STATUS_FAILED:
                if (is_null($deadline)
                    || $deadline->format($format) >= $today->format($format)
                ) {
                    $progress = $progress->markNotFailed($this->getNow(), $acting_usr_id);
                    $this->notifyProgressRevertSuccess($progress);
                }
                break;
        }

        return $progress;
    }


    protected function resetProgressToSettings(
        ilStudyProgrammeSettingsRepository $settings_repo,
        ilPRGProgress $pgs,
        int $acting_usr_id
    ): ilPRGProgress {
        $settings = $settings_repo->get($pgs->getNodeId());
        if ($pgs->isRelevant()) {
            $pgs = $this->updateProgressValidityFromSettings($settings->getValidityOfQualificationSettings(), $pgs);
            $pgs = $this->updateProgressDeadlineFromSettings($settings->getDeadlineSettings(), $pgs);
        } else {
            $pgs = $pgs
                ->withValidityOfQualification(null)
                ->withDeadline(null);
            $this->notifyValidityChange($pgs);
        }

        $pgs = $pgs
            ->withAmountOfPoints($settings->getAssessmentSettings()->getPoints())
            ->withLastChange($acting_usr_id, $this->getNow())
            ->withIndividualModifications(false);

        if ($pgs->isSuccessful()) {
            $pgs = $pgs->withCurrentAmountOfPoints($pgs->getAmountOfPoints());
            $this->notifyScoreChange($pgs);
        }

        return $pgs;
    }



    // ------------------------- tree-manipulation -----------------------------

    protected function getZipper($node_id)
    {
        $progress_path = $this->getProgressForNode($node_id)->getPath();
        $zipper = new Zipper($this->getProgressTree());
        return $zipper = $zipper->toPath($progress_path);
    }

    protected function notifyProgressSuccess(ilPRGProgress $pgs): void
    {
        $this->getEvents()->userSuccessful($this, $pgs->getNodeId());
    }
    protected function notifyValidityChange(ilPRGProgress $pgs): void
    {
        $this->getEvents()->validityChange($this, $pgs->getNodeId());
    }
    protected function notifyDeadlineChange(ilPRGProgress $pgs): void
    {
        $this->getEvents()->deadlineChange($this, $pgs->getNodeId());
    }
    protected function notifyScoreChange(ilPRGProgress $pgs): void
    {
        $this->getEvents()->scoreChange($this, $pgs->getNodeId());
    }
    protected function notifyProgressRevertSuccess(ilPRGProgress $pgs): void
    {
        $this->getEvents()->userRevertSuccessful($this, $pgs->getNodeId());
    }

    public function initAssignmentDates(): self
    {
        $zipper = $this->getZipper($this->getRootId());
        $zipper = $zipper->modifyAll(
            fn ($pgs) => $pgs->withAssignmentDate($this->getNow())
        );
        return $this->withProgressTree($zipper->getRoot());
    }

    public function resetProgresses(
        ilStudyProgrammeSettingsRepository $settings_repo,
        int $acting_usr_id
    ): self {
        $zipper = $this->getZipper($this->getRootId());
        $zipper = $zipper->modifyAll(
            function ($pgs) use ($acting_usr_id, $settings_repo): ilPRGProgress {
                $pgs = $this->updateProgressRelevanceFromSettings($settings_repo, $pgs);
                $pgs = $this->resetProgressToSettings($settings_repo, $pgs, $acting_usr_id);
                return $pgs;
            }
        );
        return $this->withProgressTree($zipper->getRoot());
    }

    public function markRelevant(
        ilStudyProgrammeSettingsRepository $settings_repo,
        int $node_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ): self {
        $zipper = $this->getZipper($node_id)->modifyFocus(
            function ($pgs) use ($err_collection, $acting_usr_id, $settings_repo): ilPRGProgress {
                if ($pgs->isRelevant()) {
                    $err_collection->add(false, 'will_not_modify_relevant_progress', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }
                $pgs = $pgs->markRelevant($this->getNow(), $acting_usr_id);
                $err_collection->add(true, 'set_to_relevant', $this->getProgressIdString($pgs->getNodeId()));
                $pgs = $this->recalculateProgressStatus($settings_repo, $pgs);
                return $pgs;
            }
        );

        $zipper = $this->updateParentProgresses($settings_repo, $zipper);
        return $this->withProgressTree($zipper->getRoot());
    }

    public function markNotRelevant(
        ilStudyProgrammeSettingsRepository $settings_repo,
        int $node_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ): self {
        $zipper = $this->getZipper($node_id);

        if ($zipper->isTop()) {
            $err_collection->add(false, 'will_not_set_top_progress_to_irrelevant', $this->getProgressIdString($node_id));
            return $this;
        }

        $zipper = $zipper->modifyFocus(
            function ($pgs) use ($err_collection, $acting_usr_id): ilPRGProgress {
                if (!$pgs->isRelevant()) {
                    $err_collection->add(false, 'will_not_modify_irrelevant_progress', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }
                $pgs = $pgs->markNotRelevant($this->getNow(), $acting_usr_id);
                $err_collection->add(true, 'set_to_irrelevant', $this->getProgressIdString($pgs->getNodeId()));
                return $pgs;
            }
        );

        $zipper = $this->updateParentProgresses($settings_repo, $zipper);
        return $this->withProgressTree($zipper->getRoot());
    }

    public function markAccredited(
        ilStudyProgrammeSettingsRepository $settings_repo,
        ilStudyProgrammeEvents $events, //TODO: remove.
        int $node_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ): self {
        $zipper = $this->getZipper($node_id);

        $zipper = $zipper->modifyFocus(
            function ($pgs) use ($err_collection, $acting_usr_id, $settings_repo): ilPRGProgress {
                if (!$pgs->isRelevant()) {
                    $err_collection->add(false, 'will_not_modify_irrelevant_progress', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }

                $new_status = ilPRGProgress::STATUS_ACCREDITED;
                if ($pgs->getStatus() === $new_status) {
                    $err_collection->add(false, 'status_unchanged', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }
                if (!$pgs->isTransitionAllowedTo($new_status)) {
                    $err_collection->add(false, 'status_transition_not_allowed', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }

                $pgs = $pgs
                    ->markAccredited($this->getNow(), $acting_usr_id)
                    ->withCurrentAmountOfPoints($pgs->getAmountOfPoints());
                $this->notifyScoreChange($pgs);

                if (!$pgs->getValidityOfQualification()) {
                    $settings = $settings_repo->get($pgs->getNodeId())->getValidityOfQualificationSettings();
                    $pgs = $this->updateProgressValidityFromSettings($settings, $pgs);
                }

                $this->notifyProgressSuccess($pgs);
                $err_collection->add(true, 'status_changed', $this->getProgressIdString($pgs->getNodeId()));
                return $pgs;
            }
        );

        $zipper = $this->updateParentProgresses($settings_repo, $zipper);
        return $this->withProgressTree($zipper->getRoot());
    }


    public function unmarkAccredited(
        ilStudyProgrammeSettingsRepository $settings_repo,
        int $node_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ): self {
        $zipper = $this->getZipper($node_id);

        $zipper = $zipper->modifyFocus(
            function ($pgs) use ($err_collection, $acting_usr_id, $settings_repo): ilPRGProgress {
                if (!$pgs->isRelevant()) {
                    $err_collection->add(false, 'will_not_modify_irrelevant_progress', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }

                $new_status = ilPRGProgress::STATUS_IN_PROGRESS;
                if ($pgs->getStatus() === $new_status) {
                    $err_collection->add(false, 'status_unchanged', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }
                if (!$pgs->isTransitionAllowedTo($new_status)
                    //special case: completion may not be revoked manually (but might be as a calculation-result of underlying progresses)
                    || $pgs->getStatus() === ilPRGProgress::STATUS_COMPLETED
                ) {
                    $err_collection->add(false, 'status_transition_not_allowed', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }

                $pgs = $pgs
                    ->unmarkAccredited($this->getNow(), $acting_usr_id)
                    ->withCurrentAmountOfPoints($pgs->getAchievedPointsOfChildren());
                $this->notifyScoreChange($pgs);

                $old_status = $pgs->getStatus();
                $pgs = $this->applyProgressDeadline($settings_repo, $pgs, $acting_usr_id);
                if ($pgs->getStatus() !== $old_status) {
                    $err_collection->add(false, 'status_changed_due_to_deadline', $this->getProgressIdString($pgs->getNodeId()));
                } else {
                    $err_collection->add(true, 'status_changed', $this->getProgressIdString($pgs->getNodeId()));
                }
                $this->notifyProgressRevertSuccess($pgs);
                return $pgs;
            }
        );

        $zipper = $this->updateParentProgresses($settings_repo, $zipper);
        return $this->withProgressTree($zipper->getRoot());
    }

    public function updatePlanFromRepository(
        ilStudyProgrammeSettingsRepository $settings_repo,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ): self {
        $zipper = $this->getZipper($this->getRootId());
        $leafs = [];
        $zipper = $zipper->modifyAll(
            function ($pgs) use ($err_collection, $acting_usr_id, $settings_repo, &$leafs): ilPRGProgress {
                $pgs = $this->updateProgressRelevanceFromSettings($settings_repo, $pgs);
                $pgs = $this->resetProgressToSettings($settings_repo, $pgs, $acting_usr_id);
                $pgs = $this->applyProgressDeadline($settings_repo, $pgs, $acting_usr_id, false);
                if (!$pgs->getSubnodes()) {
                    $leafs[] = $pgs->getPath();
                }

                return $pgs;
            }
        );

        foreach ($leafs as $path) {
            $zipper = $this->updateParentProgresses($settings_repo, $zipper->toPath($path));
        }

        return $this->withProgressTree($zipper->getRoot());
    }


    public function succeed(
        ilStudyProgrammeSettingsRepository $settings_repo,
        int $node_id,
        int $triggering_obj_id
    ): self {
        $zipper = $this->getZipper($node_id)->modifyFocus(
            function ($pgs) use ($settings_repo, $triggering_obj_id): ilPRGProgress {
                $deadline = $pgs->getDeadline();
                $format = ilPRGProgress::DATE_FORMAT;
                $now = $this->getNow();
                if ($pgs->isInProgress() &&
                    (is_null($deadline) || $deadline->format($format) >= $now->format($format))
                ) {
                    $pgs = $pgs->succeed($now, $triggering_obj_id)
                        ->withCurrentAmountOfPoints($pgs->getAmountOfPoints());
                    $this->notifyScoreChange($pgs);

                    $settings = $settings_repo->get($pgs->getNodeId());
                    $pgs = $this->updateProgressValidityFromSettings($settings->getValidityOfQualificationSettings(), $pgs);
                }

                $this->notifyProgressSuccess($pgs);
                return $pgs;
            }
        );

        $zipper = $this->updateParentProgresses($settings_repo, $zipper);
        return $this->withProgressTree($zipper->getRoot());
    }


    public function markProgressesFailedForExpiredDeadline(
        ilStudyProgrammeSettingsRepository $settings_repo,
        int $acting_usr_id
    ): self {
        $zipper = $this->getZipper($this->getRootId());
        $touched = [];

        $deadline = $this->getNow();
        $zipper = $zipper->modifyAll(
            function ($pgs) use ($acting_usr_id, $deadline, &$touched): ilPRGProgress {
                if (is_null($pgs->getDeadline())
                    || !$pgs->isInProgress()
                    || $pgs->getDeadline()->format(ilPRGProgress::DATE_FORMAT) >= $deadline->format(ilPRGProgress::DATE_FORMAT)
                ) {
                    return $pgs;
                }

                $touched[] = $pgs->getPath();
                $this->notifyProgressRevertSuccess($pgs);
                return $pgs->markFailed($this->getNow(), $acting_usr_id);
            }
        );

        foreach ($touched as $path) {
            $zipper = $this->updateParentProgresses($settings_repo, $zipper->toPath($path));
        }

        return $this->withProgressTree($zipper->getRoot());
    }

    public function changeProgressDeadline(
        ilStudyProgrammeSettingsRepository $settings_repo,
        int $node_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection,
        ?DateTimeImmutable $deadline
    ): self {
        $zipper = $this->getZipper($node_id)->modifyFocus(
            function ($pgs) use ($err_collection, $acting_usr_id, $settings_repo, $deadline): ilPRGProgress {
                if (!$pgs->isRelevant()) {
                    $err_collection->add(false, 'will_not_modify_irrelevant_progress', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }
                if ($pgs->isSuccessful()) {
                    $err_collection->add(false, 'will_not_modify_deadline_on_successful_progress', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }

                $pgs = $pgs->withDeadline($deadline)
                    ->withLastChange($acting_usr_id, $this->getNow())
                    ->withIndividualModifications(true);
                $pgs = $this->applyProgressDeadline($settings_repo, $pgs, $acting_usr_id);
                if ($pgs->isInProgress()) {
                    $this->notifyDeadlineChange($pgs);
                }
                $err_collection->add(true, 'deadline_updated', $this->getProgressIdString($pgs->getNodeId()));
                return $pgs;
            }
        );

        $zipper = $this->updateParentProgresses($settings_repo, $zipper);
        return $this->withProgressTree($zipper->getRoot());
    }

    public function changeProgressValidityDate(
        ilStudyProgrammeSettingsRepository $settings_repo,
        int $node_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection,
        ?DateTimeImmutable $validity_date
    ): self {
        $zipper = $this->getZipper($node_id)->modifyFocus(
            function ($pgs) use ($err_collection, $acting_usr_id, $settings_repo, $validity_date): ilPRGProgress {
                if (!$pgs->isRelevant()) {
                    $err_collection->add(false, 'will_not_modify_irrelevant_progress', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }
                if (!$pgs->isSuccessful()) {
                    $err_collection->add(false, 'will_not_modify_validity_on_non_successful_progress', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }

                $validity = is_null($validity_date) || $validity_date->format(ilPRGProgress::DATE_FORMAT) >= $this->getNow()->format(ilPRGProgress::DATE_FORMAT);
                $pgs = $pgs->withValidityOfQualification($validity_date)
                    ->withLastChange($acting_usr_id, $this->getNow())
                    ->withIndividualModifications(true)
                    ->withInvalidated(!$validity);

                $this->notifyValidityChange($pgs);
                $err_collection->add(true, 'validity_updated', $this->getProgressIdString($pgs->getNodeId()));
                return $pgs;
            }
        );

        //$zipper = $this->updateParentProgresses($settings_repo, $zipper);
        return $this->withProgressTree($zipper->getRoot());
    }

    public function changeAmountOfPoints(
        ilStudyProgrammeSettingsRepository $settings_repo,
        int $node_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection,
        int $points
    ): self {
        $zipper = $this->getZipper($node_id)->modifyFocus(
            function ($pgs) use ($err_collection, $acting_usr_id, $settings_repo, $points): ilPRGProgress {
                if (!$pgs->isRelevant()) {
                    $err_collection->add(false, 'will_not_modify_irrelevant_progress', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }
                if ($pgs->isSuccessful()) {
                    $err_collection->add(false, 'will_not_modify_successful_progress', $this->getProgressIdString($pgs->getNodeId()));
                    return $pgs;
                }

                $pgs = $pgs->withAmountOfPoints($points)
                    ->withLastChange($acting_usr_id, $this->getNow())
                    ->withIndividualModifications(true);

                $err_collection->add(true, 'required_points_updated', $this->getProgressIdString($pgs->getNodeId()));
                $pgs = $this->recalculateProgressStatus($settings_repo, $pgs);
                return $pgs;
            }
        );

        $zipper = $this->updateParentProgresses($settings_repo, $zipper);
        return $this->withProgressTree($zipper->getRoot());
    }

    public function invalidate(
        ilStudyProgrammeSettingsRepository $settings_repo
    ): self {
        $zipper = $this->getZipper($this->getRootId());
        $touched = [];
        $now = $this->getNow();

        $zipper = $zipper->modifyAll(
            function ($pgs) use ($now, &$touched): ilPRGProgress {
                if (!$pgs->isSuccessful() || $pgs->hasValidQualification($now)) {
                    return $pgs;
                }
                $touched[] = $pgs->getPath();
                return $pgs->invalidate();
            }
        );

        foreach ($touched as $path) {
            $zipper = $this->updateParentProgresses($settings_repo, $zipper->toPath($path));
        }

        return $this->withProgressTree($zipper->getRoot());
    }
}
