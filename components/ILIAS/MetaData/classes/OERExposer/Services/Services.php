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

namespace ILIAS\MetaData\OERExposer\Services;

use ILIAS\DI\Container;
use ILIAS\MetaData\OERHarvester\Services\Services as PublishingServices;
use ilMDSettings;
use ILIAS\MetaData\OERExposer\OAIPMH\HTTP\Wrapper as HTTPWrapper;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Parser as RequestParser;
use ILIAS\MetaData\OERExposer\OAIPMH\Responses\RequestProcessor;
use ILIAS\MetaData\OERExposer\OAIPMH\Responses\Writer;
use ILIAS\MetaData\OERExposer\OAIPMH\FlowControl\TokenHandler;
use ILIAS\MetaData\OERExposer\OAIPMH\HandlerInterface as OAIPMHHandlerInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Handler as OAIPMHHandler;

class Services
{
    protected OAIPMHHandlerInterface $oaipmh_handler;

    public function __construct(
        protected Container $dic,
        protected PublishingServices $publishing_services,
    ) {
    }

    public function OAIPMHHandler(): OAIPMHHandlerInterface
    {
        return $this->oaipmh_handler ??= new OAIPMHHandler(
            $this->dic->logger()->meta(),
            $settings = ilMDSettings::_getInstance(),
            $http_wrapper = new HTTPWrapper(
                $this->dic->http(),
                $this->dic->refinery()
            ),
            new RequestParser($http_wrapper),
            new RequestProcessor(
                new Writer(),
                $settings,
                $this->publishing_services->exposedRecordsRepository(),
                new TokenHandler()
            )
        );
    }
}
