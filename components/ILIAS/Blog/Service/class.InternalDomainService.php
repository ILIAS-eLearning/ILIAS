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

    public function exercise(int $a_node_id): BlogExercise
    {
        return new BlogExercise(
            $a_node_id,
            $this->repositoryTree(),
            $this->user()
        );
    }

    public function perm(
        $access_handler,
        ?int $node_id,
        int $id_type,
        int $user_id,
        int $owner
    ): PermissionManager {
        return new PermissionManager(
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

    /**
     * Access news handling for blog postings.
     */
    public function news(): NewsManager
    {
        return self::$instance["news"] ??= new NewsManager(
            $this->data,
            $this->repo,
            $this
        );
    }


}
