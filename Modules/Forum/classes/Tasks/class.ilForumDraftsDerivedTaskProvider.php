<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumDraftsDerivedTaskProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumDraftsDerivedTaskProvider implements \ilDerivedTaskProvider
{
    /** @var ilTaskService */
    protected $taskService;

    /** @var \ilAccess */
    protected $accessHandler;

    /** @var \ilLanguage */
    protected $lng;

    /** @var \ilSetting */
    protected $settings;

    /** @var \ilCtrl */
    protected $ctrl;

    /**
     * ilForumDraftsDerivedTaskProvider constructor.
     * @param \ilTaskService $taskService
     * @param \ilAccessHandler $accessHandler
     * @param \ilLanguage $lng
     * @param \ilSetting $settings
     * @param \ilCtrl $ctrl
     */
    public function __construct(
        ilTaskService $taskService,
        \ilAccessHandler $accessHandler,
        \ilLanguage $lng,
        \ilSetting $settings,
        \ilCtrl $ctrl
    ) {
        $this->taskService = $taskService;
        $this->accessHandler = $accessHandler;
        $this->lng = $lng;
        $this->settings = $settings;
        $this->ctrl = $ctrl;

        $this->lng->loadLanguageModule('forum');
    }

    /**
     * @inheritDoc
     */
    public function getTasks(int $user_id): array
    {
        $tasks = [];

        $drafts = \ilForumPostDraft::getDraftInstancesByUserId($user_id);
        foreach ($drafts as $draft) {
            $objId = ilForum::_lookupObjIdForForumId($draft->getForumId());
            $refId = $this->getFirstRefIdWithPermission('read', $objId, $user_id);

            if (0 === $refId) {
                continue;
            }

            $title = sprintf(
                $this->lng->txt('frm_task_publishing_draft_title'),
                $draft->getPostSubject()
            );

            $task = $this->taskService->derived()->factory()->task(
                $title,
                $refId,
                0,
                strtotime($draft->getPostDate())
            );

            $isThread = false;
            if (0 === (int)$draft->getThreadId()) {
                $isThread = true;
            }

            $anchor = '';
            if ($isThread) {
                $params['draft_id'] = $draft->getDraftId();
                $params['cmd'] = 'editThreadDraft';
            } else {
                $params['thr_pk'] = $draft->getThreadId();
                $params['pos_pk'] = $draft->getPostId();
                $params['cmd'] = 'viewThread';
                $anchor = '#draft_' . $draft->getDraftId();
            }

            $url = \ilLink::_getLink($refId, 'frm', $params) . $anchor;

            $tasks[] = $task->withUrl($url);
        }

        return $tasks;
    }

    /**
     * @param string $operation
     * @param int $objId
     * @param int $userId
     * @return int
     */
    protected function getFirstRefIdWithPermission(string $operation, int $objId, int $userId): int
    {
        foreach (\ilObject::_getAllReferences($objId) as $refId) {
            if ($this->accessHandler->checkAccessOfUser($userId, $operation, '', $refId)) {
                return $refId;
            }
        }

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return (bool)$this->settings->get('save_post_drafts', false);
    }
}