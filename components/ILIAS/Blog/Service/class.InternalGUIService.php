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

class InternalGUIService
{
    use GlobalDICGUIServices;

    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;
    protected static array $instance = [];

    public function __construct(
        Container $DIC,
        InternalDataService $data_service,
        InternalDomainService $domain_service
    ) {
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->initGUIServices($DIC);
    }

    public function navigation(): Navigation\GUIService
    {
        return new Navigation\GUIService(
            $this->domain_service,
            $this
        );
    }

    public function presentation(): Presentation\GUIService
    {
        return new Presentation\GUIService(
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

    public function contributor(): Contributor\GUIService
    {
        return new Contributor\GUIService(
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
}
