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
use ILIAS\Repository\BaseGUIRequest;
use Psr\Http\Message\RequestInterface;

class RequestDataCollector
{
    use BaseGUIRequest;

    protected array $params;

    public function __construct(HTTPServices $http, Refinery $refinery)
    {
        $this->initRequest($http, $refinery);
    }

    public function getRequest(): RequestInterface
    {
        return $this->http->request();
    }

    public function isset(string $key): bool
    {
        return $this->raw($key) !== null;
    }

    public function hasRefId(): bool
    {
        return $this->raw('ref_id') !== null;
    }

    public function getRefId(): int
    {
        return $this->int('ref_id');
    }

    /**
     * @return string[]
     */
    public function getIds(): array
    {
        return $this->strArray('id');
    }

    public function hasQuestionId(): bool
    {
        return $this->raw('q_id') !== null;
    }

    public function getQuestionId(): int
    {
        return $this->int('q_id');
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

    public function raw(string $key): mixed
    {
        return $this->get($key, $this->refinery->identity());
    }

    public function strVal(string $key): string
    {
        return $this->str($key);
    }

    public function getParsedBody(): ?array
    {
        return $this->http->request()->getParsedBody();
    }

    public function getArrayOfIntsFromPost(string $key): ?array
    {
        $post_wrapper = $this->http->wrapper()->post();

        if ($post_wrapper->has($key)) {
            return $post_wrapper->retrieve(
                $key,
                $this->refinery->container()->mapValues(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }

        return null;
    }

    public function getArrayOfStringsFromPost(string $key): ?array
    {
        $post_wrapper = $this->http->wrapper()->post();

        if ($post_wrapper->has($key)) {
            return $post_wrapper->retrieve(
                $key,
                $this->refinery->container()->mapValues(
                    $this->refinery->kindlyTo()->string()
                )
            );
        }

        return null;
    }
}
