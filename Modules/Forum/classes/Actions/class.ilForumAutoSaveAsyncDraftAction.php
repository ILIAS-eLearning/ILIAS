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

/**
 * Class ilForumSaveAsyncDraftAction
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumAutoSaveAsyncDraftAction
{
    private ilObjUser $actor;
    private ilPropertyFormGUI $form;
    private ilForumProperties $forumProperties;
    private ilForumTopic $thread;
    private ?ilForumPost $relatedPost;
    private Closure $subjectFormatterCallable;
    private int $relatedDraftId;
    private int $relatedForumId;
    private string $action;

    public function __construct(
        ilObjUser $actor,
        ilPropertyFormGUI $form,
        ilForumProperties $forumProperties,
        ilForumTopic $thread,
        ?ilForumPost $relatedPost,
        Closure $subjectFormatterCallable,
        int $relatedDraftId,
        int $relatedForumId,
        string $action
    ) {
        $this->actor = $actor;
        $this->form = $form;
        $this->forumProperties = $forumProperties;
        $this->thread = $thread;
        $this->relatedPost = $relatedPost;
        $this->subjectFormatterCallable = $subjectFormatterCallable;

        $this->relatedDraftId = $relatedDraftId;
        $this->relatedForumId = $relatedForumId;
        $this->action = $action;
    }

    public function executeAndGetResponseObject(): stdClass
    {
        $response = new stdClass();
        $response->draft_id = 0;

        if ($this->actor->isAnonymous() || !($this->actor->getId() > 0)) {
            return $response;
        }

        if ($this->thread->getId() > 0 && $this->thread->isClosed()) {
            return $response;
        }

        if (!ilForumPostDraft::isAutoSavePostDraftAllowed()) {
            return $response;
        }

        if (
            $this->relatedPost instanceof ilForumPost && (
                !$this->relatedPost->isActivated() || $this->relatedPost->isCensored()
            )
        ) {
            return $response;
        }

        $relatedPostId = 0;
        if ($this->relatedPost instanceof ilForumPost) {
            $relatedPostId = $this->relatedPost->getId();
        }

        $this->form->checkInput();
        $inputValues = $this->getInputValuesFromForm();

        if ($this->relatedDraftId > 0) {
            $draftId = $this->relatedDraftId;
        } else {
            $draftId = (int) $this->form->getInput('draft_id');
        }

        $subjectFormatterCallback = $this->subjectFormatterCallable;

        if ($draftId > 0) {
            if ('showreply' === $this->action) {
                $draftObj = ilForumPostDraft::newInstanceByDraftId($draftId);
                $draftObj->setPostSubject($subjectFormatterCallback($inputValues['subject']));
                $draftObj->setPostMessage(ilRTE::_replaceMediaObjectImageSrc($inputValues['message'], 0));
                $draftObj->setPostUserAlias($inputValues['alias']);
                $draftObj->setNotificationStatus($inputValues['notify']);
                $draftObj->setUpdateUserId($this->actor->getId());
                $draftObj->setPostAuthorId($this->actor->getId());
                $draftObj->setPostDisplayUserId(($this->forumProperties->isAnonymized() ? 0 : $this->actor->getId()));
                $draftObj->updateDraft();

                $uploadedObjects = ilObjMediaObject::_getMobsOfObject('frm~:html', $this->actor->getId());
                $oldMediaObjects = ilObjMediaObject::_getMobsOfObject('frm~d:html', $draftObj->getDraftId());
                $curMediaObjects = ilRTE::_getMediaObjects($inputValues['message'], 0);

                $this->handleMedia(
                    ilForumPostDraft::MEDIAOBJECT_TYPE,
                    $draftObj->getDraftId(),
                    $uploadedObjects,
                    $oldMediaObjects,
                    $curMediaObjects
                );
            } else {
                $draftObj = new ilForumDraftsHistory();
                $draftObj->setDraftId($draftId);
                $draftObj->setPostSubject($subjectFormatterCallback($inputValues['subject']));
                $draftObj->setPostMessage(ilRTE::_replaceMediaObjectImageSrc($inputValues['message'], 0));
                $draftObj->addDraftToHistory();

                $uploadedObjects = ilObjMediaObject::_getMobsOfObject('frm~:html', $this->actor->getId());
                $oldMediaObjects = ilObjMediaObject::_getMobsOfObject('frm~d:html', $draftObj->getDraftId());
                $curMediaObjects = ilRTE::_getMediaObjects($inputValues['message'], 0);

                $this->handleMedia(
                    ilForumDraftsHistory::MEDIAOBJECT_TYPE,
                    $draftObj->getHistoryId(),
                    $uploadedObjects,
                    $oldMediaObjects,
                    $curMediaObjects
                );
            }
        } else {
            $draftObj = new ilForumPostDraft();
            $draftObj->setForumId($this->relatedForumId);
            $draftObj->setThreadId($this->thread->getId());
            $draftObj->setPostId($relatedPostId);
            $draftObj->setPostSubject($subjectFormatterCallback($inputValues['subject']));
            $draftObj->setPostMessage(ilRTE::_replaceMediaObjectImageSrc($inputValues['message'], 0));
            $draftObj->setPostUserAlias($inputValues['alias']);
            $draftObj->setNotificationStatus($inputValues['notify']);
            $draftObj->setPostAuthorId($this->actor->getId());
            $draftObj->setPostDisplayUserId(($this->forumProperties->isAnonymized() ? 0 : $this->actor->getId()));
            $draftObj->saveDraft();

            $uploadedObjects = ilObjMediaObject::_getMobsOfObject('frm~:html', $this->actor->getId());
            $oldMediaObjects = ilObjMediaObject::_getMobsOfObject('frm~d:html', $draftObj->getDraftId());
            $curMediaObjects = ilRTE::_getMediaObjects($inputValues['message'], 0);

            $this->handleMedia(
                ilForumPostDraft::MEDIAOBJECT_TYPE,
                $draftObj->getDraftId(),
                $uploadedObjects,
                $oldMediaObjects,
                $curMediaObjects
            );
        }

        $response->draft_id = $draftObj->getDraftId();

        return $response;
    }

    /**
     * @param string $type
     * @param int $draftId
     * @param int[] $uploadedObjects
     * @param int[] $oldMediaObjects
     * @param int[] $curMediaObjects
     */
    protected function handleMedia(
        string $type,
        int $draftId,
        array $uploadedObjects,
        array $oldMediaObjects,
        array $curMediaObjects
    ): void {
        foreach ($uploadedObjects as $mob) {
            ilObjMediaObject::_removeUsage($mob, 'frm~:html', $this->actor->getId());
            ilObjMediaObject::_saveUsage($mob, $type, $draftId);
        }

        foreach ($oldMediaObjects as $mob) {
            ilObjMediaObject::_saveUsage($mob, $type, $draftId);
        }

        foreach ($curMediaObjects as $mob) {
            ilObjMediaObject::_saveUsage($mob, $type, $draftId);
        }
    }

    protected function getInputValuesFromForm(): array
    {
        $inputValues = [];

        $inputValues['subject'] = $this->form->getInput('subject');
        $inputValues['message'] = $this->form->getInput('message');
        $inputValues['notify'] = (int) $this->form->getInput('notify');
        $inputValues['alias'] = ilForumUtil::getPublicUserAlias(
            $this->form->getInput('alias'),
            $this->forumProperties->isAnonymized()
        );

        return $inputValues;
    }
}
