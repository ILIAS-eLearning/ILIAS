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

namespace ILIAS\Exercise\InstructionFile;

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Exercise\IRSS\ResourceInformation;

class InstructionFileManager
{
    protected \ilExcInstructionFilesStakeholder $stakeholder;
    protected \ILIAS\FileUpload\FileUpload $upload;
    protected int $ass_id;
    protected InstructionFileRepository $repo;

    public function __construct(
        int $ass_id,
        InstructionFileRepository $repo,
        \ilExcInstructionFilesStakeholder $stakeholder
    ) {
        global $DIC;

        $this->upload = $DIC->upload();

        $this->repo = $repo;
        $this->ass_id = $ass_id;
        $this->stakeholder = $stakeholder;
    }

    public function getStakeholder(): ResourceStakeholder
    {
        return $this->stakeholder;
    }

    public function getCollectionIdString(): string
    {
        return $this->repo->getIdStringForAssId($this->ass_id);
    }

    public function createCollection(): void
    {
        $this->repo->createCollection($this->ass_id);
    }

    public function importFromLegacyUpload(array $file_input): void
    {
        $this->repo->importFromLegacyUpload(
            $this->ass_id,
            $file_input,
            $this->stakeholder
        );
    }

    public function deleteCollection(): void
    {
        $this->repo->deleteCollection(
            $this->ass_id,
            $this->stakeholder
        );
    }

    public function getFiles(): array
    {
        if ($this->repo->hasCollection($this->ass_id)) {
            return array_map(function (ResourceInformation $info): array {
                return [
                    'rid' => $info->getRid(),
                    'name' => $info->getTitle(),
                    'size' => $info->getSize(),
                    'ctime' => $info->getCreationTimestamp(),
                    'fullpath' => $info->getSrc(),
                    'mime' => $info->getMimeType(), // this is additional to still use the image delivery in class.ilExAssignmentGUI.php:306
                    'order' => 0 // sorting is currently not supported
                ];
            }, iterator_to_array($this->repo->getCollectionResourcesInfo($this->ass_id)));
        } else {
            $exc_id = \ilExAssignment::lookupExerciseId($this->ass_id);
            $storage = new \ilFSWebStorageExercise($exc_id, $this->ass_id);
            return $storage->getFiles();
        }
    }

    public function deliver(string $full_path, string $file): void
    {
        if ($this->repo->hasCollection($this->ass_id)) {
            $this->repo->deliverFile($this->ass_id, $file);
        } else {
            // deliver file
            \ilFileDelivery::deliverFileLegacy($full_path, $file);
            exit();
        }
    }

    public function cloneTo(
        int $to_ass_id
    ): void {
        $this->repo->clone($this->ass_id, $to_ass_id);
    }
}
