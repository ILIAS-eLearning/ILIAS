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

namespace ILIAS\BookingManager\Common;

use ILIAS\Filesystem\Stream\Stream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Services as IliasHttpServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\ServerRequestInterface;

class HttpService
{
    public const string KEY_REF_ID = 'ref_id';

    public const string ALL_OBJECTS = 'ALL_OBJECTS';

    public function __construct(
        private readonly IliasHttpServices $http,
        private readonly Refinery $refinery
    ) {
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->http->request();
    }

    public function getRefId(): int
    {
        return $this->get(self::KEY_REF_ID, $this->refinery->kindlyTo()->int());
    }

    public function resolveRowParameter(string $key): string|int
    {
        return $this->get($key, $this->refinery->byTrying([
            $this->refinery->kindlyTo()->int(),
            $this->refinery->kindlyTo()->string(),
            $this->refinery->custom()->transformation(static fn(array $value): string|int => $value[0])
        ]));
    }

    public function resolveRowParameters(string $key): array|string
    {
        return $this->get($key, $this->refinery->custom()->transformation(
            static fn(array|string $value): array|string => $value === self::ALL_OBJECTS || $value[0] === self::ALL_OBJECTS
                ? self::ALL_OBJECTS
                : array_map(
                    static fn(string $value): string|int => count(explode('_', $value)) > 1 ? $value : (int) $value,
                    $value
                )
        )) ?? [];
    }

    public function get(string $key, Transformation $t): mixed
    {
        $wrapper = $this->http->wrapper();

        return match(true) {
            $wrapper->post()->has($key) => $wrapper->post()->retrieve($key, $t),
            $wrapper->query()->has($key) => $wrapper->query()->retrieve($key, $t),
            default => null,
        };
    }

    /**
     * @param Stream|string|mixed $response
     */
    public function sendAsync(mixed $response): void
    {
        $response = match(true) {
            is_string($response) => Streams::ofString($response),
            is_resource($response) => Streams::ofResource($response),
            default => $response,
        };

        $this->http->saveResponse($this->http->response()->withBody($response));
        $this->http->sendResponse();
        $this->http->close();
    }

    public function has(string $key): bool
    {
        return $this->http->wrapper()->query()->has($key) || $this->http->wrapper()->post()->has($key);
    }
}
