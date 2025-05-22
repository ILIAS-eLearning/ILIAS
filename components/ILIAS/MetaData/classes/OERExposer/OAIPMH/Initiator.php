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

namespace ILIAS\MetaData\OERExposer\OAIPMH;

use ILIAS\DI\Container;
use ILIAS\MetaData\OERExposer\OAIPMH\HTTP\WrapperInterface as HTTPWrapperInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\HTTP\Wrapper as HTTPWrapper;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\ParserInterface as RequestParserInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Parser as RequestParser;
use ILIAS\MetaData\OERExposer\OAIPMH\Responses\RequestProcessorInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Responses\RequestProcessor;
use ILIAS\MetaData\OERExposer\OAIPMH\Responses\Writer;
use ILIAS\MetaData\OERExposer\OAIPMH\FlowControl\TokenHandler;
use ILIAS\MetaData\Settings\SettingsInterface;
use ILIAS\MetaData\OERHarvester\ExposedRecords\DatabaseRepository as ExposedRecordsRepository;

class Initiator implements InitiatorInterface
{
    protected Container $dic;

    protected HTTPWrapperInterface $http_wrapper;
    protected RequestParserInterface $request_parser;
    protected RequestProcessorInterface $request_processor;

    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }

    public function httpWrapper(): HTTPWrapperInterface
    {
        if (isset($this->http_wrapper)) {
            return $this->http_wrapper;
        }

        return $this->http_wrapper = new HTTPWrapper(
            $this->dic->http(),
            $this->dic->refinery()
        );
    }

    public function settings(): SettingsInterface
    {
        return \ilMDSettings::_getInstance();
    }

    public function requestParser(): RequestParserInterface
    {
        if (isset($this->request_parser)) {
            return $this->request_parser;
        }

        return $this->request_parser = new RequestParser($this->httpWrapper());
    }

    public function requestProcessor(): RequestProcessorInterface
    {
        if (isset($this->request_processor)) {
            return $this->request_processor;
        }

        return $this->request_processor = new RequestProcessor(
            new Writer(),
            $this->settings(),
            new ExposedRecordsRepository($this->dic->database()),
            new TokenHandler()
        );
    }
}
