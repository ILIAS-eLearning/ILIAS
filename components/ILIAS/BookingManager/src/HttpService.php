<?php

namespace ILIAS\BookingManager;

use ILIAS\Filesystem\Stream\Stream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Services as IliasHttpServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\ServerRequestInterface;

class HttpService
{
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
        return $this->get('ref_id', $this->refinery->kindlyTo()->int());
    }

    public function resolveRowParameter(string $key): string|int
    {
        return $this->get($key, $this->refinery->byTrying([
            $this->refinery->kindlyTo()->int(),
            $this->refinery->kindlyTo()->string(),
            $this->refinery->custom()->transformation(fn(array $v): string|int => $v[0])
        ]));
    }

    public function resolveRowParameters(string $key): array|string
    {
        return $this->get($key, $this->refinery->custom()->transformation(
            static fn(array|string $value): array|string => $value === 'ALL_OBJECTS' || $value[0] === 'ALL_OBJECTS'
                ? 'ALL_OBJECTS'
                : array_map('intval', $value)
        )) ?? [];
    }

    public function get(string $key, Transformation $t): mixed
    {

        $wrapper = $this->http->wrapper();
        if ($wrapper->post()->has($key)) {
            return $wrapper->post()->retrieve($key, $t);
        }
        if ($wrapper->query()->has($key)) {
            return $wrapper->query()->retrieve($key, $t);
        }
        return null;
    }

    /**
     * @param Stream|string|mixed $response
     */
    public function sendAsync(mixed $response): void
    {
        if (is_string($response)) {
            $response = Streams::ofString($response);
        } elseif (is_resource($response)) {
            $response = Streams::ofResource($response);
        }

        $this->http->saveResponse(
            $this->http->response()->withBody($response)
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    public function has(string $key): bool
    {
        return $this->http->wrapper()->query()->has($key) || $this->http->wrapper()->post()->has($key);
    }
}
