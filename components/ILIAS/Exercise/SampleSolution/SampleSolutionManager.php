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

namespace ILIAS\Exercise\SampleSolution;

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Exercise\IRSS\ResourceInformation;
use ILIAS\Exercise\InternalDomainService;

class SampleSolutionManager
{
    protected \ilExcSampleSolutionStakeholder $stakeholder;
    protected int $ass_id;
    protected \ILIAS\FileUpload\FileUpload $upload;
    protected InternalDomainService $domain;
    protected SampleSolutionRepository $repo;

    public function __construct(
        int $ass_id,
        SampleSolutionRepository $repo,
        \ilExcSampleSolutionStakeholder $stakeholder,
        InternalDomainService $domain
    ) {
        global $DIC;

        $this->upload = $DIC->upload();

        $this->repo = $repo;
        $this->ass_id = $ass_id;
        $this->stakeholder = $stakeholder;
        $this->domain = $domain;
    }

    public function getStakeholder(): ResourceStakeholder
    {
        return $this->stakeholder;
    }


    public function importFromLegacyUpload(array $file_input): string
    {
        if (!isset($file_input["tmp_name"])) {
            return "";
        }
        return $this->repo->importFromLegacyUpload(
            $this->ass_id,
            $file_input,
            $this->stakeholder
        );
    }

    public function deliver(): void
    {
        if ($this->repo->hasFile($this->ass_id)) {
            $this->repo->deliverFile($this->ass_id);
        } else {
            $ass = $this->domain->assignment()->getAssignment($this->ass_id);
            \ilFileDelivery::deliverFileLegacy(
                $ass->getGlobalFeedbackFilePath(),
                $ass->getFeedbackFile()
            );
        }
    }

    public function cloneTo(
        int $to_ass_id
    ): void {
        // IRSS
        if ($this->repo->hasFile($this->ass_id)) {
            $this->repo->clone($this->ass_id, $to_ass_id);
        } else { // NO IRSS
            $old_exc_id = \ilExAssignment::lookupExerciseId($this->ass_id);
            $new_exc_id = \ilExAssignment::lookupExerciseId($to_ass_id);

            $old_storage = new \ilFSStorageExercise($old_exc_id, $this->ass_id);
            $new_storage = new \ilFSStorageExercise($new_exc_id, $to_ass_id);
            $new_storage->create();
            if (is_dir($old_storage->getGlobalFeedbackPath())) {
                \ilFileUtils::rCopy($old_storage->getGlobalFeedbackPath(), $new_storage->getGlobalFeedbackPath());
            }
        }
    }

}
