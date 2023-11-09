<?php

declare(strict_types=1);

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

namespace ILIAS\Container;

use ILIAS\DI;
use ILIAS\Repository;
use ILIAS\Container\Page\PageManager;
use ILIAS\Container\Classification\ClassificationManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use Repository\GlobalDICDomainServices;

    protected \ILIAS\Style\Content\DomainService $content_style_domain;
    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;

    public function __construct(
        DI\Container $DIC,
        InternalRepoService $repo_service,
        InternalDataService $data_service
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->content_style_domain = $DIC->contentStyle()->domain();
        $this->initDomainServices($DIC);
    }

    public function content(): Content\DomainService
    {
        return new Content\DomainService(
            $this->repo_service,
            $this->data_service,
            $this
        );
    }

    public function page(\ilContainer $container): Page\PageManager
    {
        return new PageManager(
            $this,
            $this->content_style_domain,
            $container
        );
    }

    public function classification(int $base_ref_id): ClassificationManager
    {
        return new ClassificationManager(
            $this->repo_service->classification($base_ref_id),
            $base_ref_id
        );
    }
}
