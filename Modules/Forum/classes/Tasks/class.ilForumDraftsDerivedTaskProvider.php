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

/**
 * Class ilForumDraftsDerivedTaskProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumDraftsDerivedTaskProvider implements ilDerivedTaskProvider
{
    public function __construct(
        protected ilTaskService $taskService,
        protected ilAccessHandler $accessHandler,
        protected ilLanguage $lng,
        protected ilSetting $settings,
        protected ilCtrlInterface $ctrl
    ) {
        $this->lng->loadLanguageModule('forum');
    }

    public function getTasks(int $user_id): array
    {
        $tasks = [];

        $drafts = ilForumPostDraft::getDraftInstancesByUserId($user_id);
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
                0
            );

            $isThread = false;
            if (0 === $draft->getThreadId()) {
                $isThread = true;
            }

            $anchor = '';
            $params = ['ref_id' => $refId];
            if ($isThread) {
                $params['draft_id'] = $draft->getDraftId();
                $cmd = 'editThreadDraft';
            } else {
                $params['thr_pk'] = $draft->getThreadId();
                $params['pos_pk'] = $draft->getPostId();
                $cmd = 'viewThread';
                $anchor = 'draft_' . $draft->getDraftId();
            }

            foreach ($params as $name => $value) {
                $this->ctrl->setParameterByClass(ilObjForumGUI::class, $name, $value);
            }
            $url = $this->ctrl->getLinkTargetByClass(
                [
                    ilRepositoryGUI::class,
                    ilObjForumGUI::class
                ],
                $cmd,
                $anchor
            );
            foreach (array_keys($params) as $name) {
                $this->ctrl->setParameterByClass(ilObjForumGUI::class, $name, null);
            }

            $tasks[] = $task->withUrl($url);
        }

        return $tasks;
    }

    protected function getFirstRefIdWithPermission(string $operation, int $objId, int $userId): int
    {
        foreach (ilObject::_getAllReferences($objId) as $refId) {
            if ($this->accessHandler->checkAccessOfUser($userId, $operation, '', $refId)) {
                return $refId;
            }
        }

        return 0;
    }

    public function isActive(): bool
    {
        return (bool) $this->settings->get('save_post_drafts', '0');
    }
}
