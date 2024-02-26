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

use ILIAS\Exercise\InternalRepoService;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\FileUpload\DTO\UploadResult;

class TutorFeedbackZipManager
{
    protected \ilExcTutorFeedbackFileStakeholder $user_feedback_stakeholder;
    protected TutorFeedbackFileRepository $participant_repo;
    protected \ilExcTutorFeedbackZipStakeholder $stakeholder;
    protected int $ass_id;
    protected InternalDomainService $domain;
    protected TutorFeedbackZipRepository $repo;

    public function __construct(
        InternalRepoService $repo,
        InternalDomainService $domain,
        \ilExcTutorFeedbackZipStakeholder $stakeholder,
        \ilExcTutorFeedbackFileStakeholder $user_feedback_stakeholder
    ) {
        $this->domain = $domain;
        $this->repo = $repo->tutorFeedbackZip();
        $this->stakeholder = $stakeholder;
        $this->user_feedback_stakeholder = $user_feedback_stakeholder;
        $this->participant_repo = $repo->tutorFeedbackFile();
    }

    protected function toAscii(string $filename): string
    {
        global $DIC;
        return (new \ilFileServicesPolicy($DIC->fileServiceSettings()))->ascii($filename);
    }

    public function getMultiFeedbackStructureFile(
        \ilExAssignment $assignment,
        \ilObjExercise $exercise
    ): object {
        $access = $this->domain->access();
        $exmem = new \ilExerciseMembers($exercise);

        $tmp_file = tmpfile();
        $tmp_location = stream_get_meta_data($tmp_file)['uri'];
        $zip = new \ZipArchive();
        $res = $zip->open($tmp_location, \ZipArchive::OVERWRITE);

        // send and delete the zip file
        $base_name = trim(str_replace(" ", "_", $assignment->getTitle() . "_" . $assignment->getId()));
        $base_name = "multi_feedback_" . $this->toAscii($base_name);

        // create subfolders <lastname>_<firstname>_<id> for each participant
        $mems = $exmem->getMembers();

        $mems = $access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'edit_submissions_grades',
            'edit_submissions_grades',
            $exercise->getRefId(),
            $mems
        );
        foreach ($mems as $mem) {
            $name = \ilObjUser::_lookupName($mem);
            $subdir = $name["lastname"] . "_" . $name["firstname"] . "_" . $name["login"] . "_" . $name["user_id"];
            $subdir = $this->toAscii($subdir);
            $zip->addEmptyDir($base_name . "/" . $subdir);
        }
        $zip->close();
        $ret = new \StdClass();
        $ret->content = file_get_contents($tmp_location);
        $ret->filename = $base_name . ".zip";
        return $ret;
    }

    public function importFromUploadResult(
        int $ass_id,
        int $tutor_id,
        UploadResult $result
    ): string {
        $this->repo->deleteCurrent($ass_id, $tutor_id, $this->stakeholder);
        return $this->repo->importFromUploadResult(
            $ass_id,
            $tutor_id,
            $result,
            $this->stakeholder
        );
    }

    public function getFiles(\ilObjExercise $exc, int $ass_id, int $tutor_id): array
    {
        $exmem = new \ilExerciseMembers($exc);
        $mems = $exmem->getMembers();

        return $this->repo->getFiles($ass_id, $tutor_id, $mems);
    }

    public function getFileMd5(int $user_id, string $filename): string
    {
        return md5($user_id . ":" . $filename);
    }

    public function saveMultiFeedbackFiles(
        \ilObjExercise $exc,
        int $ass_id,
        int $tutor_id,
        array $file_md5s
    ) {
        $notification = $this->domain->notification($exc->getRefId());
        foreach ($this->getFiles($exc, $ass_id, $tutor_id) as $file) {
            $user_id = (int) $file["user_id"];
            $md5 = $this->getFileMd5((int) $file["user_id"], $file["file"]);
            if (is_array($file_md5s[$user_id] ?? null) && in_array($md5, $file_md5s[$user_id])) {
                $target_collection = $this->participant_repo->getCollection($ass_id, $user_id);
                if ($target_collection) {
                    $this->repo->addFileFromZipToCollection(
                        $ass_id,
                        $tutor_id,
                        $file["full_entry"],
                        $target_collection,
                        $this->user_feedback_stakeholder
                    );
                }

                $ass = new \ilExAssignment($ass_id);
                $submission = new \ilExSubmission($ass, $user_id);
                $feedback_id = $submission->getFeedbackId();
                $noti_rec_ids = $submission->getUserIds();

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
                            $file["file"]
                        );
                    }
                }
            }
        }
    }

}
