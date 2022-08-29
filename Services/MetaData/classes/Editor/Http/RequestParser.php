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

namespace ILIAS\MetaData\Editor\Http;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;

class RequestParser implements RequestParserInterface
{
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected PathFactoryInterface $path_factory;

    public function __construct(
        GlobalHttpState $http,
        Refinery $refinery,
        PathFactoryInterface $path_factory
    ) {
        $this->http = $http;
        $this->refinery = $refinery;
        $this->path_factory = $path_factory;
    }

    public function fetchBasePath(): PathInterface
    {
        return $this->fetchPath(Parameter::BASE_PATH, false);
    }

    public function fetchActionPath(): PathInterface
    {
        return $this->fetchPath(Parameter::ACTION_PATH, true);
    }

    public function fetchRequestForForm(
        bool $with_action_path
    ): RequestForFormInterface {
        return new RequestForForm(
            $request = $this->http->request(),
            $with_action_path ? $this->fetchActionPath() : null
        );
    }

    protected function fetchPath(
        Parameter $parameter,
        bool $throw_error
    ): PathInterface {
        $request_wrapper = $this->http->wrapper()->query();
        if ($request_wrapper->has($parameter->value)) {
            $path_string = $request_wrapper->retrieve(
                $parameter->value,
                $this->refinery->kindlyTo()->string()
            );
            return $this->path_factory->fromString(urldecode($path_string));
        }
        if ($throw_error) {
            throw new \ilMDEditorException('Parameter not found.');
        } else {
            return $this->path_factory->custom()->get();
        }
    }
}
