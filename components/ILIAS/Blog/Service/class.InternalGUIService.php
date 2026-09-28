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
use ILIAS\Repository\GlobalDICGUIServices;
use ILIAS\PermanentLink\PermanentLinkManager;
use ILIAS\Blog\ReadingTime\GUIService;
use ILIAS\Blog\RSS\RSSGUI;
use ILIAS\Blog\Posting\Service\GUIService as PostingGUIService;
use ILIAS\Blog\Permission\BlogCmdPermission;
use ILIAS\Blog\Permission\PermissionManager;
use ILIAS\Blog\Export\GUIService as ExportGUIService;
use ilObjBlog;

class InternalGUIService
{
    use GlobalDICGUIServices;

    protected static array $instance = [];

    public function __construct(
        Container $DIC,
        protected InternalDataService $data_service,
        protected InternalDomainService $domain_service
    ) {
        $this->initGUIServices($DIC);
    }

    public function navigation(): Navigation\GUIService
    {
        return self::$instance["navigation"] ??
            self::$instance["navigation"] = new Navigation\GUIService(
                $this->domain_service,
                $this
            );
    }

    public function presentation(): Presentation\GUIService
    {
        return self::$instance["presentation"] ??= new Presentation\GUIService(
            $this->data_service,
            $this->domain_service,
            $this
        );
    }

    public function editing(): Editing\GUIService
    {
        return self::$instance["editing"] ??= new Editing\GUIService(
            $this->data_service,
            $this->domain_service,
            $this
        );
    }

    public function standardRequest(): StandardGUIRequest
    {
        return new StandardGUIRequest(
            $this->http(),
            $this->domain_service->refinery()
        );
    }

    public function blogContext(
        int $node_id,
        int $id_type,
        ?int $blog_id,
        string $month,
        ?int $author,
        PermissionManager $permission,
        bool $call_by_reference = false
    ): BlogGUIContext {
        return new BlogGUIContext(
            $node_id,
            $id_type,
            $blog_id === null ? null : new ilObjBlog($blog_id, false),
            $month,
            $author,
            $permission,
            $this->standardRequest(),
            $call_by_reference
        );
    }

    public function contributor(): Contributor\GUIService
    {
        return self::$instance["contributor"] ??
            self::$instance["contributor"] = new Contributor\GUIService(
                $this->data_service,
                $this->domain_service,
                $this
            );
    }

    public function exercise(): Exercise\GUIService
    {
        return new Exercise\GUIService(
            $this->data_service,
            $this->domain_service,
            $this
        );
    }

    public function permanentLink(
        int $ref_id = 0,
        int $wsp_id = 0
    ): PermanentLinkManager {
        return new PermanentLinkManager(
            $this->domain_service->staticUrl(),
            $this,
            $ref_id,
            $wsp_id
        );
    }

    public function settings(): Settings\GUIService
    {
        return self::$instance["settings"] ??
            self::$instance["settings"] = new Settings\GUIService(
                $this->data_service,
                $this->domain_service,
                $this
            );
    }

    public function readingTime(): GUIService
    {
        return self::$instance["reading_time"] ??
            self::$instance["reading_time"] = new ReadingTime\GUIService(
                $this->data_service,
                $this->domain_service,
                $this
            );
    }

    public function posting(): PostingGUIService
    {
        return self::$instance["posting"] ??= new PostingGUIService(
            $this->data_service,
            $this->domain_service,
            $this
        );
    }

    public function rss(): RSSGUI
    {
        return self::$instance["rss"] ??= new RSSGUI(
            $this->data_service,
            $this->domain_service,
            $this
        );
    }

    public function cmdPerm(PermissionManager $blog_access): BlogCmdPermission
    {
        return new BlogCmdPermission(
            $this->domain_service->lng(),
            $blog_access,
            $this->ui()->mainTemplate(),
            $this->ctrl(),
            $this->standardRequest()
        );
    }

    public function export(): ExportGUIService
    {
        return self::$instance["export"] ??= new ExportGUIService(
            $this->data_service,
            $this->domain_service,
            $this
        );
    }

}
