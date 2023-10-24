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

namespace ILIAS\StaticURL;

use ILIAS\StaticURL\Handler\HandlerService;
use ILIAS\StaticURL\Builder\URIBuilder;
use ILIAS\StaticURL\Response\Factory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Services
{
    private Factory $response_factory;

    public function __construct(
        private HandlerService $handler_service,
        private URIBuilder $uri_builder,
        private Context $context
    ) {
    }

    public function handler(): HandlerService
    {
        return $this->handler_service;
    }

    public function builder(): URIBuilder
    {
        return $this->uri_builder;
    }

}
