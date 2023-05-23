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

namespace ILIAS\COPage\PC;

use ILIAS\DI\Container;
use ILIAS\COPage\InternalDataService;
use ILIAS\COPage\InternalRepoService;
use ILIAS\COPage\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DomainService
{
    protected ?PCDefinition $def;
    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDataService $data_service,
        InternalRepoService $repo_service,
        InternalDomainService $domain_service,
        ?PCDefinition $pc_definition = null
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->def = $pc_definition;
        $this->pc_factory = new PCFactory(
            $this->definition()
        );
    }

    public function getByNode(
        ?\DOMNode $node,
        \ilPageObject $page_object
    ): ?\ilPageContent {
        return $this->pc_factory->getByNode($node, $page_object);
    }

    public function definition(): PCDefinition
    {
        return $this->def ?? new PCDefinition();
    }
}
