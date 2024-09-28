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

namespace ILIAS\Exercise\TutorFeedbackFile;

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Exercise\IRSS\ResourceInformation;
use ILIAS\Exercise\InstructionFile\ilFSWebStorageExercise;
use ILIAS\Exercise\InstructionFile\InstructionFileRepository;
use ILIAS\Exercise\InternalRepoService;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\ResourceStorage\Events\Event;

class TutorFeedbackFileManager
{
    protected TutorFeedbackFileObserver $file_observer;
    protected int $ass_id;
    protected \ILIAS\FileUpload\FileUpload $upload;
    protected \ilExAssignmentTypeInterface $type;
    protected InternalDomainService $domain;
    protected TutorFeedbackFileRepositoryInterface $repo;
    protected ResourceStakeholder $stakeholder;

    public function __construct(
        int $ass_id,
        InternalRepoService $repo,
        InternalDomainService $domain,
        \ilExcTutorFeedbackFileStakeholder $stakeholder,
        \ilExcTutorTeamFeedbackFileStakeholder $team_stakeholder,
        TutorFeedbackFileObserver $file_observer
    ) {
        global $DIC;

        $this->upload = $DIC->upload();

        $this->file_observer = $file_observer;
        $types = \ilExAssignmentTypes::getInstance();
        $this->type = $types->getById(\ilExAssignment::lookupType($ass_id));
        if ($this->type->usesTeams()) {
            $this->repo = $repo->tutorFeedbackFileTeam();
            $this->stakeholder = $team_stakeholder;
        } else {
            $this->repo = $repo->tutorFeedbackFile();
            $this->stakeholder = $stakeholder;
        }
        $this->domain = $domain;
        $this->ass_id = $ass_id;
    }

    public function getStakeholder(): ResourceStakeholder
    {
        return $this->stakeholder;
    }

    public function addObserver(): void
    {
        $this->domain->resourceStorage()->events()->attach(
            $this->file_observer,
            Event::COLLECTION_RESOURCE_ADDED
        );
    }

    public function sendNotification(string $rcid, string $rid): void
    {
        $log = $this->domain->log();
        $log->debug("Ass id: " . $this->ass_id);

        $exc_id = \ilExAssignment::lookupExerciseId($this->ass_id);
        $ref_id = (int) current(\ilObject::_getAllReferences($exc_id));

        $log->debug("Ref id: " . $ref_id);

        if ($ref_id === 0) {
            return;
        }
        $log->debug("Get notification");
        $notification = $this->domain->notification($ref_id);
        $log->debug("Get participant");
        $part_id = $this->repo->getParticipantIdForRcid($this->ass_id, $rcid);
        $log->debug("Get filename");
        $filename = $this->repo->getFilenameForRid($this->ass_id, $part_id, $rid);
        $log->debug("Get assignment");
        $ass = new \ilExAssignment($this->ass_id);
        $log->debug("Get submission");
        $submission = new \ilExSubmission($ass, $part_id);
        $feedback_id = $submission->getFeedbackId();
        $noti_rec_ids = $submission->getUserIds();

        $log->debug("Feedback id: " . $feedback_id);
        if ($feedback_id) {
            if ($noti_rec_ids) {
                foreach ($noti_rec_ids as $user_id) {
                    $member_status = $ass->getMemberStatus($user_id);
                    $member_status->setFeedback(true);
                    $member_status->update();
                }

                $notification->sendFeedbackNotification(
                    $ass->getId(),
                    $noti_rec_ids,
                    $filename
                );
            }
        }
    }

    public function hasCollection(int $participant_id): bool
    {
        return ($this->getCollectionIdString($participant_id) !== "");
    }

    protected function getLegacyFeedbackId(int $participant_id): string
    {
        if ($this->type->usesTeams()) {
            $team_id = $this->domain->team()->getTeamForMember($this->ass_id, $participant_id);
            if (is_null($team_id)) {
                throw new \ilExerciseException("Team for user " . $participant_id . " in assignment " . $this->ass_id . " not found.");
            }
            return "t" . $team_id;
        } else {
            return (string) $participant_id;
        }
    }

    public function count(int $participant_id): int
    {
        if ($this->hasCollection($participant_id)) {
            // IRSS
            return $this->repo->count($this->ass_id, $participant_id);
        }
        // LEGACY
        $exc_id = \ilExAssignment::lookupExerciseId($this->ass_id);
        try {
            $storage = new \ilFSStorageExercise($exc_id, $this->ass_id);
            return $storage->countFeedbackFiles($this->getLegacyFeedbackId($participant_id));
        } catch (\ilExerciseException $e) {
        }
        return 0;
    }

    public function getFeedbackTitle(int $participant_id): string
    {
        $title = $this->domain->lng()->txt('exc_fb_files');
        if ($this->type->usesTeams()) {
            $name = \ilObjUser::_lookupName($participant_id);
            return $title . ": " . $name["lastname"] . ", " . $name["firstname"];
        } else {
            $name = \ilObjUser::_lookupName($participant_id);
            return $title . ": " . $name["lastname"] . ", " . $name["firstname"];
        }
    }

    public function getCollectionIdString(int $participant_id): string
    {
        return $this->repo->getIdStringForAssIdAndUserId($this->ass_id, $participant_id);
    }

    public function createCollection(int $participant_id): void
    {
        $this->repo->createCollection($this->ass_id, $participant_id);
    }

    public function deleteCollection(int $participant_id): void
    {
        $this->repo->deleteCollection(
            $this->ass_id,
            $participant_id,
            $this->stakeholder
        );
    }

    public function getFiles(int $participant_id): array
    {
        $files = [];
        if ($this->repo->hasCollection($this->ass_id, $participant_id)) {
            $files = array_map(function (ResourceInformation $info): string {
                return $info->getTitle();
            }, iterator_to_array($this->repo->getCollectionResourcesInfo($this->ass_id, $participant_id)));
        } else {
            $exc_id = \ilExAssignment::lookupExerciseId($this->ass_id);
            $storage = new \ilFSStorageExercise($exc_id, $this->ass_id);
            $files = $storage->getFeedbackFiles($this->getLegacyFeedbackId($participant_id));
        }
        return $files;
    }

    public function deliver(int $participant_id, string $file): void
    {
        $assignment = $this->domain->assignment()->getAssignment($this->ass_id);
        if ($assignment->notStartedYet()) {
            return;
        }

        if ($this->repo->hasCollection($this->ass_id, $participant_id)) {
            // IRSS
            $this->repo->deliverFile($this->ass_id, $participant_id, $file);
        } else {
            // LEGACY
            $exc_id = \ilExAssignment::lookupExerciseId($this->ass_id);
            $storage = new \ilFSStorageExercise($exc_id, $this->ass_id);
            $files = $storage->getFeedbackFiles($this->getLegacyFeedbackId($participant_id));
            $file_exist = false;
            foreach ($files as $fb_file) {
                if ($fb_file === $file) {
                    $file_exist = true;
                    break;
                }
            }
            if (!$file_exist) {
                return;
            }
            // deliver file
            $p = $storage->getFeedbackFilePath($this->getLegacyFeedbackId($participant_id), $file);
            \ilFileDelivery::deliverFileLegacy($p, $file);
        }
    }

}
