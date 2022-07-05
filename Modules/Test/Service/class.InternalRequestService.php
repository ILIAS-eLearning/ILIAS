<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Test;

use ILIAS\Repository\BaseGUIRequest;

class InternalRequestService
{
    use BaseGUIRequest;

    protected \ILIAS\HTTP\Services $http;
    protected array $params;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery
    ) {
        $this->initRequest(
            $http,
            $refinery
        );
    }

    public function isset(string $key) : bool
    {
        return $this->raw($key) !== null;
    }

    public function hasRefId() : int
    {
        return $this->raw('ref_id') !== null;
    }

    public function getRefId() : int
    {
        return $this->int("ref_id");
    }

    /** @return string[] */
    public function getIds() : array
    {
        return $this->strArray("id");
    }

    public function hasQuestionId() : bool
    {
        return $this->raw('q_id') !== null;
    }

    public function getQuestionId() : int
    {
        return $this->int('q_id');
    }

    public function getCallingTest() : int
    {
        return $this->int('calling_test');
    }

    public function getNextCommand() : string
    {
        return $this->str('nextCommand');
    }

    public function getActiveId() : int
    {
        return $this->int('active_id');
    }

    public function getPassId() : int
    {
        return $this->int('pass_id');
    }

    /**
     * @return mixed|null
     */
    public function raw(string $key)
    {
        $no_transform = $this->refinery->identity();
        return $this->get($key, $no_transform);
    }

    public function getParsedBody()
    {
        return $this->http->request()->getParsedBody();
    }
}
