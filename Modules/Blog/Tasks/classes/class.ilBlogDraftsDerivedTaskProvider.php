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

/**
 * Class ilBlogDraftsDerivedTaskProvider
 * @author Thomas Famula <famula@leifos.de>
 */
class ilBlogDraftsDerivedTaskProvider implements ilDerivedTaskProvider
{
    protected ilTaskService $taskService;
    protected \ilAccess $accessHandler;
    protected \ilLanguage $lng;

    public function __construct(
        ilTaskService $taskService,
        \ilAccessHandler $accessHandler,
        \ilLanguage $lng
    ) {
        $this->taskService = $taskService;
        $this->accessHandler = $accessHandler;
        $this->lng = $lng;

        $this->lng->loadLanguageModule('blog');
    }

    public function isActive() : bool
    {
        return true;
    }

    public function getTasks(int $user_id) : array
    {
        $tasks = [];

        $blogs = ilBlogPosting::searchBlogsByAuthor($user_id);
        foreach ($blogs as $blog_id) {
            $posts = ilBlogPosting::getAllPostings($blog_id);
            foreach ($posts as $post_id => $post) {
                if ((int) $post['author'] !== $user_id) {
                    continue;
                }

                $active = ilBlogPosting::_lookupActive($post_id, "blp");
                $withdrawn = $post['last_withdrawn']->get(IL_CAL_DATETIME);
                if (!$active && $withdrawn === null) {
                    $refId = $this->getFirstRefIdWithPermission('read', $blog_id, $user_id);
                    $wspId = 0;

                    $url = ilLink::_getStaticLink($refId, 'blog', true, "_" . $post_id . "_edit");

                    if ($refId === 0) {
                        $wspId = $this->getWspId($blog_id, $user_id);
                        if ($wspId === 0) {
                            continue;
                        }
                        $url = ilLink::_getStaticLink($wspId, 'blog', true, "_" . $post_id . "_edit_wsp");
                    }

                    $title = sprintf(
                        $this->lng->txt('blog_task_publishing_draft_title'),
                        $post['title']
                    );

                    $task = $this->taskService->derived()->factory()->task(
                        $title,
                        $refId,
                        0,
                        0,
                        $wspId
                    );

                    $tasks[] = $task->withUrl($url);
                }
            }
        }

        return $tasks;
    }

    protected function getFirstRefIdWithPermission(
        string $operation,
        int $objId,
        int $userId
    ) : int {
        foreach (\ilObject::_getAllReferences($objId) as $refId) {
            if ($this->accessHandler->checkAccessOfUser($userId, $operation, '', $refId)) {
                return $refId;
            }
        }

        return 0;
    }

    protected function getWspId(int $objId, int $userId) : int
    {
        $wst = new ilWorkspaceTree($userId);
        return $wst->lookupNodeId($objId);
    }
}
