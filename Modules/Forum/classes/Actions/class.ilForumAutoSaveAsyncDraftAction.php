<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumSaveAsyncDraftAction
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumAutoSaveAsyncDraftAction
{
    /** @var \ilObjUser */
    private $actor;

    /** @var \ilPropertyFormGUI */
    private $form;

    /** @var \ilForumProperties */
    private $forumProperties;

    /** @var \ilForumTopic */
    private $thread;

    /** @var \ilForumPost|null */
    private $relatedPost;
    
    /** @var callable */
    private $subjectFormatterCallable;

    /** @var int */
    private $relatedDraftId = 0;

    /** @var int */
    private $relatedForumId;

    /** @var string */
    private $action = '';

    /**
     * ilForumAutoSaveAsyncDraftAction constructor.
     * @param \ilObjUser         $actor
     * @param \ilPropertyFormGUI $form
     * @param \ilForumProperties $forumProperties
     * @param \ilForumTopic      $thread
     * @param \ilForumPost|null  $relatedPost
     * @param callable           $subjectFormatterCallable
     * @param int                $relatedDraftId
     * @param int                $relatedForumId
     * @param string             $action
     */
    public function __construct(
        \ilObjUser $actor,
        \ilPropertyFormGUI $form,
        \ilForumProperties $forumProperties,
        \ilForumTopic $thread,
        $relatedPost,
        callable $subjectFormatterCallable,
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

    /**
     * @return \stdClass
     */
    public function executeAndGetResponseObject() : \stdClass
    {
        $response = new \stdClass();
        $response->draft_id = 0;

        if ($this->actor->isAnonymous() || !($this->actor->getId() > 0)) {
            return $response;
        }

        if ($this->thread->getId() > 0 && $this->thread->isClosed()) {
            return $response;
        }

        if (!\ilForumPostDraft::isAutoSavePostDraftAllowed()) {
            return $response;
        }

        if (
            $this->relatedPost instanceof \ilForumPost && (
                !$this->relatedPost->isActivated() || $this->relatedPost->isCensored()
            )
        ) {
            return $response;
        }

        $relatedPostId = 0;
        if ($this->relatedPost instanceof \ilForumPost) {
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
                $draftObj = \ilForumPostDraft::newInstanceByDraftId($draftId);
                $draftObj->setPostSubject($subjectFormatterCallback($inputValues['subject']));
                $draftObj->setPostMessage(\ilRTE::_replaceMediaObjectImageSrc($inputValues['message'], 0));
                $draftObj->setPostUserAlias($inputValues['alias']);
                $draftObj->setNotify($inputValues['notify']);
                $draftObj->setUpdateUserId($this->actor->getId());
                $draftObj->setPostAuthorId($this->actor->getId());
                $draftObj->setPostDisplayUserId(($this->forumProperties->isAnonymized() ? 0 : $this->actor->getId()));
                $draftObj->updateDraft();

                $uploadedObjects = \ilObjMediaObject::_getMobsOfObject('frm~:html', $this->actor->getId());
                $oldMediaObjects = \ilObjMediaObject::_getMobsOfObject('frm~d:html', $draftObj->getDraftId());
                $curMediaObjects = \ilRTE::_getMediaObjects($inputValues['message'], 0);

                $this->handleMedia(
                    \ilForumPostDraft::MEDIAOBJECT_TYPE,
                    $draftObj->getDraftId(),
                    $uploadedObjects,
                    $oldMediaObjects,
                    $curMediaObjects
                );
            } else {
                $draftObj = new \ilForumDraftsHistory();
                $draftObj->setDraftId($draftId);
                $draftObj->setPostSubject($subjectFormatterCallback($inputValues['subject']));
                $draftObj->setPostMessage(\ilRTE::_replaceMediaObjectImageSrc($inputValues['message'], 0));
                $draftObj->addDraftToHistory();

                $uploadedObjects = \ilObjMediaObject::_getMobsOfObject('frm~:html', $this->actor->getId());
                $oldMediaObjects = \ilObjMediaObject::_getMobsOfObject('frm~d:html', $draftObj->getDraftId());
                $curMediaObjects = \ilRTE::_getMediaObjects($inputValues['message'], 0);

                $this->handleMedia(
                    \ilForumDraftsHistory::MEDIAOBJECT_TYPE,
                    $draftObj->getHistoryId(),
                    $uploadedObjects,
                    $oldMediaObjects,
                    $curMediaObjects
                );
            }
        } else {
            $draftObj = new \ilForumPostDraft();
            $draftObj->setForumId($this->relatedForumId);
            $draftObj->setThreadId($this->thread->getId());
            $draftObj->setPostId($relatedPostId);
            $draftObj->setPostSubject($subjectFormatterCallback($inputValues['subject']));
            $draftObj->setPostMessage(\ilRTE::_replaceMediaObjectImageSrc($inputValues['message'], 0));
            $draftObj->setPostUserAlias($inputValues['alias']);
            $draftObj->setNotify($inputValues['notify']);
            $draftObj->setPostAuthorId($this->actor->getId());
            $draftObj->setPostDisplayUserId(($this->forumProperties->isAnonymized() ? 0 : $this->actor->getId()));
            $draftObj->saveDraft();

            $uploadedObjects = \ilObjMediaObject::_getMobsOfObject('frm~:html', $this->actor->getId());
            $oldMediaObjects = \ilObjMediaObject::_getMobsOfObject('frm~d:html', $draftObj->getDraftId());
            $curMediaObjects = \ilRTE::_getMediaObjects($inputValues['message'], 0);

            $this->handleMedia(
                \ilForumPostDraft::MEDIAOBJECT_TYPE,
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
    ) {
        foreach ($uploadedObjects as $mob) {
            \ilObjMediaObject::_removeUsage($mob, 'frm~:html', $this->actor->getId());
            \ilObjMediaObject::_saveUsage($mob, $type, $draftId);
        }

        foreach ($oldMediaObjects as $mob) {
            \ilObjMediaObject::_saveUsage($mob, $type, $draftId);
        }

        foreach ($curMediaObjects as $mob) {
            \ilObjMediaObject::_saveUsage($mob, $type, $draftId);
        }
    }

    /**
     * @return array
     */
    protected function getInputValuesFromForm() : array
    {
        $inputValues = [];

        $inputValues['subject'] = (string) $this->form->getInput('subject');
        $inputValues['message'] = (string) $this->form->getInput('message');
        $inputValues['notify'] = (int) $this->form->getInput('notify');
        $inputValues['alias'] = \ilForumUtil::getPublicUserAlias(
            (string) $this->form->getInput('alias'),
            $this->forumProperties->isAnonymized()
        );

        return $inputValues;
    }
}
