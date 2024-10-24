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

    protected function getStakeholder(): ResourceStakeholder
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
        }
    }

    public function getFilename(): string
    {
        return $this->repo->getFilename($this->ass_id);
    }

    public function cloneTo(
        int $to_ass_id
    ): void {
        // IRSS
        if ($this->repo->hasFile($this->ass_id)) {
            $this->repo->clone($this->ass_id, $to_ass_id);
        }
    }

}
