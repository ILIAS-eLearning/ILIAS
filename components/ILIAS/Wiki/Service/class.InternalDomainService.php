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
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\Wiki\Content;
use ILIAS\Wiki\Page;
use ILIAS\Wiki\Wiki;
use ILIAS\Wiki\Links\LinkManager;
use ILIAS\Wiki\Settings\SettingsManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;
    protected static array $instance = [];

    public function __construct(
        Container $DIC,
        protected InternalRepoService $repo_service,
        protected InternalDataService $data_service
    ) {
        $this->initDomainServices($DIC);
    }

    public function log(): \ilLogger
    {
        return $this->logger()->wiki();
    }

    public function content(): Content\DomainService
    {
        return new Content\DomainService(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function wiki(): Wiki\DomainService
    {
        return self::$instance["wiki"] ??= new Wiki\DomainService(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function page(): Page\DomainService
    {
        return self::$instance["page"] ??= new Page\DomainService(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function importantPage(int $ref_id): Navigation\ImportantPageManager
    {
        return self::$instance["imp_page"][$ref_id] ??= new Navigation\ImportantPageManager(
            $this->data_service,
            $this->repo_service->importantPage(),
            $this->wiki(),
            $ref_id
        );
    }

    public function links(int $ref_id): LinkManager
    {
        return self::$instance["links"][$ref_id] ??= new LinkManager(
            $this->data_service,
            $this->repo_service->missingPage(),
            $this,
            $ref_id
        );
    }

    public function wikiSettings(): SettingsManager
    {
        return self::$instance["settings"] ??= new SettingsManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

}
