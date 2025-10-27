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

namespace ILIAS\Test;

use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\Repository\BaseGUIRequest;
use ILIAS\TestQuestionPool\RequestDataCollectorInterface;

class RequestDataCollector implements RequestDataCollectorInterface
{
    use BaseGUIRequest;

    protected array $params;

    public function __construct(
        HTTPServices $http,
        Refinery $refinery
    ) {
        $this->initRequest($http, $refinery);
    }

    /** @return string[] */
    public function getIds(): array
    {
        return $this->strArray("id");
    }

    public function getQuestionIds(): array
    {
        return $this->intArray('q_id');
    }

    public function getNextCommand(): string
    {
        return $this->str('nextCommand');
    }

    public function getActiveId(): int
    {
        return $this->int('active_id');
    }

    public function getPassId(): int
    {
        return $this->int('pass_id');
    }

    public function retrieveBoolFromPost(string $key): ?bool
    {
        if (!$this->http->wrapper()->post()->has($key)) {
            return null;
        }

        return $this->http->wrapper()->post()->retrieve(
            $key,
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->bool(),
                $this->refinery->always(null)
            ])
        );
    }

    public function isInstanceResponseRequested(): bool
    {
        if (!$this->http->wrapper()->query()->has('instresp')) {
            return false;
        }

        return $this->http->wrapper()->query()->retrieve(
            'instresp',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->bool(),
                $this->refinery->always(false)
            ])
        );
    }

    public function strVal(string $key): string
    {
        return $this->str($key);
    }

    public function isPostRequest(): bool
    {
        return $this->http->request()->getMethod() === 'POST';
    }

    /**
     * @return array<string>
     */
    public function retrieveArrayOfStringsFromPost(string $key): array
    {
        return $this->retrieveArrayFromPost($key, $this->refinery->kindlyTo()->string());
    }

    /**
     * @return array<int>
     */
    public function retrieveArrayOfIntsFromPost(string $key): array
    {
        return $this->retrieveArrayFromPost($key, $this->refinery->kindlyTo()->int());
    }

    private function retrieveArrayFromPost(string $key, Transformation $transformation): array
    {
        return $this->http->wrapper()->post()->retrieve(
            $key,
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($transformation),
                $this->refinery->always([])
            ])
        );
    }
}
