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

namespace ILIAS\Wiki;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;
use ILIAS\Wiki\Content;
use ILIAS\Wiki\Page;
use ILIAS\Wiki\Notification\NotificationGUI;

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

    public function request(
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ): WikiGUIRequest {
        return new WikiGUIRequest(
            $this->http(),
            $this->domain_service->refinery(),
            $passed_query_params,
            $passed_post_data
        );
    }

    public function content(): Content\GUIService
    {
        return self::$instance["content"] ??= new Content\GUIService(
            $this->domain_service,
            $this
        );
    }

    public function page(): Page\GUIService
    {
        return self::$instance["page"] ??= new Page\GUIService(
            $this->domain_service,
            $this
        );
    }

    public function notification(): NotificationGUI
    {
        return self::$instance["notification"] ??= new NotificationGUI(
            $this->domain_service,
            $this
        );
    }

    public function wiki(): Wiki\GUIService
    {
        return self::$instance["wiki"] ??= new Wiki\GUIService(
            $this->domain_service,
            $this
        );
    }

    public function settings(
    ): Settings\GUIService {
        return self::$instance["settings"] ??= new Settings\GUIService(
            $this->data_service,
            $this->domain_service,
            $this
        );
    }

}
