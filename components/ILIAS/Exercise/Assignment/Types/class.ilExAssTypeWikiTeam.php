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

use ILIAS\Wiki\Export\WikiHtmlExport;

/**
 * Team wiki type
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssTypeWikiTeam implements ilExAssignmentTypeInterface
{
    protected const STR_IDENTIFIER = "wiki";

    protected ilLanguage $lng;

    /**
     * Constructor
     *
     * @param ilLanguage|null $a_lng
     */
    public function __construct(ilLanguage $a_lng = null)
    {
        global $DIC;

        $this->lng = ($a_lng)
            ?: $DIC->language();
    }

    public function isActive(): bool
    {
        return true;
    }

    public function usesTeams(): bool
    {
        return true;
    }

    public function usesFileUpload(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("wiki");
        return $lng->txt("wiki_type_wiki_team");
    }

    public function getSubmissionType(): string
    {
        return ilExSubmission::TYPE_REPO_OBJECT;
    }

    public function isSubmissionAssignedToTeam(): bool
    {
        return true;
    }

    /**
     * Submit wiki
     *
     * @throws ilTemplateException
     * @throws ilWikiExportException
     */
    public function submitWiki(int $a_ass_id, int $a_user_id, int $a_wiki_ref_id): void
    {
        $ass = new ilExAssignment($a_ass_id);
        $submission = new ilExSubmission($ass, $a_user_id);

        if (!$submission->canSubmit()) {
            return;
        }

        $wiki = new ilObjWiki($a_wiki_ref_id);
        $exp = new WikiHtmlExport($wiki);
        //$exp->setMode(ilWikiHTMLExport::MODE_USER);
        $file = $exp->buildExportFile();

        $size = filesize($file);
        if ($size) {
            $submission->deleteAllFiles();

            $meta = array(
                "name" => $a_wiki_ref_id . ".zip",
                "tmp_name" => $file,
                "size" => $size
            );
            $submission->uploadFile($meta, true);

            // print version
            $file = $file = $exp->buildExportFile(true);
            $size = filesize($file);
            if ($size) {
                $meta = array(
                    "name" => $a_wiki_ref_id . "print.zip",
                    "tmp_name" => $file,
                    "size" => $size
                );
                $submission->uploadFile($meta, true);
            }

            $this->handleNewUpload($ass, $submission);
        }
    }

    // @todo move to trait
    protected function handleNewUpload(
        ilExAssignment $ass,
        ilExSubmission $submission,
        $a_no_notifications = false
    ): void {
        $has_submitted = $submission->hasSubmitted();

        // we need one ref id here
        $exc_ref_ids = ilObject::_getAllReferences($ass->getExerciseId());
        $exc_ref_id = current($exc_ref_ids);

        $exc = new ilObjExercise($ass->getExerciseId(), false);

        $exc->processExerciseStatus(
            $ass,
            $submission->getUserIds(),
            $has_submitted,
            $submission->validatePeerReviews()
        );

        if ($has_submitted &&
            !$a_no_notifications) {
            $users = ilNotification::getNotificationsForObject(
                ilNotification::TYPE_EXERCISE_SUBMISSION,
                $exc->getId()
            );

            $not = new ilExerciseMailNotification();
            $not->setType(ilExerciseMailNotification::TYPE_SUBMISSION_UPLOAD);
            $not->setAssignmentId($ass->getId());
            $not->setRefId($exc_ref_id);
            $not->setRecipients($users);
            $not->send();
        }
    }

    public function cloneSpecificProperties(
        ilExAssignment $source,
        ilExAssignment $target
    ): void {
        $source_ar = new ilExAssWikiTeamAR($source->getId());
        $target_ar = new ilExAssWikiTeamAR();
        $target_ar->setId($target->getId());
        $target_ar->setTemplateRefId($source_ar->getTemplateRefId());
        $target_ar->setContainerRefId($source_ar->getContainerRefId());
        $target_ar->save();
    }

    public function supportsWebDirAccess(): bool
    {
        return true;
    }

    public function getStringIdentifier(): string
    {
        return self::STR_IDENTIFIER;
    }

    // In case of wikis we get the ref id as resource id
    public function getExportObjIdForResourceId(int $resource_id): int
    {
        return ilObject::_lookupObjectId($resource_id);
    }
}
