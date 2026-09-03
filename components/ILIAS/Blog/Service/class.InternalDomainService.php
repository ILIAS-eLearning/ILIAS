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

namespace ILIAS\Blog;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\Blog\Exercise\BlogExercise;
use ILIAS\Blog\Permission\PermissionManager;
use ILIAS\Blog\ReadingTime\ReadingTimeManager;
use ILIAS\Blog\Settings\SettingsManager;
use ILIAS\Blog\Posting\PostingManager;
use ILIAS\Notes;
use ILIAS\Blog\News\NewsManager;
use ILIAS\Blog\Notification\NotificationManager;
use ILIAS\Blog\Export\DomainService;
use ILIAS\Blog\Keywords\KeywordManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    protected static array $instance = [];
    protected Container $dic;

    public function __construct(
        Container $DIC,
        protected InternalRepoService $repo,
        protected InternalDataService $data
    ) {
        $this->initDomainServices($DIC);
        $this->dic = $DIC;
    }

    public function export(): DomainService
    {
        return self::$instance["export"] ??= new DomainService(
            $this->data,
            $this->repo,
            $this
        );
    }

    public function exercise(int $a_node_id): BlogExercise
    {
        return new BlogExercise(
            $a_node_id,
            $this->repositoryTree(),
            $this->user()
        );
    }

    public function getBlogAccessHandler(int $id_type): \ilWorkspaceAccessHandler|\ilAccessHandler
    {
        switch ($id_type) {
            case \ilObjBlogGUI::REPOSITORY_NODE_ID:
                return $this->access();

            case \ilObjBlogGUI::WORKSPACE_NODE_ID:
                $tree = new \ilWorkspaceTree($this->user()->getId());
                return new \ilWorkspaceAccessHandler($tree);
        }
        throw new \RuntimeException("Invalid id type ($id_type).");
    }

    public function getObjectIdForWspId(int $wsp_id): int
    {
        $tree = new \ilWorkspaceTree($this->user()->getId());
        return (int) $tree->lookupObjectId($wsp_id);
    }

    public function perm(
        ?int $node_id,
        int $id_type,
        int $user_id,
        int $owner
    ): PermissionManager {
        $access_handler = $this->getBlogAccessHandler($id_type);
        return new PermissionManager(
            $this,
            $access_handler,
            $node_id,
            $id_type,
            $user_id,
            $owner
        );
    }

    public function readingTime(): ReadingTimeManager
    {
        return new ReadingTimeManager();
    }

    public function notes(): Notes\DomainService
    {
        return $this->dic->notes()->domain();
    }

    public function blogSettings(): SettingsManager
    {
        return self::$instance["settings"] ??
            self::$instance["settings"] = new SettingsManager(
                $this->data,
                $this->repo,
                $this
            );
    }

    public function posting(): PostingManager
    {
        return self::$instance["posting"] ??= new PostingManager(
            $this->data,
            $this->repo,
            $this
        );
    }

    public function postingList(
        int $obj_id,
        bool $include_inactive = true
    ): Posting\PostingList {
        $settings = $this->blogSettings()->getByObjId($obj_id);
        return new Posting\PostingList(
            $obj_id,
            $this->posting(),
            $settings,
            $include_inactive
        );
    }

    public function news(): NewsManager
    {
        return self::$instance["news"] ??= new NewsManager(
            $this->data,
            $this->repo,
            $this,
            $this->dic->blog()->internal()->gui()
        );
    }

    public function notification(): NotificationManager
    {
        return self::$instance["notification"] ??= new NotificationManager(
            $this
        );
    }

    public function keywords(): KeywordManager
    {
        return self::$instance["keywords"] ??= new KeywordManager(
            $this
        );
    }

}
