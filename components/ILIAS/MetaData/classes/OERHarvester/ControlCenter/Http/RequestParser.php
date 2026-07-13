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

namespace ILIAS\MetaData\OERHarvester\ControlCenter\Http;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\GlobalHttpState;
use ilMDEditorException;

class RequestParser implements RequestParserInterface
{
    protected GlobalHttpState $http;
    protected Refinery $refinery;

    public function __construct(
        GlobalHttpState $http,
        Refinery $refinery
    ) {
        $this->http = $http;
        $this->refinery = $refinery;
    }

    public function fetchRefID(): int
    {
        $request_wrapper = $this->http->wrapper()->query();
        if ($request_wrapper->has(RequestParserInterface::REF_ID_PARAM)) {
            return $request_wrapper->retrieve(
                RequestParserInterface::REF_ID_PARAM,
                $this->refinery->kindlyTo()->int()
            );
        }
        throw new ilMDEditorException('ref_id for control center not found.');
    }

    public function fetchObjID(): int
    {
        $request_wrapper = $this->http->wrapper()->query();
        if ($request_wrapper->has(RequestParserInterface::OBJ_ID_PARAM)) {
            return $request_wrapper->retrieve(
                RequestParserInterface::OBJ_ID_PARAM,
                $this->refinery->kindlyTo()->int()
            );
        }
        throw new ilMDEditorException('obj_id for control center not found.');
    }

    public function fetchType(): string
    {
        $request_wrapper = $this->http->wrapper()->query();
        if ($request_wrapper->has(RequestParserInterface::TYPE_PARAM)) {
            return $request_wrapper->retrieve(
                RequestParserInterface::TYPE_PARAM,
                $this->refinery->kindlyTo()->string()
            );
        }
        throw new ilMDEditorException('type for control center not found.');
    }
}
